#!/usr/bin/env bash
# Tes restore backup ke container MySQL SEMENTARA. Tidak menyentuh produksi.
# Pemakaian: bash test_restore_backup.sh /var/backups/mgi/evidence_XXXX/db.sql
set -Eeuo pipefail

DUMP="${1:?Beri path file db.sql}"
[ -s "$DUMP" ] || { echo "File dump kosong / tidak ada: $DUMP"; exit 2; }
NAME="mgi_restore_test"
IMG="${IMG:-mysql:8.0}"

cleanup() { docker rm -f "$NAME" >/dev/null 2>&1 || true; echo "Container uji dihapus."; }
trap cleanup EXIT
docker rm -f "$NAME" >/dev/null 2>&1 || true

# Tanpa -p (tidak ada port terbuka), jaringan bawaan terpisah, kosong password hanya di dalam container uji.
docker run -d --name "$NAME" -e MYSQL_ALLOW_EMPTY_PASSWORD=yes "$IMG" >/dev/null

echo "Menunggu MySQL uji siap (maks 120 detik)..."
for i in $(seq 1 40); do
  docker exec "$NAME" mysql -uroot -e "SELECT 1" >/dev/null 2>&1 && break
  sleep 3
  [ "$i" = 40 ] && { echo "MySQL uji tidak siap."; exit 3; }
done

docker exec "$NAME" mysql -uroot -e "CREATE DATABASE restore_test CHARACTER SET utf8mb4;"
echo "Mengimpor dump..."
docker exec -i "$NAME" mysql -uroot restore_test < "$DUMP"

echo
echo "=== Jumlah tabel dan baris (bandingkan dengan produksi) ==="
docker exec "$NAME" mysql -uroot -N -B -e \
 "SELECT COUNT(*) AS jumlah_tabel FROM information_schema.tables WHERE table_schema='restore_test';"
docker exec "$NAME" mysql -uroot -N -B restore_test -e \
 "SELECT 'admin_users', COUNT(*) FROM admin_users UNION ALL SELECT 'admin_logs', COUNT(*) FROM admin_logs;" || true
echo
echo "HASIL: restore BERHASIL jika tidak ada error di atas dan angka masuk akal."
