<?php
require_once __DIR__ . '/Tiket.php';

/**
 * Class TiketIMAX
 * 
 * Subclass konkrit dari Tiket untuk tipe studio IMAX.
 */
class TiketIMAX extends Tiket {
    private string $kacamata3dId;
    private string $efekGerakFitur;

    /**
     * Constructor untuk inisialisasi properti TiketIMAX.
     * 
     * @param array $data Array asosiatif berisi data tiket IMAX
     */
    public function __construct(array $data) {
        // Memanggil constructor parent (Tiket)
        parent::__construct($data);

        // Inisialisasi properti spesifik TiketIMAX
        $this->kacamata3dId   = $data['kacamata3dId'] ?? $data['kacamata_3d_id'] ?? 'IMAX-3D-001';
        $this->efekGerakFitur = $data['efekGerakFitur'] ?? $data['efek_gerak_fitur'] ?? 'Standard Motion';
    }

    /**
     * Menghitung total harga tiket IMAX.
     * Formula: (jumlah_kursi * hargaDasarTiket) + Surcharge Fix Rp35.000
     * 
     * @return float
     */
    public function hitungTotalHarga(): float {
        $totalHargaDasar = $this->getJumlahKursi() * $this->getHargaDasarTiket();
        $surcharge = 35000.00;
        return $totalHargaDasar + $surcharge;
    }

    /**
     * Menampilkan informasi fasilitas tambahan Tiket IMAX.
     * 
     * @return string
     */
    public function tampilkanInfoFasilitas(): string {
        return "Fasilitas IMAX: Kacamata 3D Eksklusif dengan ID " . $this->kacamata3dId . " dan simulasi efek gerak kursi (" . $this->efekGerakFitur . ") yang imersif.";
    }

    // ==========================================
    // GETTERS & SETTERS
    // ==========================================

    public function getKacamata3dId(): string {
        return $this->kacamata3dId;
    }

    public function setKacamata3dId(string $kacamata3dId): void {
        $this->kacamata3dId = $kacamata3dId;
    }

    public function getEfekGerakFitur(): string {
        return $this->efekGerakFitur;
    }

    public function setEfekGerakFitur(string $efekGerakFitur): void {
        $this->efekGerakFitur = $efekGerakFitur;
    }
}
