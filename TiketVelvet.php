<?php
require_once __DIR__ . '/Tiket.php';

/**
 * Class TiketVelvet
 * 
 * Subclass konkrit dari Tiket untuk tipe studio Velvet (Luxury Premium).
 */
class TiketVelvet extends Tiket {
    private string $bantalSelimutPack;
    private string $layananButler;

    /**
     * Constructor untuk inisialisasi properti TiketVelvet.
     * 
     * @param array $data Array asosiatif berisi data tiket Velvet
     */
    public function __construct(array $data) {
        // Memanggil constructor parent (Tiket)
        parent::__construct($data);

        // Inisialisasi properti spesifik TiketVelvet
        $this->bantalSelimutPack = $data['bantalSelimutPack'] ?? $data['bantal_selimut_pack'] ?? 'Premium Bedding Set';
        $this->layananButler     = $data['layananButler'] ?? $data['layanan_butler'] ?? 'On-Demand Service';
    }

    /**
     * Menghitung total harga tiket Velvet.
     * Formula: (jumlah_kursi * hargaDasarTiket) * 1.50 (Surcharge 50% atau dikali 1.50)
     * 
     * @return float
     */
    public function hitungTotalHarga(): float {
        $totalHargaDasar = $this->getJumlahKursi() * $this->getHargaDasarTiket();
        return $totalHargaDasar * 1.50;
    }

    /**
     * Menampilkan informasi fasilitas tambahan Tiket Velvet.
     * 
     * @return string
     */
    public function tampilkanInfoFasilitas(): string {
        return "Fasilitas Velvet: Fasilitas kasur premium lengkap dengan paket bantal-selimut (" . $this->bantalSelimutPack . ") serta pelayanan asisten pribadi (" . $this->layananButler . ").";
    }

    // ==========================================
    // GETTERS & SETTERS
    // ==========================================

    public function getBantalSelimutPack(): string {
        return $this->bantalSelimutPack;
    }

    public function setBantalSelimutPack(string $bantalSelimutPack): void {
        $this->bantalSelimutPack = $bantalSelimutPack;
    }

    public function getLayananButler(): string {
        return $this->layananButler;
    }

    public function setLayananButler(string $layananButler): void {
        $this->layananButler = $layananButler;
    }
}
