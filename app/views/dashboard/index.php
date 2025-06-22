<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "Dashboard";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';
$versiSistem = $pengaturan['versi'] ?? '-';
$loginJudul = $pengaturan['login_judul'] ?? 'Kontrol Penuh. Keamanan Maksimal.';
$loginDeskripsi = $pengaturan['login_deskripsi'] ?? 'Panel admin ini dirancang untuk performa dan keamanan dalam mengelola sistem SPK berbasis metode ELECTRE.';
?>

<!DOCTYPE html>
<html lang="id" id="htmlRoot">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($pengaturan['nama_aplikasi'] ?? $pageTitle) ?></title>
  <link rel="icon" href="../../../assets/logo/<?= $logo ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
    if (localStorage.getItem("theme") === "dark") {
      document.documentElement.classList.add("dark");
    }
  </script>
  <style>
    .fade-in { animation: fadeIn 1s ease-out forwards; opacity: 0; transform: translateY(10px); }
    @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
    .glow { animation: glowText 1.5s ease-in-out infinite; }
    @keyframes glowText { 0%,100%{opacity:1} 50%{opacity:.4} }
  </style>
</head>
<body class="min-h-screen bg-white dark:bg-gray-900 text-gray-800 dark:text-white flex transition">

  <!-- Splash Screen -->
  <div id="splash" class="fixed inset-0 z-50 flex items-center justify-center bg-white dark:bg-gray-900 transition duration-700">
    <div class="text-center fade-in">
      <img src="../../../assets/logo/<?= $logo ?>" class="h-20 w-20 animate-spin mx-auto mb-4" alt="Loading Logo" />
      <p class="text-sm text-gray-600 dark:text-gray-300 glow">Memuat Dashboard...</p>
    </div>
  </div>

  <!-- Sidebar -->
  <?php include '../layouts/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="ml-64 flex-1 flex flex-col">
    <?php include '../layouts/topbar.php'; ?>

    <main class="flex-1 px-6 py-6 bg-gray-50 dark:bg-gray-900 fade-in">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div class="flex items-center gap-4">
          <img src="../../../assets/logo/<?= $logo ?>" class="h-12 w-12 rounded shadow" alt="Logo">
          <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($pengaturan['nama_aplikasi']) ?></h1>
            <p class="text-sm text-gray-500 dark:text-gray-300"><?= htmlspecialchars($pengaturan['deskripsi']) ?></p>
          </div>
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mt-4 sm:mt-0">
          Versi Sistem: <span class="font-medium"><?= htmlspecialchars($versiSistem) ?></span>
        </div>
      </div>

      <!-- Login Banner Style (Optional Branding) -->
      <div class="bg-indigo-600 dark:bg-indigo-700 text-white px-6 py-4 rounded-lg mb-6 shadow flex flex-col sm:flex-row sm:justify-between sm:items-center">
        <div>
          <h2 class="text-lg font-semibold"><?= htmlspecialchars($loginJudul) ?></h2>
          <p class="text-sm"><?= htmlspecialchars($loginDeskripsi) ?></p>
        </div>
        <div class="mt-3 sm:mt-0 text-xs text-indigo-100 sm:text-right">Informasi login dapat diubah dari menu Pengaturan.</div>
      </div>

      <!-- Statistik -->
      <?php
      $jumlahAlternatif = $conn->query("SELECT COUNT(*) FROM alternatif")->fetchColumn();
      $jumlahKriteria   = $conn->query("SELECT COUNT(*) FROM kriteria")->fetchColumn();
      $jumlahNilai      = $conn->query("SELECT COUNT(*) FROM nilai")->fetchColumn();
      ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 p-5 rounded shadow hover:shadow-md transition group">
          <div class="flex items-center justify-between">
            <h3 class="text-sm text-gray-500 dark:text-gray-400">Jumlah Alternatif</h3>
            <i data-lucide="layers" class="w-5 h-5 text-indigo-600 group-hover:scale-110 transition"></i>
          </div>
          <p class="text-3xl font-bold text-indigo-600 mt-2"><?= $jumlahAlternatif ?></p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded shadow hover:shadow-md transition group">
          <div class="flex items-center justify-between">
            <h3 class="text-sm text-gray-500 dark:text-gray-400">Jumlah Kriteria</h3>
            <i data-lucide="sliders" class="w-5 h-5 text-indigo-600 group-hover:scale-110 transition"></i>
          </div>
          <p class="text-3xl font-bold text-indigo-600 mt-2"><?= $jumlahKriteria ?></p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded shadow hover:shadow-md transition group">
          <div class="flex items-center justify-between">
            <h3 class="text-sm text-gray-500 dark:text-gray-400">Data Nilai</h3>
            <i data-lucide="clipboard-list" class="w-5 h-5 text-indigo-600 group-hover:scale-110 transition"></i>
          </div>
          <p class="text-3xl font-bold text-indigo-600 mt-2"><?= $jumlahNilai ?></p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded shadow hover:shadow-md transition group">
          <div class="flex items-center justify-between">
            <h3 class="text-sm text-gray-500 dark:text-gray-400">Versi Sistem</h3>
            <i data-lucide="info" class="w-5 h-5 text-indigo-600 group-hover:scale-110 transition"></i>
          </div>
          <p class="text-3xl font-bold text-indigo-600 mt-2"><?= htmlspecialchars($versiSistem) ?></p>
        </div>
      </div>
    </main>

    <?php include '../layouts/footer.php'; ?>
  </div>

  <script>
    // Splash screen
    window.addEventListener("DOMContentLoaded", () => {
      setTimeout(() => {
        document.getElementById("splash").classList.add("opacity-0", "pointer-events-none");
      }, 1000);
    });

    lucide.createIcons();
  </script>
</body>
</html>
