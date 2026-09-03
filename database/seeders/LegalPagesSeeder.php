<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Konten awal Syarat & Ketentuan dan Kebijakan Privasi — klausul umum yang
 * wajar untuk toko online kecil-menengah di Indonesia. Pemilik toko tetap
 * perlu meninjau & menyesuaikan (terutama alamat, jenis produk, dan
 * kebijakan retur spesifik) sebelum publish sungguhan, tapi ini jauh lebih
 * baik daripada halaman kosong. updateOrCreate supaya aman dijalankan ulang.
 */
class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        Page::updateOrCreate(
            ['slug' => 'syarat-ketentuan'],
            [
                'title' => 'Syarat & Ketentuan',
                'is_active' => true,
                'content' => <<<'TEXT'
Dengan mengakses dan menggunakan situs ini, Anda dianggap telah membaca, memahami, dan menyetujui seluruh syarat & ketentuan berikut.

1. Akun Pengguna
Anda bertanggung jawab menjaga kerahasiaan email dan kata sandi akun Anda, serta seluruh aktivitas yang terjadi di bawah akun tersebut. Segera hubungi kami bila Anda menduga akun Anda diakses tanpa izin.

2. Pemesanan & Harga
Seluruh harga produk sudah mencakup pajak (jika berlaku) dan ditampilkan dalam Rupiah. Kami berhak mengubah harga produk sewaktu-waktu tanpa pemberitahuan sebelumnya, namun perubahan tersebut tidak berlaku untuk pesanan yang sudah dibuat. Pesanan dianggap sah setelah pembayaran berhasil diverifikasi.

3. Pembayaran
Pembayaran dapat dilakukan lewat transfer bank manual maupun metode instan (kartu, virtual account, e-wallet, QRIS) melalui mitra pembayaran kami. Pesanan dengan transfer manual akan diproses setelah bukti transfer diverifikasi oleh tim kami.

4. Pengiriman
Estimasi waktu pengiriman yang ditampilkan bersifat perkiraan, bukan jaminan mutlak, karena bergantung pada kurir dan kondisi di luar kendali kami. Risiko kehilangan atau kerusakan barang berpindah kepada Anda begitu barang diserahkan ke kurir, kecuali terbukti akibat kesalahan kami dalam pengemasan.

5. Retur, Komplain & Pembatalan
Pengajuan retur/komplain mengikuti kebijakan yang tercantum di halaman FAQ dan hanya berlaku untuk pesanan yang sudah berstatus selesai. Pembatalan pesanan hanya dapat dilakukan sebelum status "Dikirim" dan mengembalikan stok secara otomatis.

6. Hak Kekayaan Intelektual
Seluruh konten di situs ini (termasuk teks, gambar produk, dan logo) adalah milik kami atau pemasok kami dan dilindungi hukum yang berlaku. Dilarang menyalin atau menggunakannya untuk tujuan komersial tanpa izin tertulis.

7. Batasan Tanggung Jawab
Kami tidak bertanggung jawab atas kerugian tidak langsung yang timbul dari penggunaan produk di luar petunjuk pemakaian yang wajar.

8. Perubahan Syarat & Ketentuan
Kami berhak mengubah syarat & ketentuan ini sewaktu-waktu. Perubahan berlaku efektif sejak dipublikasikan di halaman ini.

9. Hukum yang Berlaku
Syarat & ketentuan ini tunduk pada hukum yang berlaku di Republik Indonesia.

Jika ada pertanyaan mengenai syarat & ketentuan ini, silakan hubungi kami lewat fitur chat di situs ini.
TEXT,
            ]
        );

        Page::updateOrCreate(
            ['slug' => 'kebijakan-privasi'],
            [
                'title' => 'Kebijakan Privasi',
                'is_active' => true,
                'content' => <<<'TEXT'
Kami menghargai privasi Anda. Kebijakan ini menjelaskan data apa saja yang kami kumpulkan dan bagaimana kami menggunakannya.

1. Data yang Kami Kumpulkan
Nama, email, nomor telepon, dan alamat pengiriman yang Anda berikan saat mendaftar atau checkout, serta riwayat pesanan dan komunikasi Anda dengan tim CS kami.

2. Penggunaan Data
Data Anda digunakan untuk memproses pesanan, mengirim notifikasi terkait pesanan (status, resi, dsb), merespons pertanyaan Anda, dan meningkatkan layanan kami. Kami tidak menjual data pribadi Anda kepada pihak ketiga.

3. Berbagi Data dengan Pihak Ketiga
Data Anda hanya dibagikan sebatas yang diperlukan untuk memenuhi pesanan: kepada mitra pembayaran (untuk memproses transaksi) dan jasa kurir (untuk pengiriman barang).

4. Cookie & Analitik
Situs ini menggunakan cookie untuk fungsi dasar (mis. keranjang belanja) dan, bila Anda menyetujuinya lewat banner cookie, untuk analitik guna memahami penggunaan situs. Anda dapat menolak cookie analitik kapan saja.

5. Keamanan Data
Kami menyimpan kata sandi Anda dalam bentuk terenkripsi dan menerapkan langkah-langkah keamanan wajar untuk melindungi data Anda dari akses tidak sah.

6. Hak Anda
Anda berhak mengakses dan memperbarui data akun Anda kapan saja lewat halaman akun, serta meminta penghapusan akun Anda beserta data pribadinya lewat halaman Keamanan Akun (data pesanan tetap kami simpan sesuai kewajiban pembukuan).

7. Perubahan Kebijakan
Kami dapat memperbarui kebijakan privasi ini sewaktu-waktu. Perubahan berlaku efektif sejak dipublikasikan di halaman ini.

Jika ada pertanyaan mengenai kebijakan privasi ini, silakan hubungi kami lewat fitur chat di situs ini.
TEXT,
            ]
        );
    }
}
