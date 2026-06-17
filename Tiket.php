<?php
/**
 * Class Tiket
 * 
 * Merupakan abstract class yang merepresentasikan data tiket bioskop.
 * Menerapkan prinsip Abstraksi dan Enkapsulasi dalam Object-Oriented Programming (OOP).
 */
abstract class Tiket {
    // Protected properties
    protected ?int $id_tiket;
    protected string $nama_film;
    protected string $jadwal_tayang;
    protected int $jumlah_kursi;
    protected float $hargaDasarTiket;

    /**
     * Constructor untuk menginisialisasi properti objek dari array asosiatif database.
     * 
     * @param array $data Array asosiatif berisi data baris (row) dari database
     */
    public function __construct(array $data) {
        $this->id_tiket        = isset($data['id_tiket']) ? (int)$data['id_tiket'] : null;
        $this->nama_film       = $data['nama_film'] ?? '';
        $this->jadwal_tayang   = $data['jadwal_tayang'] ?? '';
        $this->jumlah_kursi    = isset($data['jumlah_kursi']) ? (int)$data['jumlah_kursi'] : 0;
        
        // Mendukung penamaan database snake_case (harga_dasar_tiket) maupun camelCase (hargaDasarTiket)
        $this->hargaDasarTiket = isset($data['hargaDasarTiket']) 
            ? (float)$data['hargaDasarTiket'] 
            : (isset($data['harga_dasar_tiket']) ? (float)$data['harga_dasar_tiket'] : 0.0);
    }

    /**
     * Metode abstrak untuk menghitung total harga tiket berdasarkan jenis tiket dan jumlah kursi.
     * Harus diimplementasikan oleh setiap subclass (misal: TiketRegular, TiketVIP).
     * 
     * @return float Total harga tiket
     */
    abstract public function hitungTotalHarga(): float;

    /**
     * Metode abstrak untuk menampilkan informasi fasilitas tambahan yang tersedia untuk jenis tiket tersebut.
     * Harus diimplementasikan oleh setiap subclass.
     * 
     * @return string Informasi fasilitas
     */
    abstract public function tampilkanInfoFasilitas(): string;

    // ==========================================
    // GETTER METHODS
    // ==========================================

    /**
     * Mendapatkan ID Tiket
     * 
     * @return int|null
     */
    public function getIdTiket(): ?int {
        return $this->id_tiket;
    }

    /**
     * Mendapatkan Nama Film
     * 
     * @return string
     */
    public function getNamaFilm(): string {
        return $this->nama_film;
    }

    /**
     * Mendapatkan Jadwal Tayang Film
     * 
     * @return string
     */
    public function getJadwalTayang(): string {
        return $this->jadwal_tayang;
    }

    /**
     * Mendapatkan Jumlah Kursi yang Dipesan
     * 
     * @return int
     */
    public function getJumlahKursi(): int {
        return $this->jumlah_kursi;
    }

    /**
     * Mendapatkan Harga Dasar Tiket
     * 
     * @return float
     */
    public function getHargaDasarTiket(): float {
        return $this->hargaDasarTiket;
    }
}
