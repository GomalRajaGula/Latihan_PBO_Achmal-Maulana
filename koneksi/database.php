<?php
/**
 * Class Database
 * 
 * Digunakan untuk menangani koneksi ke database MySQL menggunakan ekstensi PDO (PHP Data Objects).
 * Menerapkan standar Object-Oriented Programming (OOP) dan error handling try-catch secara profesional.
 * 
 * Nama Database: DB_LATIHAN_PBO_TRPL1B_Achmal_Maulana
 */

class Database {
    // Properti konfigurasi database
    private string $host = "localhost";
    private string $username = "root";
    private string $password = "";
    private string $db_name = "DB_LATIHAN_PBO_TRPL1B_Achmal_Maulana";
    private string $charset = "utf8mb4";
    private ?PDO $conn = null;

    /**
     * Mendapatkan koneksi database PDO.
     * 
     * @return PDO|null
     * @throws Exception jika terjadi kesalahan koneksi
     */
    public function getConnection(): ?PDO {
        $this->conn = null;

        try {
            // Data Source Name (DSN) untuk MySQL
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            // Konfigurasi opsi PDO untuk performa dan keamanan optimal
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mengubah error database menjadi PDOException
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch data sebagai array asosiatif secara default
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Menggunakan prepared statements asli/native
            ];

            // Inisialisasi objek PDO
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $exception) {
            // Logging kesalahan secara internal
            error_log("Database Connection Failure: " . $exception->getMessage());
            
            // Melempar exception yang ramah tanpa membocorkan kredensial database sensitif ke pengguna
            throw new Exception("Koneksi ke database gagal. Silakan hubungi administrator.");
        }

        return $this->conn;
    }
}
