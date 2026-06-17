<?php
require_once __DIR__ . '/Tiket.php';

/**
 * Class TiketRegular
 * 
 * Subclass konkrit dari Tiket untuk tipe studio Regular.
 */
class TiketRegular extends Tiket {
    private string $tipeAudio;
    private string $lokasiBaris;

    /**
     * Constructor untuk inisialisasi properti TiketRegular.
     * 
     * @param array $data Array asosiatif berisi data tiket regular
     */
    public function __construct(array $data) {
        // Memanggil constructor parent (Tiket)
        parent::__construct($data);

        // Inisialisasi properti spesifik TiketRegular
        $this->tipeAudio   = $data['tipeAudio'] ?? $data['tipe_audio'] ?? 'Dolby Atmos';
        $this->lokasiBaris = $data['lokasiBaris'] ?? $data['lokasi_baris'] ?? 'Row C';
    }

    /**
     * Menghitung total harga tiket regular.
     * Formula: jumlah_kursi * hargaDasarTiket
     * 
     * @return float
     */
    public function hitungTotalHarga(): float {
        return $this->getJumlahKursi() * $this->getHargaDasarTiket();
    }

    /**
     * Menampilkan informasi fasilitas tambahan Tiket Regular.
     * 
     * @return string
     */
    public function tampilkanInfoFasilitas(): string {
        return "Fasilitas Regular: Audio berkualitas standar tinggi (" . $this->tipeAudio . ") dan tempat duduk nyaman di baris " . $this->lokasiBaris . ".";
    }

    // ==========================================
    // GETTERS & SETTERS
    // ==========================================

    public function getTipeAudio(): string {
        return $this->tipeAudio;
    }

    public function setTipeAudio(string $tipeAudio): void {
        $this->tipeAudio = $tipeAudio;
    }

    public function getLokasiBaris(): string {
        return $this->lokasiBaris;
    }

    public function setLokasiBaris(string $lokasiBaris): void {
        $this->lokasiBaris = $lokasiBaris;
    }
}
