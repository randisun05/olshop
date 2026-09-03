<?php

namespace Database\Seeders;

use App\Models\FaqEntry;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Konten FAQ nyata (bukan lorem ipsum) dipakai dua tempat: entri terstruktur
 * di FaqEntry (dicocokkan kata kunci oleh ChatBotResponder untuk balasan
 * otomatis di chat) dan halaman publik /halaman/faq (Page CMS) supaya
 * pengunjung yang tidak chat pun bisa baca. updateOrCreate supaya aman
 * dijalankan ulang tanpa duplikat.
 */
class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            [
                'question' => 'Bagaimana cara memesan/berbelanja di toko ini?',
                'answer' => 'Mudah! (1) Pilih produk yang diinginkan, tentukan varian (ukuran/warna) kalau ada, lalu klik "Tambah ke Keranjang". (2) Buka Keranjang, cek kembali pesanan Anda, lalu klik Checkout. (3) Isi alamat pengiriman dan pilih metode pembayaran (transfer bank atau kartu/VA/e-wallet/QRIS via Midtrans). (4) Selesaikan pembayaran, dan pesanan Anda akan segera diproses. Anda tidak wajib membuat akun — bisa checkout sebagai tamu.',
                'keywords' => 'cara pesan, cara order, cara belanja, cara beli, bagaimana memesan, checkout',
                'category' => 'Order',
                'sort_order' => 1,
            ],
            [
                'question' => 'Metode pembayaran apa saja yang tersedia?',
                'answer' => 'Kami menerima transfer bank manual (unggah bukti transfer setelah pembayaran) maupun pembayaran instan lewat Midtrans: kartu kredit/debit, virtual account (VA) semua bank besar, e-wallet (GoPay, OVO, Dana, dst), dan QRIS. Pembayaran via Midtrans langsung terverifikasi otomatis, sedangkan transfer manual akan diverifikasi tim kami setelah bukti transfer diunggah.',
                'keywords' => 'pembayaran, bayar, transfer, midtrans, va, virtual account, e-wallet, qris, kartu kredit',
                'category' => 'Pembayaran',
                'sort_order' => 2,
            ],
            [
                'question' => 'Berapa ongkos kirim dan ke mana saja bisa dikirim?',
                'answer' => 'Ongkos kirim dihitung otomatis berdasarkan wilayah tujuan yang Anda pilih saat checkout — besarannya tampil jelas sebelum Anda membayar, tidak ada biaya tersembunyi. Silakan cek pilihan wilayah pengiriman yang tersedia di halaman checkout.',
                'keywords' => 'ongkir, ongkos kirim, biaya kirim, wilayah kirim, kirim ke mana',
                'category' => 'Pengiriman',
                'sort_order' => 3,
            ],
            [
                'question' => 'Bagaimana cara melacak status pesanan saya?',
                'answer' => 'Buka menu "Lacak Pesanan" di halaman utama, lalu masukkan nomor pesanan dan email yang Anda gunakan saat checkout. Anda akan melihat status terkini (diproses/dikirim/selesai), riwayat lengkap perubahan status, dan nomor resi pengiriman begitu pesanan dikirim. Kalau Anda login, semua pesanan juga bisa dilihat langsung dari halaman akun Anda.',
                'keywords' => 'lacak, resi, status pesanan, dimana pesanan, cek pesanan, nomor resi, tracking',
                'category' => 'Order',
                'sort_order' => 4,
            ],
            [
                'question' => 'Berapa lama pesanan diproses dan sampai tujuan?',
                'answer' => 'Setelah pembayaran terverifikasi, tim kami akan memproses (mengemas) pesanan Anda, biasanya dalam 1-2 hari kerja, lalu menyerahkannya ke kurir. Estimasi waktu sampai tergantung jarak dan kurir yang digunakan — nomor resi akan diberikan begitu pesanan dikirim supaya Anda bisa memantau langsung lewat aplikasi/situs kurir.',
                'keywords' => 'berapa lama, kapan sampai, proses berapa hari, estimasi kirim',
                'category' => 'Pengiriman',
                'sort_order' => 5,
            ],
            [
                'question' => 'Bagaimana kalau saya ingin retur atau komplain produk?',
                'answer' => 'Kami menerima pengajuan retur/komplain untuk pesanan yang sudah berstatus "Selesai", lewat halaman detail pesanan Anda — sertakan foto sebagai bukti kalau perlu. Tim CS kami akan meninjau dan merespons pengajuan Anda, dan Anda akan mendapat notifikasi email di setiap perubahan status pengajuan.',
                'keywords' => 'retur, komplain, rusak, cacat, tidak sesuai, tukar barang, kembalikan barang',
                'category' => 'Retur',
                'sort_order' => 6,
            ],
            [
                'question' => 'Apakah pesanan saya bisa dibatalkan?',
                'answer' => 'Pesanan yang belum berstatus "Dikirim" masih berpotensi bisa dibatalkan oleh tim kami — silakan hubungi CS lewat chat secepatnya dengan menyertakan nomor pesanan Anda, dan stok akan dikembalikan otomatis kalau pembatalan disetujui.',
                'keywords' => 'batal, cancel, batalkan pesanan',
                'category' => 'Order',
                'sort_order' => 7,
            ],
            [
                'question' => 'Di mana saya bisa melihat spesifikasi lengkap suatu produk?',
                'answer' => 'Buka halaman detail produk (klik produknya dari katalog) — di sana ada deskripsi lengkap, pilihan varian (ukuran/warna beserta stok & harga masing-masing), beberapa foto produk, serta rating & ulasan dari pembeli lain yang sudah menerima barangnya. Kalau ada info yang masih kurang jelas, jangan ragu tanya lewat chat ini!',
                'keywords' => 'spesifikasi, spek, detail produk, ukuran, bahan, warna, varian',
                'category' => 'Produk',
                'sort_order' => 8,
            ],
            [
                'question' => 'Kenapa harus belanja di toko ini? Apa keunggulannya?',
                'answer' => 'Beberapa alasan pelanggan kami betah belanja di sini: (1) Transparan — harga, ongkir, dan pajak (kalau ada) sudah jelas terlihat sebelum bayar, tidak ada biaya kejutan. (2) Banyak pilihan pembayaran, termasuk instan lewat Midtrans. (3) Bisa lacak pesanan kapan saja tanpa perlu login. (4) Tim CS responsif lewat chat langsung di situs ini. (5) Ulasan pembeli asli tampil di setiap produk, jadi Anda bisa belanja dengan lebih yakin. (6) Ada kebijakan retur/komplain yang jelas kalau ada masalah dengan pesanan Anda.',
                'keywords' => 'kenapa belanja, keunggulan, kelebihan, kenapa harus, alasan pilih, kepercayaan, terpercaya',
                'category' => 'Umum',
                'sort_order' => 9,
            ],
            [
                'question' => 'Bagaimana cara menghubungi customer service?',
                'answer' => 'Anda sedang menggunakannya! Ketik pertanyaan Anda di sini kapan saja, tim CS kami akan membalas secepatnya pada jam operasional. Untuk pertanyaan seputar pesanan tertentu, sertakan nomor pesanannya supaya kami bisa bantu lebih cepat.',
                'keywords' => 'hubungi cs, customer service, kontak, jam operasional, admin',
                'category' => 'Umum',
                'sort_order' => 10,
            ],
        ];

        foreach ($entries as $entry) {
            FaqEntry::updateOrCreate(
                ['question' => $entry['question']],
                [...$entry, 'is_active' => true]
            );
        }

        $pageContent = collect($entries)
            ->map(fn (array $e) => "{$e['question']}\n{$e['answer']}")
            ->implode("\n\n");

        Page::updateOrCreate(
            ['slug' => 'faq'],
            [
                'title' => 'FAQ — Pertanyaan yang Sering Diajukan',
                'content' => $pageContent,
                'is_active' => true,
            ]
        );
    }
}
