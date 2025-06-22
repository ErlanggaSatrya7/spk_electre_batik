<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';
$pageTitle = "Import Data Alternatif";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?> | <?= $pengaturan['nama_aplikasi'] ?? 'SPK' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="../../../assets/logo/<?= $logo ?>">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
  <div class="bg-white dark:bg-gray-800 text-gray-800 dark:text-white p-8 rounded-lg shadow-lg w-full max-w-xl">
    <h1 class="text-xl font-bold mb-4">📥 Import Alternatif</h1>
    <form action="import-handler.php" method="POST" enctype="multipart/form-data" class="space-y-5">
      <div>
        <label class="block text-sm font-medium">Pilih File Excel (.xlsx) <span class="text-red-500">*</span></label>
        <input type="file" name="file" accept=".xls,.xlsx" required class="w-full border px-3 py-2 rounded bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-sm">
      </div>
      <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
        <a href="index.php" class="text-sm text-gray-500 hover:underline">← Kembali</a>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded text-sm shadow">Import</button>
      </div>
    </form>
  </div>
</body>
</html>
