#!/usr/bin/env bash
# P5 (opsional, paling berisiko): rotasi password DB, Jalur A (MySQL >= 8.0.14).
# Default: ROLL-FORWARD. Skrip tidak pernah mengembalikan .env otomatis.
# Password hanya lewat STDIN/variabel shell, tidak pernah di argumen proses.
set -Eeuo pipefail
umask 077

DB_SVC="${DB_SVC:-mgi-db}"
WEB_SVC="${WEB_SVC:-mgi-web}"
DB_USER_NAME="${DB_USER_NAME:-mgi_user}"
DB_PASS_KEY="${DB_PASS_KEY:-DB_PASS}"
ROOT_PASS_KEY="${ROOT_PASS_KEY:-MYSQL_ROOT_PASSWORD}"
WAIT_SECONDS="${WAIT_SECONDS:-300}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/mgi}"
STAGE="awal (belum ada perubahan)"

on_exit() {
  rc=$?
  if [ "$rc" -ne 0 ]; then
    echo
    echo "!!! GAGAL pada tahap: $STAGE (kode $rc)"
    echo "!!! Status maintenance Nginx TIDAK diubah oleh skrip ini. Cek sendiri bila Anda mengaktifkannya."
    echo "!!! Skrip TIDAK melakukan rollback otomatis. Lihat 'PEMULIHAN MANUAL' di bawah."
    cat <<'EOF'

PEMULIHAN MANUAL
- Jika gagal SEBELUM tahap "ubah .env": tidak ada yang berubah di .env. Password lama mgi_user masih sah. Aman, tidak perlu apa-apa.
- Jika gagal SETELAH ALTER USER: mgi_user punya DUA password sah (lama+baru) sampai DISCARD OLD PASSWORD
  dijalankan. Jadi web dengan .env lama MASIH bisa konek. Root hanya punya password BARU.
- Jika .env sudah diubah tapi container gagal sehat: diagnosis dulu -> 'docker compose logs --tail=100 mgi-db'.
  Pilihan aman: tetap di kredensial baru (roll-forward), perpanjang waktu tunggu, ulangi recreate.
- Jika mau kembali ke .env lama: cp file backup di /var/backups/mgi/env.* ke .env, lalu recreate mgi-web.
  Itu hanya berlaku untuk mgi_user (password lama masih ditahan). Password ROOT lama sudah tidak berlaku;
  gunakan password root baru (ada di .env baru, atau di backup jika Anda mencatatnya).
EOF
  fi
}
trap on_exit EXIT

# --- Cek lokasi ---
STAGE="cek direktori proyek"
if [ ! -f .env ] || ! ls docker-compose*.y*ml >/dev/null 2>&1; then
  echo "Jalankan dari direktori proyek (harus ada .env dan docker-compose.yml)." >&2
  exit 2
fi

# --- Helper ---
env_get() { # baca KEY dari .env: tanpa kutip, tanpa CR
  grep -m1 "^$1=" .env | cut -d= -f2- | tr -d '\r' | sed -E "s/^(['\"])(.*)\1\$/\2/"
}
sql_escape() { # \ -> \\ ,  ' -> ''
  printf '%s' "$1" | sed -e 's/\\/\\\\/g' -e "s/'/''/g"
}
run_sql() { # $1=user $2=password $3=SQL ; password lewat STDIN
  { printf '%s\n' "$2"; printf '%s\n' "$3"; } |
    docker compose exec -T "$DB_SVC" sh -c 'IFS= read -r MYSQL_PWD; export MYSQL_PWD; mysql -u "$0" -N -B' "$1"
}

STAGE="baca .env"
OLD_DB_PASS="$(env_get "$DB_PASS_KEY")"
CURRENT_ROOT_PASS="$(env_get "$ROOT_PASS_KEY")"
[ -n "$OLD_DB_PASS" ] && [ -n "$CURRENT_ROOT_PASS" ] || { echo "Key $DB_PASS_KEY / $ROOT_PASS_KEY kosong di .env"; exit 2; }

STAGE="cek versi MySQL"
VER="$(run_sql root "$CURRENT_ROOT_PASS" 'SELECT VERSION();')"
echo "Versi MySQL: $VER"
case "$VER" in *MariaDB*) echo "MariaDB: Jalur A TIDAK berlaku. Berhenti."; exit 3;; esac
if [ "$(printf '8.0.14\n%s\n' "${VER%%-*}" | sort -V | head -1)" != "8.0.14" ]; then
  echo "Versi < 8.0.14: pakai Jalur B. Berhenti."; exit 3
fi

echo
echo "Ini akan mengganti password $DB_USER_NAME dan root. Ketik YA untuk lanjut:"
read -r ans; [ "$ans" = "YA" ] || { echo "Dibatalkan."; exit 0; }

# --- Langkah 1: RATE_LIMIT_SECRET dulu (sebelum DB_PASS berubah) ---
STAGE="tambah RATE_LIMIT_SECRET"
if ! grep -q '^RATE_LIMIT_SECRET=.\+' .env; then
  printf '\nRATE_LIMIT_SECRET=%s\n' "$(openssl rand -hex 32)" >> .env
fi
grep -q '^RATE_LIMIT_SECRET=.\+' .env || { echo "RATE_LIMIT_SECRET gagal ditulis"; exit 4; }
echo "RATE_LIMIT_SECRET ada di .env. (Recreate $WEB_SVC bila .env dibaca saat start.)"

# --- Langkah 2: password baru (hex = aman dari karakter khusus) ---
NEW_DB_PASS="$(openssl rand -hex 24)"
NEW_ROOT_PASS="$(openssl rand -hex 24)"
export NEW_DB_PASS NEW_ROOT_PASS OLD_DB_PASS

# --- Langkah 3: ALTER USER (lama masih sah untuk mgi_user) ---
STAGE="ALTER USER"
SQL="ALTER USER '$(sql_escape "$DB_USER_NAME")'@'%' IDENTIFIED BY '$(sql_escape "$NEW_DB_PASS")' RETAIN CURRENT PASSWORD;
ALTER USER 'root'@'localhost' IDENTIFIED BY '$(sql_escape "$NEW_ROOT_PASS")';
FLUSH PRIVILEGES;"
run_sql root "$CURRENT_ROOT_PASS" "$SQL"

STAGE="verifikasi login password baru"
[ "$(run_sql "$DB_USER_NAME" "$NEW_DB_PASS" 'SELECT 1;')" = "1" ] || { echo "Login mgi_user baru gagal"; exit 5; }
[ "$(run_sql root "$NEW_ROOT_PASS" 'SELECT 1;')" = "1" ] || { echo "Login root baru gagal"; exit 5; }
echo "Login password baru: OK."

# --- Langkah 4: backup + ubah .env + verifikasi ---
STAGE="backup dan ubah .env"
mkdir -p "$BACKUP_DIR"; chmod 700 "$BACKUP_DIR"
BK="$BACKUP_DIR/env.$(date +%Y%m%d_%H%M%S)"
cp -p .env "$BK"; chmod 600 "$BK"
echo "Backup .env (berisi password LAMA, simpan aman): $BK"

python3 - <<'PY'
import os, re
p = ".env"
s = open(p).read()
def setkey(s, k, v):
    pat = re.compile(r"^%s=.*$" % re.escape(k), re.M)
    return pat.sub(lambda m: "%s=%s" % (k, v), s) if pat.search(s) else s + "\n%s=%s\n" % (k, v)
s = setkey(s, os.environ.get("DB_PASS_KEY", "DB_PASS"), os.environ["NEW_DB_PASS"])
s = setkey(s, os.environ.get("ROOT_PASS_KEY", "MYSQL_ROOT_PASSWORD"), os.environ["NEW_ROOT_PASS"])
open(p + ".tmp", "w").write(s)
os.replace(p + ".tmp", p)
PY
chmod 600 .env
[ "$(env_get "$DB_PASS_KEY")" = "$NEW_DB_PASS" ] || { echo ".env GAGAL diperbarui (DB). Jangan lanjut."; exit 6; }
[ "$(env_get "$ROOT_PASS_KEY")" = "$NEW_ROOT_PASS" ] || { echo ".env GAGAL diperbarui (root). Jangan lanjut."; exit 6; }
echo ".env terverifikasi berisi password baru."

# --- Langkah 5: recreate + tunggu sehat (dengan batas waktu) ---
STAGE="recreate $DB_SVC"
docker compose up -d --force-recreate "$DB_SVC"
STAGE="menunggu $DB_SVC sehat (maks ${WAIT_SECONDS}s)"
deadline=$(( $(date +%s) + WAIT_SECONDS ))
until [ "$(run_sql "$DB_USER_NAME" "$NEW_DB_PASS" 'SELECT 1;' 2>/dev/null || true)" = "1" ]; do
  if [ "$(date +%s)" -ge "$deadline" ]; then
    echo "DB belum siap setelah ${WAIT_SECONDS}s. ROLL-FORWARD: tetap di kredensial baru."
    docker compose logs --tail=50 "$DB_SVC" || true
    exit 7
  fi
  sleep 5
done
echo "DB sehat."

STAGE="recreate $WEB_SVC"
docker compose up -d --force-recreate "$WEB_SVC"

echo
echo "SELESAI. Uji website + login admin. Setelah yakin, buang password lama mgi_user:"
echo "  ALTER USER '$DB_USER_NAME'@'%' DISCARD OLD PASSWORD;  (jalankan manual, lewat STDIN)"
STAGE="selesai"
