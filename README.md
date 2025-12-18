<div align="center">
  
# 🐺 WOLVEBITE COMMUNITY & ACADEMY 🏀

### *Platform Komunitas Basket Terintegrasi dengan Akademi Pelatihan*

[![PHP Version](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://javascript.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

<img src="assets/images/logo.png" alt="Wolvebite Logo" width="200"/>

**Website full-stack untuk komunitas basket dengan fitur e-commerce, booking lapangan, dan akademi pelatihan profesional.**

[🌐 Demo](#demo) • [✨ Fitur](#-fitur-utama) • [🚀 Instalasi](#-instalasi) • [📖 Dokumentasi](#-dokumentasi) • [👥 Tim](#-tim-pengembang)

</div>

---

## 📋 Daftar Isi

- [Tentang Project](#-tentang-project)
- [Fitur Utama](#-fitur-utama)
- [Tech Stack](#-tech-stack)
- [Struktur Database](#-struktur-database)
- [Instalasi](#-instalasi)
- [Penggunaan](#-penggunaan)
- [Screenshot](#-screenshot)
- [Tim Pengembang](#-tim-pengembang)

---

## 🎯 Tentang Project

**Wolvebite** adalah platform digital untuk komunitas basket yang mengintegrasikan dua layanan utama:

| Platform | Deskripsi |
|----------|-----------|
| 🏪 **Community** | E-commerce perlengkapan basket, booking lapangan, dan download materi |
| 🎓 **Academy** | Akademi pelatihan dengan enrollment, jadwal kelas, dan modul pembelajaran |

### Tujuan Pengembangan
- ✅ Mengimplementasikan operasi **CRUD** secara komprehensif
- ✅ Menerapkan fitur **Upload & Download** file
- ✅ Membangun koneksi **Database PHP-MySQL** yang aman
- ✅ Membuat **Validasi Form** client-side & server-side
- ✅ Mengembangkan sistem **Autentikasi Multi-Role**

---

## ✨ Fitur Utama

### 🏪 Wolvebite Community
| Fitur | Deskripsi |
|-------|-----------|
| 🛒 **Shop** | E-commerce dengan cart, checkout, dan payment |
| 🏟️ **Booking** | Reservasi lapangan basket dengan slot waktu |
| 📥 **Download** | Materi dan dokumen komunitas |
| 📄 **Invoice** | Generate invoice PDF untuk transaksi |
| 👤 **Profile** | Manajemen akun pengguna |

### 🎓 Wolvebite Academy
| Fitur | Deskripsi |
|-------|-----------|
| 📚 **Programs** | Daftar program pelatihan berbagai level |
| 👨‍🏫 **Coaches** | Profil pelatih profesional |
| 📅 **Schedule** | Jadwal mingguan kelas |
| 📝 **Enrollment** | Pendaftaran dengan validasi usia |
| 📖 **Modules** | Download modul pembelajaran |
| 🎫 **Booking** | Reservasi kelas pelatihan |

### 🔐 Admin Dashboard
| Fitur | Deskripsi |
|-------|-----------|
| 📊 **Dashboard** | Statistik dan overview |
| 📦 **Products** | CRUD produk shop |
| 👥 **Users** | Manajemen pengguna |
| 📋 **Orders** | Kelola pesanan |
| ✅ **Approvals** | Approval enrollment & booking |

---

## 🛠️ Tech Stack

<div align="center">

| Layer | Teknologi |
|-------|-----------|
| **Backend** | ![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white) PHP 8.2 Native |
| **Database** | ![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white) MariaDB 10.4 |
| **Frontend** | ![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white) ![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white) ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black) |
| **Server** | ![Apache](https://img.shields.io/badge/Apache-D22128?style=flat-square&logo=apache&logoColor=white) XAMPP |
| **Icons** | ![FontAwesome](https://img.shields.io/badge/Font_Awesome-339AF0?style=flat-square&logo=fontawesome&logoColor=white) |

</div>

---

## 🗄️ Struktur Database

```
wolvebite_db/
├── users                    # Akun pengguna
├── products                 # Produk shop
├── cart_items               # Keranjang belanja
├── orders                   # Pesanan
├── order_items              # Detail pesanan
├── bookings                 # Booking lapangan
├── uploads                  # File download
├── academy_coaches          # Pelatih
├── academy_programs         # Program pelatihan
├── academy_schedule         # Jadwal kelas
├── academy_enrollments      # Pendaftaran siswa
├── academy_bookings         # Booking kelas
└── academy_modules          # Modul pembelajaran
```

**Total: 13 Tabel dengan Relational Database Design**

---

## 🚀 Instalasi

### Prasyarat
- ✅ XAMPP (PHP 8.x + MariaDB)
- ✅ Web Browser Modern

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/dezel21/wolvebite-community.git

# 2. Pindahkan ke htdocs
mv wolvebite-community C:/xampp/htdocs/pemweb

# 3. Jalankan XAMPP (Apache + MySQL)

# 4. Import database
# - Buka phpMyAdmin
# - Buat database: wolvebite_db
# - Import: database.sql dan academy/database_academy.sql

# 5. Akses website
# http://localhost/pemweb/
```

---

## 📖 Penggunaan

### Akun Default

| Role | Email | Password |
|------|-------|----------|
| 👑 Admin | `admin@wolvebite.com` | `password` |

### Quick Start
1. **Register** akun baru atau login dengan akun default
2. **Browse** produk di Shop atau program di Academy
3. **Checkout** produk atau **Enroll** ke program
4. **Admin** bisa mengelola semua melalui Dashboard

---

## 📱 Screenshot

<div align="center">

| Homepage | Shop | Academy |
|:--------:|:----:|:-------:|
| 🏠 | 🛒 | 🎓 |

| Admin Dashboard | Invoice | Profile |
|:---------------:|:-------:|:-------:|
| 📊 | 📄 | 👤 |

</div>

---

## 📁 Struktur Project

```
pemweb/
├── 📂 academy/              # Platform Academy
│   ├── 📂 admin/           # Admin Academy
│   ├── 📂 includes/        # Header, Footer, Functions
│   ├── 📂 assets/          # CSS Academy
│   └── 📂 uploads/         # File uploads
├── 📂 admin/                # Admin Community
├── 📂 assets/               # CSS, JS, Images
├── 📂 config/               # Database config
├── 📂 controllers/          # Logic handlers
├── 📂 includes/             # Shared components
├── 📂 uploads/              # User uploads
├── 📄 index.php             # Homepage
├── 📄 shop.php              # Shop page
├── 📄 booking.php           # Booking page
├── 📄 download.php          # Download page
└── 📄 database.sql          # SQL schema
```

---

## 🔒 Keamanan

- ✅ **Password Hashing** - `password_hash()` dengan bcrypt
- ✅ **Input Sanitization** - `htmlspecialchars()` untuk XSS prevention
- ✅ **SQL Escape** - `mysqli_real_escape_string()` untuk SQL injection
- ✅ **Session Management** - Secure session handling
- ✅ **Access Control** - Role-based access (user/admin)

---

## 👥 Tim Pengembang

<div align="center">

| Nama | Role |
|------|------|
| **Al-ghifari Rahbani Ramadhan** | Developer |
| **Dzulhas Syahara Muthahari** | Developer |
| **Muhammad Fawwaz Satriadi** | Developer |
| **Bagas Malik Ibrahim** | Developer |

</div>

---

## 📄 Lisensi

Project ini dibuat untuk keperluan akademik - **Proyek Akhir Pemrograman Web**

---

<div align="center">

### 🐺 *"Train Hard, Play Smart, Be a Wolf!"* 🏀

**Made with ❤️ by Wolvebite Team**

*Desember 2025*

</div>
