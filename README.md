# 🎬 Sistem Manajemen Tiket & Fasilitas Studio Bioskop Berbasis PHP-OOP
> **Simulasi / Latihan Pra-UAS Praktikum PBO TRPL 1B**

Dokumentasi ini dibuat oleh **Achmal Maulana** ([Achmal-002](https://github.com/Achmal-002)) sebagai bukti penyelesaian tugas praktikum Pemrograman Berorientasi Objek.

---

## 📌 Deskripsi Proyek
Proyek ini adalah sistem manajemen tiket bioskop dinamis yang dirancang menggunakan konsep pemrograman berorientasi objek (**OOP**) murni dan menggunakan bahasa pemrograman **PHP**. Sistem memodelkan berbagai jenis studio bioskop (**Regular**, **IMAX**, dan **Velvet**) dengan menghitung harga tiket secara polimorfik serta menampilkan fasilitas unik masing-masing tipe studio secara dinamis.

Frontend aplikasi dibangun dengan mengadopsi tema **"Modern Light Blue"** menggunakan **Tailwind CSS (CDN)** untuk menyajikan visualisasi data transaksi tiket yang interaktif, bersih, dan informatif bagi pengguna.

---

## 🌟 Penerapan Pilar OOP & Fitur Utama

Sistem ini menerapkan empat pilar utama Pemrograman Berorientasi Objek:

### 1. 🌀 Abstraction (Abstraksi)
* Diimplementasikan melalui abstract class [`Tiket`](file:///c:/Users/Administrator/Documents/PROJECT%20PBO%20UAS%20KEL%203/Latihan_PBO_Achmal_Maulana/Tiket.php) yang tidak dapat diinstansiasi secara langsung melainkan berfungsi sebagai cetak biru (*blueprint*).
* Memiliki metode abstrak `hitungTotalHarga()` dan `tampilkanInfoFasilitas()` yang wajib diimplementasikan oleh setiap kelas anak konkrit.

### 2. 🔒 Encapsulation (Enkapsulasi)
* Properti dasar seperti `id_tiket`, `nama_film`, `jadwal_tayang`, `jumlah_kursi`, dan `hargaDasarTiket` dilindungi dengan hak akses `protected` pada kelas induk.
* Properti unik spesifik kelas anak dideklarasikan sebagai `private` (misalnya: `tipeAudio`, `kacamata3dId`, `bantalSelimutPack`).
* Akses data dari luar kelas dijembatani secara aman menggunakan metode **Getter** (seperti `getIdTiket()`, `getNamaFilm()`, dll).

### 3. 🌿 Inheritance (Pewarisan)
* Tiga subclass konkrit mewarisi sifat dan perilaku dari kelas induk `Tiket`:
  1. [`TiketRegular`](file:///c:/Users/Administrator/Documents/PROJECT%20PBO%20UAS%20KEL%203/Latihan_PBO_Achmal_Maulana/TiketRegular.php)
  2. [`TiketIMAX`](file:///c:/Users/Administrator/Documents/PROJECT%20PBO%20UAS%20KEL%203/Latihan_PBO_Achmal_Maulana/TiketIMAX.php)
  3. [`TiketVelvet`](file:///c:/Users/Administrator/Documents/PROJECT%20PBO%20UAS%20KEL%203/Latihan_PBO_Achmal_Maulana/TiketVelvet.php)
* Setiap constructor kelas anak memanggil `parent::__construct()` untuk memastikan properti dasar terinisialisasi dengan benar.

### 4. 🧬 Polymorphism (Polimorfisme)
* Terjadi *Method Overriding* pada `hitungTotalHarga()` dan `tampilkanInfoFasilitas()` di setiap subclass.
* **Perhitungan Tarif**:
  * **Regular**: `jumlah_kursi * harga_dasar`
  * **IMAX**: `(jumlah_kursi * harga_dasar) + Rp35.000` (surcharge tetap)
  * **Velvet**: `(jumlah_kursi * harga_dasar) * 1.50` (surcharge tarif 50%)
* Ketika merender dashboard, program memanggil metode yang sama dari array induk secara dinamis tanpa perlu mengecek tipe objek secara manual.

---

## 📂 Arsitektur Struktur Folder

Berikut adalah visualisasi struktur direktori proyek ini:

```bash
Latihan_PBO_Achmal_Maulana/
├── koneksi/
│   └── database.php         # Kelas koneksi database menggunakan PDO & try-catch
├── database_latihan.sql     # Skrip dump database & 20 data sampel (MySQL)
├── Tiket.php                # Abstract class utama (Parent)
├── TiketRegular.php         # Subclass konkrit kelas Regular
├── TiketIMAX.php            # Subclass konkrit kelas IMAX
├── TiketVelvet.php          # Subclass konkrit kelas Velvet (Luxury)
├── index.php                # Main dashboard UI & implementasi Factory Pattern
└── README.md                # Dokumentasi proyek (File ini)
```

---

## 🛠️ Panduan Instalasi Lokal

Ikuti langkah-langkah di bawah ini untuk menjalankan aplikasi pada komputer Anda:

### 1. Kloning Repositori
Clone repositori Git ini ke direktori local server Anda (misal di folder `htdocs` bagi pengguna XAMPP):
```bash
git clone https://github.com/GomalRajaGula/Latihan_PBO_Achmal-Maulana.git
```

### 2. Import Database
1. Jalankan control panel **XAMPP** dan aktifkan modul **Apache** dan **MySQL**.
2. Buka web browser dan arahkan ke alamat [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Buat database baru dengan nama `DB_LATIHAN_PBO_TRPL1B_Achmal_Maulana`.
4. Pilih database tersebut, lalu masuk ke tab **Import**.
5. Pilih file [`database_latihan.sql`](file:///c:/Users/Administrator/Documents/PROJECT%20PBO%20UAS%20KEL%203/Latihan_PBO_Achmal_Maulana/database_latihan.sql) yang terletak di direktori proyek, lalu klik **Go** / **Import**.

### 3. Konfigurasi Koneksi Database
Jika Anda menggunakan kredensial database MySQL bawaan XAMPP default (Host: `localhost`, User: `root`, Password: ``), maka Anda tidak perlu melakukan perubahan apa pun pada file [`koneksi/database.php`](file:///c:/Users/Administrator/Documents/PROJECT%20PBO%20UAS%20KEL%203/Latihan_PBO_Achmal_Maulana/koneksi/database.php). Namun jika berbeda, Anda dapat menyesuaikan properti kelas berikut:
```php
private string $host = "localhost";
private string $username = "username_anda";
private string $password = "password_anda";
```

### 4. Jalankan Aplikasi
Akses aplikasi melalui browser Anda dengan URL berikut:
```text
http://localhost/Latihan_PBO_Achmal_Maulana/index.php
```

---

## 📋 Standar Pesan Commit Git

Proyek dikerjakan secara bertahap sesuai alur tugas yang terstruktur. Berikut aturan standar pesan commit (*commit message*) yang diterapkan:

| Tahap | Judul Tahapan Komitmen Git | Deskripsi & Ruang Lingkup |
| :---: | :--- | :--- |
| **Tahap 1 & 2** | `[Tahap 1 & 2] Membuat database, tabel_tiket dengan 20 data sampel, dan ekspor file .sql` | Membuat database MySQL, mendesain tabel `tabel_tiket`, mengisi data seed, dan mengekspor ke `.sql`. |
| **Tahap 3** | `[Tahap 3] Membuat koneksi database dan abstract class Tiket beserta properti protected` | Membuat kelas `Database` berbasis PDO dan membuat abstract class `Tiket` (parent class). |
| **Tahap 4** | `[Tahap 4] Membuat subclass TiketRegular, TiketIMAX, dan TiketVelvet dengan properti spesifik` | Menambahkan file konkrit kelas anak dengan properti enkapsulasi `private` masing-masing. |
| **Tahap 5** | `[Tahap 5] Mengimplementasikan overriding method hitungTotalHarga pada setiap subclass sesuai ketentuan tarif` | Mengimplementasikan logika perhitungan tarif tiket dan metode fasilitas di masing-masing anak. |
| **Tahap 6** | `[Tahap 6] Membangun UI Dashboard Modern Light Blue dan integrasi Factory Pattern` | Pembuatan halaman `index.php`, integrasi Factory Pattern dinamis, styling UI Tailwind CSS, dan dokumentasi `README.md`. |

---
*Proyek ini diselesaikan sebagai bagian dari pemenuhan kompetensi mata kuliah Pemrograman Berorientasi Objek di Program Studi D4 Teknologi Rekayasa Perangkat Lunak.*
# Portfolio_Achmal_Maulana
