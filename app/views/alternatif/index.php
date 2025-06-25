<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "Alternatif";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';
$kategoriList = $conn->query("SELECT DISTINCT kategori FROM alternatif WHERE kategori IS NOT NULL AND kategori != ''")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($pengaturan['nama_aplikasi'] ?? 'SPK') ?> | <?= $pageTitle ?></title>
  <link rel="icon" href="../../../assets/logo/<?= $logo ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    tailwind.config = {
      darkMode: 'class'
    };
    if (localStorage.getItem("theme") === "dark") {
      document.documentElement.classList.add("dark");
    }
  </script>
  <style>
    .fade-in {
      animation: fadeIn .5s ease-out forwards;
      opacity: 0;
      transform: translateY(10px);
    }

    @keyframes fadeIn {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
</head>

<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex transition">

  <?php include '../layouts/splash.php'; ?>
  <?php include '../layouts/darkmode-toggle.php'; ?>
  <?php include '../layouts/sidebar.php'; ?>

  <div class="ml-64 flex-1 flex flex-col">
    <?php include '../layouts/topbar.php'; ?>

    <main class="flex-1 px-6 py-6 bg-gray-50 dark:bg-gray-900 fade-in">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= $pageTitle ?></h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Data alternatif batik untuk perhitungan ELECTRE.</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
          <a href="#" onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm shadow">📂 Import</a>
          <a href="form.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm shadow">+ Tambah</a>
        </div>
      </div>

      <?php if (isset($_GET['import_success'])): ?>
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 text-sm shadow">
          ✅ Berhasil mengimpor <?= htmlspecialchars($_GET['import_success']) ?> data alternatif.
        </div>
      <?php endif; ?>

      <form id="filterForm" class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-3">
        <div class="flex gap-3 w-full sm:w-auto">
          <input type="text" name="search" id="search" placeholder="Cari nama batik..." class="px-3 py-2 border rounded w-full sm:w-64 dark:bg-gray-800 dark:border-gray-700">
          <select name="kategori" id="kategori" class="px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategoriList as $kat): ?>
              <option value="<?= htmlspecialchars($kat) ?>"><?= htmlspecialchars($kat) ?></option>
            <?php endforeach ?>
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

      <div id="dataContainer" class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow"></div>
    </main>

    <?php include '../layouts/footer.php'; ?>
  </div>

  <!-- Modal Galeri -->
  <div id="galleryModal" class="fixed inset-0 z-50 bg-black bg-opacity-70 hidden flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-xl w-[90%] relative text-center p-6">
      <button onclick="closeGalleryModal()" class="absolute top-3 right-4 text-gray-500 hover:text-red-600">✖</button>
      <h2 class="text-lg font-semibold mb-3 text-indigo-600 dark:text-indigo-300">Galeri Gambar</h2>
      <div class="flex justify-center items-center min-h-[300px]">
        <img id="galleryImage" src="" class="max-h-[500px] w-auto object-contain mx-auto rounded border shadow" alt="preview" />
      </div>
      <div class="flex justify-between mt-4">
        <button onclick="prevImage()" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">← Sebelumnya</button>
        <button onclick="nextImage()" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">Berikutnya →</button>
      </div>
    </div>
  </div>

  <!-- Modal Import -->
  <div id="importModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white dark:bg-gray-800 p-6 rounded shadow max-w-md w-full relative">
      <button onclick="document.getElementById('importModal').classList.add('hidden')" class="absolute top-3 right-4 text-gray-500 hover:text-red-600">✖</button>
      <h2 class="text-lg font-semibold text-indigo-600 dark:text-indigo-300 mb-3">Import Alternatif dari Excel/CSV</h2>
      <form action="../../../app/controllers/ImportAlternatifController.php" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="file" name="file" accept=".xls,.xlsx,.csv" required class="w-full text-sm file:border file:rounded file:px-4 file:py-2 file:bg-indigo-600 file:text-white file:cursor-pointer dark:bg-gray-900">
        <div class="flex justify-end">
          <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm">Import Sekarang</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    lucide.createIcons();
    let galleryFiles = [], currentIndex = 0;

    function openGalleryModal(files) {
      galleryFiles = files;
      currentIndex = 0;
      updateGallery();
      document.getElementById('galleryModal').classList.remove('hidden');
    }

    function updateGallery() {
      document.getElementById('galleryImage').src = '../../../assets/images/' + galleryFiles[currentIndex];
    }

    function nextImage() {
      currentIndex = (currentIndex + 1) % galleryFiles.length;
      updateGallery();
    }

    function prevImage() {
      currentIndex = (currentIndex - 1 + galleryFiles.length) % galleryFiles.length;
      updateGallery();
    }

    function closeGalleryModal() {
      document.getElementById('galleryModal').classList.add('hidden');
    }

    function loadData(page = 1) {
      const search = document.getElementById('search').value;
      const kategori = document.getElementById('kategori').value;
      const sort = document.getElementById('sort').value;
      const params = new URLSearchParams({ search, kategori, sort, page });

      fetch('alternatif-ajax.php?' + params.toString())
        .then(res => res.text())
        .then(html => {
          document.getElementById('dataContainer').innerHTML = html;
          lucide.createIcons();
        });
    }

    function loadPage(p) {
      loadData(p);
    }

    document.addEventListener('DOMContentLoaded', () => {
      loadData();
      document.getElementById('search').addEventListener('keyup', () => loadData());
      document.getElementById('kategori').addEventListener('change', () => loadData());
      document.getElementById('sort').addEventListener('change', () => loadData());
    });
  </script>

</body>

</html>
