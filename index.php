<?php
/**
 * Aplikasi Sistem Manajemen Tiket Bioskop (OOP, Polimorfisme & CRUD)
 * 
 * index.php - File utama yang menggabungkan logika backend PHP (CRUD, Filter, Search, Factory Pattern)
 * dan antarmuka (UI) Dashboard Modern berbasis Tailwind CSS.
 */

require_once __DIR__ . '/koneksi/database.php';
require_once __DIR__ . '/Tiket.php';
require_once __DIR__ . '/TiketRegular.php';
require_once __DIR__ . '/TiketIMAX.php';
require_once __DIR__ . '/TiketVelvet.php';

$tiketObjects = [];
$errorMsg = null;
$successMsg = null;

// Catch filter & search parameters dari GET request
$current_studio = $_GET['studio'] ?? '';
$search_query   = $_GET['search'] ?? '';

// Inisialisasi koneksi database
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    $errorMsg = "Koneksi Database Gagal: " . $e->getMessage();
}

// ----------------------------------------------------
// PROSES INSERT (CREATE) - PEMESANAN TIKET BARU
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create' && isset($db)) {
    try {
        $nama_film         = trim($_POST['nama_film'] ?? '');
        $jadwal_tayang     = trim($_POST['jadwal_tayang'] ?? '');
        $jumlah_kursi      = (int)($_POST['jumlah_kursi'] ?? 0);
        $harga_dasar_tiket = (float)($_POST['harga_dasar_tiket'] ?? 0.0);
        $jenis_studio      = $_POST['jenis_studio'] ?? '';

        // Validasi input dasar
        if (empty($nama_film) || empty($jadwal_tayang) || $jumlah_kursi <= 0 || $harga_dasar_tiket <= 0 || empty($jenis_studio)) {
            throw new Exception("Seluruh field wajib diisi dengan benar.");
        }

        // Variabel penampung atribut spesifik
        $tipe_audio          = null;
        $lokasi_baris        = null;
        $kacamata_3d_id      = null;
        $efek_gerak_fitur    = null;
        $bantal_selimut_pack = null;
        $layanan_butler      = null;

        // Penyetelan atribut spesifik sesuai tipe studio (selain tipe yang dipilih, diset NULL)
        if ($jenis_studio === 'Regular') {
            $tipe_audio   = trim($_POST['tipe_audio'] ?? 'Dolby Atmos');
            $lokasi_baris = trim($_POST['lokasi_baris'] ?? 'Row C');
        } elseif ($jenis_studio === 'IMAX') {
            $kacamata_3d_id   = trim($_POST['kacamata_3d_id'] ?? 'IMAX-3D-001');
            $efek_gerak_fitur = trim($_POST['efek_gerak_fitur'] ?? 'Standard Motion');
        } elseif ($jenis_studio === 'Velvet') {
            $bantal_selimut_pack = trim($_POST['bantal_selimut_pack'] ?? 'Premium Bedding Set');
            $layanan_butler     = trim($_POST['layanan_butler'] ?? 'On-Demand Service');
        } else {
            throw new Exception("Jenis studio tidak valid.");
        }

        // Jalankan Query INSERT INTO menggunakan Prepared Statement
        $insertQuery = "INSERT INTO tabel_tiket (
                            nama_film, jadwal_tayang, jumlah_kursi, harga_dasar_tiket, jenis_studio, 
                            tipe_audio, lokasi_baris, kacamata_3d_id, efek_gerak_fitur, 
                            bantal_selimut_pack, layanan_butler
                        ) VALUES (
                            :nama_film, :jadwal_tayang, :jumlah_kursi, :harga_dasar_tiket, :jenis_studio, 
                            :tipe_audio, :lokasi_baris, :kacamata_3d_id, :efek_gerak_fitur, 
                            :bantal_selimut_pack, :layanan_butler
                        )";

        $stmt = $db->prepare($insertQuery);
        $stmt->execute([
            ':nama_film'           => $nama_film,
            ':jadwal_tayang'       => $jadwal_tayang,
            ':jumlah_kursi'        => $jumlah_kursi,
            ':harga_dasar_tiket'   => $harga_dasar_tiket,
            ':jenis_studio'        => $jenis_studio,
            ':tipe_audio'          => $tipe_audio,
            ':lokasi_baris'        => $lokasi_baris,
            ':kacamata_3d_id'      => $kacamata_3d_id,
            ':efek_gerak_fitur'    => $efek_gerak_fitur,
            ':bantal_selimut_pack' => $bantal_selimut_pack,
            ':layanan_butler'      => $layanan_butler
        ]);

        // Redirect menggunakan pattern POST-Redirect-GET
        $redirectUrl = 'index.php';
        $redirectParams = [];
        if (!empty($current_studio)) $redirectParams['studio'] = $current_studio;
        if (!empty($search_query)) $redirectParams['search'] = $search_query;
        if (!empty($redirectParams)) {
            $redirectUrl .= '?' . http_build_query($redirectParams);
        }
        
        header("Location: " . $redirectUrl);
        exit;
    } catch (Exception $e) {
        $errorMsg = "Pemesanan Gagal: " . $e->getMessage();
    }
}

// ----------------------------------------------------
// QUERY COUNTER UNTUK SIDEBAR (STATIS)
// ----------------------------------------------------
$sidebarCounts = ['Regular' => 0, 'IMAX' => 0, 'Velvet' => 0];
if (isset($db)) {
    try {
        $countQuery = "SELECT jenis_studio, COUNT(*) as total FROM tabel_tiket GROUP BY jenis_studio";
        $countStmt = $db->query($countQuery);
        while ($row = $countStmt->fetch()) {
            $sidebarCounts[$row['jenis_studio']] = (int)$row['total'];
        }
    } catch (Exception $e) {
        error_log("Sidebar counter error: " . $e->getMessage());
    }
}

// ----------------------------------------------------
// PROSES READ - BACA DATA DENGAN FILTER & SEARCH
// ----------------------------------------------------
$totalSeats = 0;
$totalRevenue = 0.0;
$counts = ['Regular' => 0, 'IMAX' => 0, 'Velvet' => 0];

if (isset($db)) {
    try {
        // Susun query dinamis menggunakan Prepared Statements
        $query = "SELECT * FROM tabel_tiket WHERE 1=1";
        $params = [];

        // Filter pencarian nama film
        if (!empty($search_query)) {
            $query .= " AND nama_film LIKE :search";
            $params[':search'] = '%' . $search_query . '%';
        }

        // Filter tipe studio
        if (!empty($current_studio) && in_array($current_studio, ['Regular', 'IMAX', 'Velvet'])) {
            $query .= " AND jenis_studio = :studio";
            $params[':studio'] = $current_studio;
        }

        $query .= " ORDER BY id_tiket DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);

        // Fetching data dan menggunakan Factory Pattern untuk instansiasi objek
        while ($row = $stmt->fetch()) {
            $tiketObj = null;
            switch ($row['jenis_studio']) {
                case 'Regular':
                    $tiketObj = new TiketRegular($row);
                    break;
                case 'IMAX':
                    $tiketObj = new TiketIMAX($row);
                    break;
                case 'Velvet':
                    $tiketObj = new TiketVelvet($row);
                    break;
            }

            if ($tiketObj) {
                $tiketObjects[] = $tiketObj;
                $totalSeats += $tiketObj->getJumlahKursi();
                $totalRevenue += $tiketObj->hitungTotalHarga();
                $counts[$row['jenis_studio']]++;
            }
        }
    } catch (Exception $e) {
        $errorMsg = "Gagal memuat data tiket: " . $e->getMessage();
    }
}

// Fungsi bantu untuk membuat URL filter dinamis agar search/filter tetap terjaga
function makeFilterUrl($studio, $search) {
    $params = [];
    if (!empty($studio)) $params['studio'] = $studio;
    if (!empty($search)) $params['search'] = $search;
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Ticket Dashboard - Achmal Maulana</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .font-display {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">

    <!-- Wrapper Layout Utama -->
    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- ==========================================
             SIDEBAR (SISI KIRI)
             ========================================== -->
        <aside class="w-full md:w-72 bg-white border-b md:border-b-0 md:border-r border-slate-200 flex-shrink-0 flex flex-col justify-between">
            <div>
                <!-- Header Sidebar / Logo -->
                <div class="p-6 border-b border-slate-200 flex items-center space-x-3">
                    <div class="bg-blue-600 text-white p-2.5 rounded-xl shadow-lg shadow-blue-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-extrabold font-display tracking-tight text-slate-900 leading-tight">CineTickets</h2>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Pra-UAS PBO Kel 3</span>
                    </div>
                </div>

                <!-- Menu Navigasi Tier Studio -->
                <nav class="p-6 space-y-2">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest block mb-4 px-3">Filter Tier Studio</span>
                    
                    <!-- Semua Studio -->
                    <a href="<?= makeFilterUrl('', $search_query); ?>" 
                       class="flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 group <?= empty($current_studio) ? 'bg-blue-50 text-blue-600 font-bold border-l-4 border-blue-600 pl-3' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' ?>">
                        <div class="flex items-center space-x-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                            </svg>
                            <span>Semua Studio</span>
                        </div>
                    </a>

                    <!-- Regular Studio -->
                    <a href="<?= makeFilterUrl('Regular', $search_query); ?>" 
                       class="flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 group <?= $current_studio === 'Regular' ? 'bg-blue-50 text-blue-600 font-bold border-l-4 border-blue-600 pl-3' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' ?>">
                        <div class="flex items-center space-x-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>Studio Regular</span>
                        </div>
                        <span class="text-xs bg-slate-100 text-slate-500 font-semibold px-2 py-0.5 rounded-full group-hover:bg-slate-200/80 transition-colors"><?= $sidebarCounts['Regular'] ?? 0; ?></span>
                    </a>

                    <!-- IMAX Studio -->
                    <a href="<?= makeFilterUrl('IMAX', $search_query); ?>" 
                       class="flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 group <?= $current_studio === 'IMAX' ? 'bg-blue-50 text-blue-600 font-bold border-l-4 border-blue-600 pl-3' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' ?>">
                        <div class="flex items-center space-x-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span>Studio IMAX</span>
                        </div>
                        <span class="text-xs bg-blue-100 text-blue-600 font-semibold px-2 py-0.5 rounded-full"><?= $sidebarCounts['IMAX'] ?? 0; ?></span>
                    </a>

                    <!-- Velvet Studio -->
                    <a href="<?= makeFilterUrl('Velvet', $search_query); ?>" 
                       class="flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 group <?= $current_studio === 'Velvet' ? 'bg-blue-50 text-blue-600 font-bold border-l-4 border-blue-600 pl-3' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' ?>">
                        <div class="flex items-center space-x-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            <span>Studio Velvet</span>
                        </div>
                        <span class="text-xs bg-purple-100 text-purple-600 font-semibold px-2 py-0.5 rounded-full"><?= $sidebarCounts['Velvet'] ?? 0; ?></span>
                    </a>
                </nav>
            </div>

            <!-- Footer Sidebar -->
            <div class="p-6 border-t border-slate-200 bg-slate-50/50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 font-bold font-display">
                        AM
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Mahasiswa</p>
                        <p class="text-sm font-bold text-slate-800">Achmal Maulana</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ==========================================
             KONTEN AREA UTAMA (KANAN)
             ========================================== -->
        <div class="flex-grow flex flex-col min-w-0">

            <!-- NAVBAR (SISI ATAS) -->
            <nav class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-2">
                    <h2 class="text-lg font-bold font-display text-slate-900">Cinema Ticket Dashboard</h2>
                    <span class="text-slate-300">|</span>
                    <span class="text-xs text-slate-500 font-semibold">Client POV</span>
                </div>

                <!-- Search Bar Fungsional (Form Method GET) -->
                <form action="" method="GET" class="relative w-full sm:w-80">
                    <!-- Pertahankan filter studio jika ada saat melakukan search -->
                    <?php if (!empty($current_studio)): ?>
                        <input type="hidden" name="studio" value="<?= htmlspecialchars($current_studio); ?>">
                    <?php endif; ?>
                    <input type="text" name="search" value="<?= htmlspecialchars($search_query); ?>" 
                           placeholder="Cari judul film..." 
                           class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200">
                    <div class="absolute left-3 top-2.5 text-slate-400 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <?php if (!empty($search_query)): ?>
                        <a href="<?= makeFilterUrl($current_studio, ''); ?>" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </form>
            </nav>

            <!-- MAIN CONTENT AREA -->
            <main class="flex-grow bg-slate-50 p-6">

                <!-- Alert Messages -->
                <?php if ($errorMsg): ?>
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl p-4 mb-6 shadow-sm flex items-start space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <span class="font-bold">Kesalahan Operasional:</span>
                            <p class="text-sm mt-0.5"><?= htmlspecialchars($errorMsg); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Header Content (Action Bar) -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
                    <div>
                        <h2 class="text-2xl font-extrabold font-display text-slate-900 leading-tight">
                            <?php 
                                if (empty($current_studio)) echo "Seluruh Daftar Pemesanan";
                                else echo "Pemesanan Studio " . htmlspecialchars($current_studio);
                            ?>
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">
                            <?php if (!empty($search_query)): ?>
                                Menampilkan hasil pencarian untuk "<span class="font-bold text-slate-700"><?= htmlspecialchars($search_query); ?></span>"
                            <?php else: ?>
                                Total pemesanan terdata di sistem ini.
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Tombol + Pesan Tiket Baru (Membuka Modal Form) -->
                    <button onclick="toggleModal(true)" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-3 rounded-xl shadow-lg shadow-blue-500/20 hover:shadow-blue-500/35 transition-all duration-200 flex items-center justify-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <span>Pesan Tiket Baru</span>
                    </button>
                </div>

                <!-- Grid Card Transaksi Pemesanan -->
                <?php if (!empty($tiketObjects)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($tiketObjects as $tiket): ?>
                            <?php 
                                $studioType = '';
                                $badgeStyle = '';
                                if ($tiket instanceof TiketRegular) {
                                    $studioType = 'Regular';
                                    $badgeStyle = 'bg-slate-100 text-slate-700 border-slate-200';
                                } elseif ($tiket instanceof TiketIMAX) {
                                    $studioType = 'IMAX';
                                    $badgeStyle = 'bg-blue-50 text-blue-600 border-blue-200';
                                } elseif ($tiket instanceof TiketVelvet) {
                                    $studioType = 'Velvet';
                                    $badgeStyle = 'bg-purple-50 text-purple-600 border-purple-200';
                                }
                            ?>
                            <!-- Card component -->
                            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between overflow-hidden group">
                                <!-- Top Accent Border -->
                                <div class="h-2 w-full <?= ($studioType === 'Velvet') ? 'bg-purple-500' : (($studioType === 'IMAX') ? 'bg-blue-500' : 'bg-slate-400'); ?>"></div>

                                <div class="p-6 flex-grow">
                                    <!-- Badge & Code -->
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-xs font-mono text-slate-400 font-semibold">ID-<?= str_pad($tiket->getIdTiket() ?? 0, 3, '0', STR_PAD_LEFT); ?></span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $badgeStyle; ?> shadow-sm">
                                            <?= $studioType; ?>
                                        </span>
                                    </div>

                                    <!-- Judul Film -->
                                    <h4 class="text-lg font-bold text-slate-800 font-display group-hover:text-blue-600 transition-colors line-clamp-2 min-h-[3.5rem] leading-snug">
                                        <?= htmlspecialchars($tiket->getNamaFilm()); ?>
                                    </h4>

                                    <!-- Rincian Pemesanan -->
                                    <div class="mt-4 space-y-2 border-t border-b border-slate-100 py-4 text-xs font-medium text-slate-600">
                                        <div class="flex justify-between">
                                            <span>Jadwal Tayang:</span>
                                            <span class="text-slate-800 font-bold"><?= htmlspecialchars($tiket->getJadwalTayang()); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Jumlah Kursi:</span>
                                            <span class="text-slate-800 font-bold"><?= $tiket->getJumlahKursi(); ?> Kursi</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Tarif Dasar:</span>
                                            <span class="text-slate-800 font-bold">Rp <?= number_format($tiket->getHargaDasarTiket(), 0, ',', '.'); ?></span>
                                        </div>
                                    </div>

                                    <!-- Polimorfisme: tampilkanInfoFasilitas() -->
                                    <div class="mt-4 bg-slate-50 border border-slate-100 rounded-xl p-3 text-xs leading-relaxed text-slate-500 shadow-inner">
                                        <span class="font-extrabold text-slate-700 block mb-1 uppercase tracking-wide text-[10px]">Fasilitas Tambahan:</span>
                                        <?= htmlspecialchars($tiket->tampilkanInfoFasilitas()); ?>
                                    </div>
                                </div>

                                <!-- Footer Card: Total Harga Disorot dengan warna biru muda -->
                                <div class="bg-blue-50/30 px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pembayaran:</span>
                                    <span class="text-lg font-extrabold text-blue-600 font-display">
                                        Rp <?= number_format($tiket->hitungTotalHarga(), 0, ',', '.'); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="bg-white rounded-3xl p-16 text-center border border-slate-200 shadow-sm max-w-lg mx-auto mt-12">
                        <div class="bg-blue-50 text-blue-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 border border-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold font-display text-slate-900">Pemesanan Tidak Ditemukan</h3>
                        <p class="text-sm text-slate-500 mt-2">
                            Tidak ditemukan kecocokan data pesanan untuk kriteria pencarian dan filter Anda saat ini. Coba ubah kata kunci atau bersihkan filter.
                        </p>
                        <div class="mt-6">
                            <a href="index.php" class="inline-flex items-center text-sm font-bold text-blue-600 hover:text-blue-700 transition-colors">
                                Reset Pencarian & Filter →
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

            </main>

            <!-- FOOTER -->
            <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400 font-medium">
                <p>© 2026 CineTickets Project. All rights reserved.</p>
                <p class="mt-1">Pra-Simulasi UAS PBO Kelas TRPL 1B - Achmal Maulana</p>
            </footer>
        </div>
    </div>

    <!-- ==========================================
         MODAL FORM PEMESANAN BARU (CREATE)
         ========================================== -->
    <div id="bookingModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Overlay -->
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="toggleModal(false)"></div>

            <!-- Trick to center modal content -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Content Card -->
            <div class="inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-200">
                
                <!-- Header Modal -->
                <div class="bg-blue-600 px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        <h3 class="text-lg font-bold font-display" id="modal-title">Formulir Pesan Tiket Baru</h3>
                    </div>
                    <button onclick="toggleModal(false)" class="text-white/80 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form Pemesanan -->
                <form action="" method="POST" class="p-6 space-y-4">
                    <input type="hidden" name="action" value="create">

                    <!-- Nama Film -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Judul Film</label>
                        <input type="text" name="nama_film" required 
                               placeholder="Contoh: Interstellar" 
                               class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Grid: Jadwal, Jumlah Kursi, Harga Dasar -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jadwal Tayang</label>
                            <input type="datetime-local" name="jadwal_tayang" required 
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jumlah Kursi</label>
                            <input type="number" name="jumlah_kursi" min="1" required 
                                   placeholder="Min: 1" 
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Harga Dasar per Kursi (Rp)</label>
                            <input type="number" name="harga_dasar_tiket" min="0" required 
                                   placeholder="Contoh: 40000" 
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis Studio / Tier</label>
                            <select name="jenis_studio" id="studioSelector" onchange="handleStudioChange()" required 
                                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="Regular">Regular</option>
                                <option value="IMAX">IMAX</option>
                                <option value="Velvet">Velvet</option>
                            </select>
                        </div>
                    </div>

                    <!-- ====================================================
                         DYNAMIC FIELDS (DIUBAH BERDASARKAN JENIS STUDIO)
                         ==================================================== -->
                    <div class="border-t border-slate-200 pt-4 mt-2">
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-widest block mb-3">Spesifikasi Fasilitas Studio</span>
                        
                        <!-- Bidang Khusus Regular -->
                        <div id="fieldsRegular" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tipe Audio</label>
                                <select name="tipe_audio" 
                                        class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="Dolby Atmos">Dolby Atmos</option>
                                    <option value="Dolby 7.1">Dolby 7.1</option>
                                    <option value="DTS:X">DTS:X</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Lokasi Baris Tempat Duduk</label>
                                <input type="text" name="lokasi_baris" placeholder="Contoh: Row C" value="Row C"
                                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Bidang Khusus IMAX -->
                        <div id="fieldsIMAX" class="grid grid-cols-1 sm:grid-cols-2 gap-4 hidden">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">ID Kacamata 3D</label>
                                <input type="text" name="kacamata_3d_id" placeholder="Contoh: IMAX-3D-099" value="IMAX-3D-088"
                                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Fitur Efek Gerak</label>
                                <select name="efek_gerak_fitur" 
                                        class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="D-BOX Motion">D-BOX Motion</option>
                                    <option value="4DX Active">4DX Active</option>
                                    <option value="Standard Motion">Standard Motion</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bidang Khusus Velvet -->
                        <div id="fieldsVelvet" class="grid grid-cols-1 sm:grid-cols-2 gap-4 hidden">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Paket Bantal & Selimut</label>
                                <input type="text" name="bantal_selimut_pack" placeholder="Contoh: Plush Velvet Blanket Set" value="Premium Bedding Set"
                                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Layanan Asisten Pribadi (Butler)</label>
                                <select name="layanan_butler" 
                                        class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="Personal Butler Service">Personal Butler Service</option>
                                    <option value="VVIP Butler Service">VVIP Butler Service</option>
                                    <option value="On-Demand Service">On-Demand Service</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action Modal -->
                    <div class="border-t border-slate-200 pt-6 mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="toggleModal(false)" 
                                class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-5 py-2.5 rounded-xl text-sm transition-colors duration-150">
                            Batal
                        </button>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2.5 rounded-xl text-sm shadow-md shadow-blue-500/10 transition-colors duration-150">
                            Konfirmasi Pemesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==========================================
         JAVASCRIPT LOGIC
         ========================================== -->
    <script>
        // Membuka & Menutup Modal
        function toggleModal(show) {
            const modal = document.getElementById('bookingModal');
            if (show) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            } else {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        // Penanganan Input Dinamis berdasarkan Pilihan Jenis Studio
        function handleStudioChange() {
            const selector = document.getElementById('studioSelector');
            const selectedType = selector.value;

            // Dapatkan DOM containers
            const fieldsRegular = document.getElementById('fieldsRegular');
            const fieldsIMAX = document.getElementById('fieldsIMAX');
            const fieldsVelvet = document.getElementById('fieldsVelvet');

            // Sembunyikan semua bidang khusus terlebih dahulu
            fieldsRegular.classList.add('hidden');
            fieldsIMAX.classList.add('hidden');
            fieldsVelvet.classList.add('hidden');

            // Tampilkan bidang yang sesuai pilihan
            if (selectedType === 'Regular') {
                fieldsRegular.classList.remove('hidden');
            } else if (selectedType === 'IMAX') {
                fieldsIMAX.classList.remove('hidden');
            } else if (selectedType === 'Velvet') {
                fieldsVelvet.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
