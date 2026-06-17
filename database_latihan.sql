-- Skrip SQL untuk Pembuatan Database dan Tabel
-- Nama Database: DB_LATIHAN_PBO_TRPL1B_Achmal_Maulana
-- Nama Tabel: tabel_tiket
-- Digunakan untuk latihan Pemrograman Berorientasi Objek (PBO)

-- 1. Membuat Database Baru jika belum ada
CREATE DATABASE IF NOT EXISTS DB_LATIHAN_PBO_TRPL1B_Achmal_Maulana;
USE DB_LATIHAN_PBO_TRPL1B_Achmal_Maulana;

-- 2. Membuat Tabel 'tabel_tiket' yang memiliki kolom jenis_studio dan properti spesifik subclass
CREATE TABLE IF NOT EXISTS tabel_tiket (
    id_tiket INT AUTO_INCREMENT PRIMARY KEY,
    nama_film VARCHAR(255) NOT NULL,
    jadwal_tayang VARCHAR(100) NOT NULL,
    jumlah_kursi INT NOT NULL DEFAULT 0,
    harga_dasar_tiket DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    jenis_studio ENUM('Regular', 'IMAX', 'Velvet') NOT NULL,
    
    -- Kolom spesifik TiketRegular
    tipe_audio VARCHAR(50) DEFAULT NULL,
    lokasi_baris VARCHAR(20) DEFAULT NULL,
    
    -- Kolom spesifik TiketIMAX
    kacamata_3d_id VARCHAR(50) DEFAULT NULL,
    efek_gerak_fitur VARCHAR(100) DEFAULT NULL,
    
    -- Kolom spesifik TiketVelvet
    bantal_selimut_pack VARCHAR(100) DEFAULT NULL,
    layanan_butler VARCHAR(100) DEFAULT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Memasukkan 20 Data Sampel (Seed Data)
INSERT INTO tabel_tiket (nama_film, jadwal_tayang, jumlah_kursi, harga_dasar_tiket, jenis_studio, tipe_audio, lokasi_baris, kacamata_3d_id, efek_gerak_fitur, bantal_selimut_pack, layanan_butler) VALUES
('Doctor Strange in the Multiverse of Madness', '2026-06-18 13:00:00', 2, 45000.00, 'Regular', 'Dolby Atmos', 'Row C', NULL, NULL, NULL, NULL),
('Avatar: The Way of Water', '2026-06-18 16:30:00', 4, 50000.00, 'IMAX', NULL, NULL, 'IMAX-3D-001', 'D-BOX Motion', NULL, NULL),
('Interstellar', '2026-06-19 19:00:00', 1, 40000.00, 'Velvet', NULL, NULL, NULL, NULL, 'Premium Silk Blanket', 'Personal Butler Service'),
('Suzume no Tojimari', '2026-06-19 21:15:00', 3, 35000.00, 'Regular', 'Dolby 7.1', 'Row D', NULL, NULL, NULL, NULL),
('Spider-Man: Across the Spider-Verse', '2026-06-20 10:00:00', 2, 45000.00, 'Regular', 'Dolby Atmos', 'Row A', NULL, NULL, NULL, NULL),
('Dune: Part Two', '2026-06-20 13:30:00', 3, 55000.00, 'IMAX', NULL, NULL, 'IMAX-3D-002', '4DX Active', NULL, NULL),
('Oppenheimer', '2026-06-20 17:00:00', 2, 60000.00, 'Velvet', NULL, NULL, NULL, NULL, 'Plush Velvet Set', 'VVIP Butler Service'),
('The Dark Knight', '2026-06-20 20:30:00', 5, 40000.00, 'Regular', 'DTS:X', 'Row F', NULL, NULL, NULL, NULL),
('Inception', '2026-06-21 11:00:00', 2, 45000.00, 'Regular', 'Dolby Atmos', 'Row B', NULL, NULL, NULL, NULL),
('Godzilla x Kong: The New Empire', '2026-06-21 14:00:00', 4, 50000.00, 'IMAX', NULL, NULL, 'IMAX-3D-003', 'D-BOX Motion', NULL, NULL),
('Howl\'s Moving Castle', '2026-06-21 17:30:00', 2, 40000.00, 'Velvet', NULL, NULL, NULL, NULL, 'Classic Bedding Pack', 'Standard Butler Service'),
('Kimi no Na wa', '2026-06-21 20:00:00', 3, 35000.00, 'Regular', 'Dolby 5.1', 'Row E', NULL, NULL, NULL, NULL),
('Guardians of the Galaxy Vol. 3', '2026-06-22 13:00:00', 2, 45000.00, 'Regular', 'Dolby Atmos', 'Row C', NULL, NULL, NULL, NULL),
('The Batman', '2026-06-22 16:00:00', 3, 50000.00, 'IMAX', NULL, NULL, 'IMAX-3D-004', '4DX Active', NULL, NULL),
('Titanic: 25th Anniversary', '2026-06-22 19:30:00', 2, 60000.00, 'Velvet', NULL, NULL, NULL, NULL, 'Royal Blanket Set', 'Premium Butler Service'),
('Spirited Away', '2026-06-23 14:00:00', 4, 35000.00, 'Regular', 'Dolby 7.1', 'Row D', NULL, NULL, NULL, NULL),
('John Wick: Chapter 4', '2026-06-23 17:00:00', 2, 45000.00, 'Regular', 'Dolby Atmos', 'Row B', NULL, NULL, NULL, NULL),
('Top Gun: Maverick', '2026-06-23 20:00:00', 3, 55000.00, 'IMAX', NULL, NULL, 'IMAX-3D-005', 'D-BOX Motion', NULL, NULL),
('La La Land', '2026-06-24 16:30:00', 2, 60000.00, 'Velvet', NULL, NULL, NULL, NULL, 'Romantic Velvet Set', 'Executive Butler Service'),
('A Quiet Place: Day One', '2026-06-24 19:30:00', 1, 45000.00, 'Regular', 'Dolby Atmos', 'Row A', NULL, NULL, NULL, NULL);
