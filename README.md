# RS Billing - Delta Surya Hospital System

Sistem billing dan kasir berbasis web untuk Rumah Sakit Delta Surya, dibangun dengan **Laravel 12**, **PHP 8.2**, dan **Tailwind CSS**.

Aplikasi ini memungkinkan kasir untuk mencatat transaksi pasien dengan harga tindakan medis dari API eksternal, serta memberikan fitur dashboard marketing untuk melihat statistik asuransi dan revenue.

---

## 📋 Persyaratan Sistem

- **PHP:** 8.2 atau lebih tinggi
- **Composer:** Latest version
- **Node.js & npm:** Untuk mengkompilasi asset frontend
- **Laravel Herd** (opsional, untuk development environment yang lebih mudah)
- **Database:** SQLite (sudah tersedia di repository) atau MySQL

---

## 🚀 Instalasi & Setup

### 1. Clone Repository
```bash
git clone <url-repository>
cd rs-billing-deltasurya
```

### 2. Install Backend Dependencies
```bash
composer install
```

### 3. Install Frontend Dependencies
```bash
npm install
```

### 4. Setup Environment Variable
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```

Kemudian edit file `.env` dan isikan kredensial untuk API Delta Surya:
```env
# Untuk mengambil data asuransi dan tindakan medis dari API Delta Surya
DELTASURYA_EMAIL=email_asli_kamu_saat_melamar@gmail.com
DELTASURYA_PASSWORD=081234567890  # Nomor HP dalam format 08xxx
```

> ⚠️ **PENTING:** Gunakan **email dan nomor HP asli kamu** yang terdaftar di sistem Delta Surya. Email/password dummy hanya untuk login ke aplikasi web, bukan untuk API.

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Jalankan Database Migrations
```bash
php artisan migrate
```

### 7. Jalankan Database Seeder (Untuk Data Dummy)
```bash
php artisan db:seed
```

Ini akan membuat akun default dan sample diskon voucher.

---

## 🎯 Menjalankan Aplikasi

### Opsi 1: Menggunakan Laravel Herd (Recommended)
Jika kamu sudah menginstall Laravel Herd:
```bash
herd link
```
Kemudian buka `http://rs-billing-deltasurya.test` di browser.

Untuk development, buka 2 terminal terpisah:

**Terminal 1 - Jalankan Vite Dev Server:**
```bash
npm run dev
```

**Terminal 2 - Watch Mode (jika diperlukan):**
```bash
php artisan serve
```

### Opsi 2: Menggunakan PHP Built-in Server
```bash
php artisan serve
```
Aplikasi akan berjalan di `http://127.0.0.1:8000`

Untuk frontend development, buka terminal terpisah:
```bash
npm run dev
```

---

## 🔐 Akun Login Default

Setelah menjalankan `php artisan db:seed`, gunakan akun berikut untuk login:

| Role | Email | Password |
|------|-------|----------|
| **Kasir** | `kasir@rsdeltasurya.com` | `password123` |
| **Marketing** | `marketing@rsdeltasurya.com` | `password123` |

---

## ✨ Fitur Utama

### 1. **Halaman Kasir** (`/kasir`)
- Membuat transaksi pembayaran baru
- Memilih asuransi dan tindakan medis dari dropdown (data dari API Delta Surya)
- Menambahkan tindakan medis ke transaksi
- Menghitung harga otomatis dengan diskon (jika ada voucher aktif untuk asuransi tersebut)
- Menghapus tindakan sebelum pembayaran
- Melakukan pembayaran dan mengubah status transaksi
- Mencetak bukti pembayaran dalam format PDF

### 2. **Dashboard Marketing** (`/marketing`)
- Melihat total revenue dari semua transaksi yang sudah dibayar
- Melihat top 5 asuransi dengan kunjungan terbanyak
- Melihat top 5 asuransi dengan revenue terbesar
- Statistik real-time dari database lokal

### 3. **Master Data Voucher/Diskon**
Diskon dapat dikonfigurasi di database dengan tipe:
- **Percentage:** Diskon berdasarkan persentase dengan limit maksimal
- **Fixed:** Diskon nominal tetap

### 4. **Cronjob Laporan Harian**
- Berjalan setiap hari pukul 01:00 AM
- Mengexport transaksi yang dibayar kemarin ke file Excel
- Mengirimkan laporan ke email `interview.deltasurya@yopmail.com`

---

## 📁 Struktur Project

```
rs-billing-deltasurya/
├── app/
│   ├── Http/Controllers/          # Controller utama aplikasi
│   │   ├── CashierTransactionController.php
│   │   └── MarketingDashboardController.php
│   ├── Services/                  # Business logic & integrasi API
│   │   ├── DeltaSuryaApiService.php
│   │   └── Discounts/             # Factory Pattern untuk diskon
│   ├── Exports/                   # Excel Export (Maatwebsite)
│   └── Models/                    # Database Models
├── database/
│   ├── migrations/                # Skema database
│   └── seeders/                   # Data dummy
├── resources/
│   ├── views/                     # Template Blade
│   ├── css/                       # Tailwind CSS
│   └── js/                        # Alpine.js & Axios
├── routes/
│   ├── web.php                    # Web routes
│   └── console.php                # Cronjob schedule
├── config/
│   └── services.php               # Konfigurasi API eksternal
└── .env                           # Environment variable
```

---

## 🔌 Integrasi API Delta Surya

Data asuransi, tindakan medis, dan harga **TIDAK disimpan di database lokal**. Semuanya diambil dari API eksternal secara real-time dengan caching (1 jam) untuk performa optimal.

**Endpoint yang digunakan:**
- `POST /api/v1/auth` - Login untuk mendapat token
- `GET /api/v1/insurances` - Daftar asuransi
- `GET /api/v1/procedures` - Daftar tindakan medis
- `GET /api/v1/procedures/{id}/prices` - Harga tindakan per ID

---

## 🛠️ Development Commands

**Clear Cache:**
```bash
php artisan cache:clear
php artisan config:clear
```

**Rebuild Frontend Assets:**
```bash
npm run build      # Production build
npm run dev        # Development watch mode
```

**Jalankan Tests:**
```bash
./vendor/bin/phpunit
```

---

## 📊 Database Schema

**transactions** - Menyimpan data transaksi pembayaran
**transaction_details** - Menyimpan detail tindakan medis per transaksi
**discount_vouchers** - Master data diskon per asuransi

---

## 🎨 Tech Stack

| Komponen | Library/Framework |
|----------|------------------|
| Backend | Laravel 12 |
| Language | PHP 8.2 |
| Database | SQLite / MySQL |
| Frontend | Blade, Alpine.js, Tailwind CSS |
| PDF Generation | Barryvdh DomPDF |
| Excel Export | Maatwebsite Excel |

---

## 📝 Design Pattern yang Digunakan

- **Factory Pattern:** `DiscountFactory` untuk membuat strategi diskon
- **Strategy Pattern:** `DiscountStrategy` dengan implementasi `PercentageDiscount` & `FixedDiscount`
- **Service Layer:** `DeltaSuryaApiService` untuk isolasi business logic API

---

## ⚠️ Troubleshooting

**Dropdown Asuransi & Tindakan Kosong?**
- Pastikan `.env` sudah benar dengan kredensial Delta Surya
- Jalankan `php artisan cache:clear`
- Cek file log di `storage/logs/laravel.log`

**Database error saat migrate?**
- Pastikan file `database/database.sqlite` exist dan writable

---

## 📌 Catatan Penting

1. Jangan commit `.env` ke repository
2. API Credentials harus disesuaikan dengan akun asli kamu
3. Cache data API tersimpan 1 jam untuk refresh gunakan `php artisan cache:clear`

---

## 📄 License

Proyek ini adalah project test untuk Delta Surya Hospital System.
