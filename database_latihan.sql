-- Skrip SQL untuk Pembuatan Database dan Tabel
-- Nama Database: DB_LATIHAN_PBO_TRPL1B_Achmal_Maulana
-- Digunakan untuk latihan Pemrograman Berorientasi Objek (PBO)

-- 1. Membuat Database Baru jika belum ada
CREATE DATABASE IF NOT EXISTS DB_LATIHAN_PBO_TRPL1B_Achmal_Maulana;
USE DB_LATIHAN_PBO_TRPL1B_Achmal_Maulana;

-- 2. Membuat Tabel 'tiket' yang sesuai dengan struktur properties pada class Tiket
CREATE TABLE IF NOT EXISTS tiket (
    id_tiket INT AUTO_INCREMENT PRIMARY KEY,
    nama_film VARCHAR(255) NOT NULL,
    jadwal_tayang VARCHAR(100) NOT NULL,
    jumlah_kursi INT NOT NULL DEFAULT 0,
    harga_dasar_tiket DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Memasukkan Data Sampel (Seed Data) untuk kebutuhan pengujian
INSERT INTO tiket (nama_film, jadwal_tayang, jumlah_kursi, harga_dasar_tiket) VALUES
('Doctor Strange in the Multiverse of Madness', '2026-06-18 13:00:00', 2, 45000.00),
('Avatar: The Way of Water', '2026-06-18 16:30:00', 4, 50000.00),
('Interstellar', '2026-06-19 19:00:00', 1, 40000.00),
('Suzume no Tojimari', '2026-06-19 21:15:00', 3, 35000.00);
