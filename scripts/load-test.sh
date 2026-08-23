#!/usr/bin/env bash
#
# Uji beban ringan (light load test) untuk alur baca storefront yang paling
# sering diakses: beranda, katalog, dan detail produk. Tidak menyasar endpoint
# yang di-rate-limit (checkout, cek kupon, dst) karena tujuannya mengukur
# performa dasar di bawah beban ringan, bukan menguji rate limiter.
#
# Pemakaian:
#   php artisan serve --port=8123 &
#   ./scripts/load-test.sh http://127.0.0.1:8123 20 50
#   (base_url) (concurrency) (total request per endpoint)
#
# Butuh: curl, xargs -P (GNU coreutils/findutils, tersedia di Linux/macOS).

set -euo pipefail

BASE_URL="${1:-http://127.0.0.1:8123}"
CONCURRENCY="${2:-10}"
TOTAL="${3:-50}"

PRODUCT_SLUG=$(curl -s "$BASE_URL/produk" | grep -o '"slug":"[^"]*"' | head -1 | sed 's/"slug":"//;s/"//') || true
PRODUCT_SLUG="${PRODUCT_SLUG:-}"

run_endpoint() {
    local path="$1"
    local label="$2"
    local url="${BASE_URL}${path}"

    echo "== ${label} (${url}) =="

    local tmpfile
    tmpfile=$(mktemp)

    seq 1 "$TOTAL" | xargs -P "$CONCURRENCY" -I{} curl -s -o /dev/null -w "%{http_code} %{time_total}\n" "$url" >> "$tmpfile"

    local total_requests ok_count avg_time max_time
    total_requests=$(wc -l < "$tmpfile")
    ok_count=$(awk '$1 == 200' "$tmpfile" | wc -l)
    avg_time=$(awk '{sum+=$2; n++} END {printf "%.3f", sum/n}' "$tmpfile")
    max_time=$(awk '{if ($2>max) max=$2} END {printf "%.3f", max}' "$tmpfile")

    echo "  total request : ${total_requests}"
    echo "  berhasil (200): ${ok_count}"
    echo "  rata-rata     : ${avg_time}s"
    echo "  terlambat     : ${max_time}s"
    echo

    rm -f "$tmpfile"
}

echo "Uji beban ringan — concurrency=${CONCURRENCY}, total=${TOTAL} per endpoint"
echo "================================================================"

run_endpoint "/" "Beranda"
run_endpoint "/produk" "Katalog Produk"

if [ -n "$PRODUCT_SLUG" ]; then
    run_endpoint "/produk/${PRODUCT_SLUG}" "Detail Produk"
else
    echo "Lewati tes detail produk: tidak ada produk di database (jalankan seeder dulu)."
fi

echo "Selesai."
