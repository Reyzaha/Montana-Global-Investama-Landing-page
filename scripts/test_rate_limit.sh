#!/usr/bin/env bash
# Uji konkurensi rate limit login admin. HANYA untuk staging/uji. Jangan dijalankan dari IP admin.
# Pemakaian:
#   MODE=php   ./scripts/test_rate_limit.sh   (langsung ke Apache Docker, melewati Nginx)
#   MODE=nginx BASE_URL=https://staging.contoh.com ./scripts/test_rate_limit.sh
# Akun uji: USERNAME bukan admin asli. Password salah sengaja.
set -Eeuo pipefail

MODE="${MODE:-php}"
N="${N:-20}"
USERNAME="${USERNAME:-uji_ratelimit}"
case "$MODE" in
  php)   BASE_URL="${BASE_URL:-http://127.0.0.1:8085}" ;;
  nginx) BASE_URL="${BASE_URL:?isi BASE_URL https staging}" ;;
  *) echo "MODE harus php atau nginx"; exit 2 ;;
esac

echo "PERINGATAN: uji ini akan mengunci IP Anda sementara. Jangan dari IP admin. Ketik YA:"
read -r a; [ "$a" = "YA" ] || exit 0

# Bersihkan blokir lama (jalankan dari direktori proyek di server)
docker compose exec -T mgi-web php scripts/unlock_admin.php --all || { echo "unlock gagal, berhenti"; exit 3; }

URL="$BASE_URL/api/admin/login.php?action=login"
TMP="$(mktemp)"; trap 'rm -f "$TMP"' EXIT
BODY=$(printf '{"username":"%s","password":"salah-sengaja"}' "$USERNAME")

for i in $(seq 1 "$N"); do
  ( curl -sk -o /dev/null -w '%{http_code}\n' -X POST -H 'Content-Type: application/json' \
      --data-binary @- "$URL" <<<"$BODY" >> "$TMP" ) &
done
wait

echo "Hasil (kode HTTP : jumlah):"
sort "$TMP" | uniq -c

if [ "$MODE" = php ]; then
  echo "Harapan: 5 x 401 dan 15 x 429 (PHP memakai slot atomik, N=20)."
else
  cat <<'EOF'
Harapan (lewat Nginx): campuran. Penjelasan:
- limit_req Nginx (rate + burst) menolak sebagian request lebih dulu (kode 503/429 sesuai 'limit_req_status').
- Yang lolos ke PHP paling banyak ~ (rate + burst); dari situ PHP hanya memberi 5 x 401, sisanya 429.
- Jadi 401 harus <= 5. Jika 401 > 5, rate limit PHP bocor. Jumlah total kode non-401 bergantung konfigurasi Nginx.
EOF
fi
echo "Setelah uji: jalankan lagi unlock_admin.php --all."
