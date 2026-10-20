#!/usr/bin/env bash
# Langkah 0: simpan bukti (log + backup DB) SEBELUM deploy. Hanya MEMBACA, tidak mengubah produksi.
# Arsip dienkripsi GPG; passphrase diminta lewat prompt dan dikirim via STDIN (fd 0).
set -Eeuo pipefail
umask 077

ls docker-compose*.y*ml >/dev/null 2>&1 || { echo "Jalankan dari direktori proyek (ada docker-compose.yml)."; exit 2; }

OUT_BASE="${OUT_BASE:-/var/backups/mgi}"
TS="$(date +%Y%m%d_%H%M%S)"
EVIDENCE_DIR="$OUT_BASE/evidence_$TS"
ARCHIVE="$OUT_BASE/evidence_$TS.tar.gz.gpg"
mkdir -p "$EVIDENCE_DIR"; chmod 700 "$OUT_BASE" "$EVIDENCE_DIR"

echo "[1/5] Salin log container"
docker logs mgi_web_app > "$EVIDENCE_DIR/docker_web.log" 2>&1 || echo "(docker logs web gagal)" >&2
docker logs mgi_database > "$EVIDENCE_DIR/docker_db.log" 2>&1 || true

echo "[2/5] Salin log Nginx (termasuk .gz)"
sudo cp -p /var/log/nginx/access.log* /var/log/nginx/error.log* "$EVIDENCE_DIR/" 2>/dev/null || echo "(sebagian log nginx tidak ada)"

echo "[3/5] Dump DB (password dibaca dari environment container, tidak di argumen)"
docker compose exec -T mgi-db sh -c \
  'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqldump -u root --single-transaction --routines --no-tablespaces "$MYSQL_DATABASE"' \
  > "$EVIDENCE_DIR/db.sql"
[ -s "$EVIDENCE_DIR/db.sql" ] || { echo "Dump DB kosong. Berhenti."; exit 3; }

echo "[4/5] Info retensi log (baca saja)"
{ cat /etc/logrotate.d/nginx 2>/dev/null; docker inspect --format '{{.HostConfig.LogConfig}}' mgi_web_app; } > "$EVIDENCE_DIR/retention.txt" 2>&1 || true

echo "[5/5] Arsip + enkripsi GPG (passphrase via STDIN)"
tar -czf "$EVIDENCE_DIR.tar.gz" -C "$OUT_BASE" "evidence_$TS"
read -rsp "Passphrase arsip (jangan hilang): " PP; echo
printf '%s\n' "$PP" | gpg --batch --pinentry-mode loopback --passphrase-fd 0 --symmetric --cipher-algo AES256 -o "$ARCHIVE" "$EVIDENCE_DIR.tar.gz"
# verifikasi: dekripsi + baca isi tar
printf '%s\n' "$PP" | gpg --batch --pinentry-mode loopback --passphrase-fd 0 -d "$ARCHIVE" 2>/dev/null | tar -tz >/dev/null \
  && echo "Verifikasi isi arsip: OK" || { echo "VERIFIKASI GAGAL. Folder asli TIDAK dihapus."; exit 4; }
unset PP
sha256sum "$ARCHIVE" | tee "$ARCHIVE.sha256"

cat <<EOF

SELESAI. Folder asli TIDAK dihapus oleh skrip ini:
  $EVIDENCE_DIR  dan  $EVIDENCE_DIR.tar.gz (belum terenkripsi)
Langkah Anda: pastikan checksum cocok (sha256sum -c $ARCHIVE.sha256), salin arsip .gpg ke tempat lain
(bukan hanya di VPS ini), lalu putuskan sendiri apakah menghapus salinan tanpa enkripsi.
Catatan jujur: 'rm' TIDAK menghapus aman (data bisa dipulihkan dari disk/snapshot). 'shred' kurang
andal di SSD/VPS. Batasi dengan izin 700 dan hapus hanya setelah salinan terenkripsi terverifikasi.
EOF
