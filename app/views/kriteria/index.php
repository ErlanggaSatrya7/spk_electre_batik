<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "Kriteria";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';
?>

<!DOCTYPE html>
<html lang="id" id="htmlRoot">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?> | <?= htmlspecialchars($pengaturan['nama_aplikasi'] ?? 'SPK') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    body { font-family: 'Inter', sans-serif; }
    .fade-in { animation: fadeIn 0.5s ease-out forwards; opacity: 0; transform: translateY(10px); }
    @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex transition">

<?php include '../layouts/splash.php'; ?>
<?php include '../layouts/sidebar.php'; ?>
<div class="ml-64 flex-1 flex flex-col">
  <?php include '../layouts/topbar.php'; ?>

  <main class="flex-1 px-6 py-6 fade-in">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
      <div>
        <h1 class="text-2xl font-bold"><?= $pageTitle ?></h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola kriteria penilaian untuk perhitungan ELECTRE.</p>
      </div>
      <a href="form.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded text-sm shadow">+ Tambah</a>
    </div>

    <!-- Filter -->
    <form id="filterForm" class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-3">
      <div class="flex gap-3 w-full sm:w-auto">
        <input type="text" name="search" id="search" placeholder="Cari nama kriteria..." class="px-3 py-2 border rounded w-full sm:w-64 dark:bg-gray-800 dark:border-gray-700">
        <select name="tipe" id="tipe" class="px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
          <option value="">Semua Tipe</option>
          <option value="benefit">Benefit</option>
          <option value="cost">Cost</option>
        </select>
        <select name="sort" id="sort" class="px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
          <option value="baru">Terbaru</option>
          <option value="lama">Terlama</option>
        </select>
      </div>
      <div class="flex gap-2">
        <a href="export.php" target="_blank" class="text-sm bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">📄 PDF</a>
        <a href="export_excel.php" target="_blank" class="text-sm bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">📊 Excel</a>
      </div>
    </form>

    <!-- Tabel AJAX -->
    <div id="dataContainer" class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow"></div>
  </main>

  <?php include '../layouts/footer.php'; ?>
</div>

<script>
  lucide.createIcons();

  function loadKriteria(page = 1) {
    const search = document.getElementById('search').value;
    const tipe = document.getElementById('tipe').value;
    const sort = document.getElementById('sort').value;
    const params = new URLSearchParams({ search, tipe, sort, page });

    fetch(`kriteria-ajax.php?${params.toString()}`)
      .then(res => res.text())
      .then(html => {
        document.getElementById('dataContainer').innerHTML = html;
        lucide.createIcons();
      });
  }

  // Trigger untuk pagination dari AJAX
  function loadKriteriaPage(page) {
    loadKriteria(page);
  }

  document.addEventListener('DOMContentLoaded', () => {
    loadKriteria();

    document.getElementById('search').addEventListener('input', () => loadKriteria());
    document.getElementById('tipe').addEventListener('change', () => loadKriteria());
    document.getElementById('sort').addEventListener('change', () => loadKriteria());
  });
</script>

</body>
</html>
