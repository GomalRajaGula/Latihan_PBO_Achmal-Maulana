<?php
/**
 * Aplikasi Sistem Manajemen Tiket Bioskop (OOP & Polimorfisme)
 * 
 * index.php - File utama yang menggabungkan logika backend PHP dan UI frontend modern.
 * Menggunakan Factory Pattern untuk instansiasi objek dan Polimorfisme untuk memanggil metode subclass.
 */

require_once __DIR__ . '/koneksi/database.php';
require_once __DIR__ . '/Tiket.php';
require_once __DIR__ . '/TiketRegular.php';
require_once __DIR__ . '/TiketIMAX.php';
require_once __DIR__ . '/TiketVelvet.php';

$tiketObjects = [];
$errorMsg = null;

// Statistik Summary
$totalSeats = 0;
$totalRevenue = 0.0;
$counts = ['Regular' => 0, 'IMAX' => 0, 'Velvet' => 0];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        // Query SELECT sesuai spesifikasi
        $query = "SELECT * FROM tabel_tiket ORDER BY id_tiket DESC";
        $stmt = $db->query($query);
        
        while ($row = $stmt->fetch()) {
            $tiketObj = null;
            // Factory Pattern Sederhana berdasarkan jenis_studio
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
                // Akumulasi data statistik untuk Dashboard
                $totalSeats += $tiketObj->getJumlahKursi();
                $totalRevenue += $tiketObj->hitungTotalHarga();
                $counts[$row['jenis_studio']]++;
            }
        }
    } else {
        $errorMsg = "Koneksi database gagal diinisialisasi.";
    }
} catch (Exception $e) {
    $errorMsg = "Terjadi Kesalahan: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tiket Bioskop - Achmal Maulana</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .font-display {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased pb-16">

    <!-- Navbar / Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50 backdrop-blur-md bg-white/95">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-600 text-white p-2 rounded-lg shadow-md shadow-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold font-display tracking-wide text-slate-900 leading-tight">CineTickets</h1>
                    <span class="text-xs text-slate-500 font-medium">PBO & Polimorfisme</span>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-1.5 animate-pulse"></span>
                    TRPL 1B - Achmal Maulana
                </span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">

        <!-- Error Alert Banner -->
        <?php if ($errorMsg): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl p-4 mb-8 flex items-start space-x-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h3 class="font-bold text-red-900">Koneksi Database Bermasalah</h3>
                    <p class="text-sm mt-1"><?= htmlspecialchars($errorMsg); ?></p>
                    <p class="text-xs mt-2 text-red-600 font-medium">Tips: Pastikan server MySQL aktif dan skrip <code class="bg-red-100 px-1.5 py-0.5 rounded font-mono">database_latihan.sql</code> telah di-import.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Welcome Banner & Summary Statistics (Modern Light Blue Theme) -->
        <div class="bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700 rounded-3xl text-white shadow-xl shadow-blue-900/10 mb-8 overflow-hidden relative">
            <!-- Background Decorative Patterns -->
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-blue-500/30 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="px-6 py-8 md:p-10 relative z-10">
                <div class="md:flex md:items-center md:justify-between mb-8">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-extrabold font-display leading-tight">Dashboard Pemesanan Tiket</h2>
                        <p class="text-blue-100 text-sm mt-1 max-w-xl">
                            Visualisasi data dari database menggunakan paradigma pemrograman berorientasi objek (PBO) dengan implementasi Factory Pattern dan Polimorfisme.
                        </p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-white/5 backdrop-blur-md p-4 rounded-2xl border border-white/10">
                    <div class="p-3">
                        <p class="text-xs text-blue-200 font-semibold uppercase tracking-wider">Total Pesanan</p>
                        <h3 class="text-2xl font-bold font-display mt-0.5"><?= count($tiketObjects); ?> Tiket</h3>
                    </div>
                    <div class="p-3 border-l border-white/10">
                        <p class="text-xs text-blue-200 font-semibold uppercase tracking-wider">Total Kursi</p>
                        <h3 class="text-2xl font-bold font-display mt-0.5"><?= $totalSeats; ?> Kursi</h3>
                    </div>
                    <div class="p-3 border-l border-white/10 col-span-2 md:col-span-2">
                        <p class="text-xs text-blue-200 font-semibold uppercase tracking-wider">Total Pendapatan</p>
                        <h3 class="text-2xl font-bold font-display mt-0.5 text-blue-300">Rp <?= number_format($totalRevenue, 2, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Studio Filter Badges -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold font-display text-slate-900">Daftar Tiket Terdaftar</h3>
            <div class="flex space-x-2 text-xs font-semibold">
                <span class="px-2.5 py-1 bg-slate-200/60 text-slate-700 rounded-full border border-slate-300/50">Regular: <?= $counts['Regular']; ?></span>
                <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full border border-blue-200">IMAX: <?= $counts['IMAX']; ?></span>
                <span class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded-full border border-purple-200">Velvet: <?= $counts['Velvet']; ?></span>
            </div>
        </div>

        <!-- CSS Grid Layout (3 Columns on Large Screen) -->
        <?php if (!empty($tiketObjects)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($tiketObjects as $tiket): ?>
                    <?php 
                        // Mendapatkan jenis kelas objek untuk styling badge
                        $studioType = '';
                        $badgeStyle = '';
                        if ($tiket instanceof TiketRegular) {
                            $studioType = 'Regular';
                            $badgeStyle = 'bg-slate-100 text-slate-700 border-slate-200';
                        } elseif ($tiket instanceof TiketIMAX) {
                            $studioType = 'IMAX';
                            $badgeStyle = 'bg-blue-50 text-blue-700 border-blue-100';
                        } elseif ($tiket instanceof TiketVelvet) {
                            $studioType = 'Velvet';
                            $badgeStyle = 'bg-purple-50 text-purple-700 border-purple-100';
                        }
                    ?>
                    <!-- Card Komponen (bg-white, rounded-xl, shadow-sm) -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between overflow-hidden group">
                        
                        <!-- Top Decorator Line -->
                        <div class="h-1.5 w-full <?= ($studioType === 'Velvet') ? 'bg-purple-500' : (($studioType === 'IMAX') ? 'bg-blue-500' : 'bg-slate-400'); ?>"></div>
                        
                        <div class="p-6 flex-grow">
                            <!-- Badge & ID -->
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs font-mono text-slate-400">#TIKET-<?= str_pad($tiket->getIdTiket() ?? 0, 3, '0', STR_PAD_LEFT); ?></span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $badgeStyle; ?>">
                                    <?= $studioType; ?>
                                </span>
                            </div>

                            <!-- Movie Title -->
                            <h4 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors duration-200 font-display line-clamp-2 min-h-[3.5rem] leading-snug">
                                <?= htmlspecialchars($tiket->getNamaFilm()); ?>
                            </h4>

                            <!-- Movie details -->
                            <div class="mt-4 space-y-2.5 border-t border-b border-slate-100 py-4 text-sm">
                                <div class="flex items-center text-slate-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span><?= htmlspecialchars($tiket->getJadwalTayang()); ?></span>
                                </div>
                                <div class="flex items-center text-slate-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 025.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>Jumlah Kursi: <strong class="text-slate-800 font-semibold"><?= $tiket->getJumlahKursi(); ?> Kursi</strong></span>
                                </div>
                                <div class="flex items-center text-slate-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 8H12" />
                                    </svg>
                                    <span>Harga Dasar: <strong class="text-slate-800 font-semibold">Rp <?= number_format($tiket->getHargaDasarTiket(), 0, ',', '.'); ?></strong></span>
                                </div>
                            </div>

                            <!-- Polimorfisme: Memanggil $tiket->tampilkanInfoFasilitas() -->
                            <div class="mt-4 bg-slate-50 border border-slate-100 rounded-lg p-3 text-xs leading-relaxed text-slate-600">
                                <span class="font-bold text-slate-700 block mb-0.5">Fasilitas Tambahan:</span>
                                <?= htmlspecialchars($tiket->tampilkanInfoFasilitas()); ?>
                            </div>
                        </div>

                        <!-- Footer Card: Total Harga disorot dengan warna biru muda -->
                        <div class="bg-blue-50/50 px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Bayar:</span>
                            <span class="text-lg font-extrabold text-blue-600">
                                Rp <?= number_format($tiket->hitungTotalHarga(), 0, ',', '.'); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <?php if (!$errorMsg): ?>
                <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm max-w-md mx-auto mt-12">
                    <div class="bg-blue-50 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border border-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold font-display text-slate-900">Belum Ada Tiket Terdaftar</h3>
                    <p class="text-sm text-slate-500 mt-2">Database berhasil terhubung tetapi tidak ada data pesanan tiket di tabel <code class="bg-slate-100 text-rose-600 px-1 py-0.5 rounded font-mono font-medium">tabel_tiket</code>.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="mt-16 text-center text-xs text-slate-400 font-medium">
        <p>© 2026 CineTickets Project. All rights reserved.</p>
        <p class="mt-1">Dibuat dengan ❤️ untuk PBO Kelas TRPL 1B - Achmal Maulana</p>
    </footer>

</body>
</html>
