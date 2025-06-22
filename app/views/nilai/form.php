<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "Input Nilai Alternatif";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$id_alternatif = $_GET['id'] ?? null;
if (!$id_alternatif) {
  header("Location: index.php");
  exit;
}

$altStmt = $conn->prepare("SELECT * FROM alternatif WHERE id = ?");
$altStmt->execute([$id_alternatif]);
$alt = $altStmt->fetch(PDO::FETCH_ASSOC);
if (!$alt) {
  echo "Alternatif tidak ditemukan.";
  exit;
}

$kriteria = $conn->query("SELECT id, nama, kode FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$nilaiStmt = $conn->prepare("SELECT id_kriteria, nilai FROM nilai WHERE id_alternatif = ?");
$nilaiStmt->execute([$id_alternatif]);
$nilaiData = [];
while ($row = $nilaiStmt->fetch(PDO::FETCH_ASSOC)) {
  $nilaiData[$row['id_kriteria']] = $row['nilai'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $pageTitle ?> | <?= htmlspecialchars($pengaturan['nama_aplikasi'] ?? 'SPK') ?></title>
  <link rel="icon" href="../../../assets/logo/<?= $logo ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.classList.add('dark');
    }
  </script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    .fade-in { animation: fadeIn 0.5s ease-out forwards; opacity: 0; transform: translateY(20px); }
    @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
    .alert-success {
      animation: fadeSuccess 0.5s ease-out forwards;
      background-color: #d1fae5;
      color: #065f46;
    }
    @keyframes fadeSuccess {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white transition min-h-screen">

<main class="min-h-screen flex items-center justify-center px-6 py-12 fade-in">
  <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-2xl">
    <h1 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mb-2"><?= $pageTitle ?></h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
      Masukkan nilai <strong class="text-indigo-600 dark:text-indigo-300"><?= htmlspecialchars($alt['nama']) ?></strong> berdasarkan setiap kriteria.
    </p>

    <?php if (isset($_GET['success'])): ?>
      <div class="p-3 mb-4 rounded alert-success text-sm shadow">✅ Nilai berhasil disimpan! Mengarahkan...</div>
      <script>setTimeout(() => { window.location.href = 'index.php'; }, 1500);</script>
    <?php endif; ?>

    <form action="../../../app/controllers/NilaiController.php" method="POST" class="space-y-5" onsubmit="return validateForm()">
      <input type="hidden" name="id_alternatif" value="<?= $id_alternatif ?>">

      <?php foreach ($kriteria as $k): ?>
        <div>
          <label class="block text-sm font-medium mb-1">
            <?= htmlspecialchars($k['kode']) ?> - <?= htmlspecialchars($k['nama']) ?> <span class="text-red-500">*</span>
          </label>
          <input type="number" name="nilai[<?= $k['id'] ?>]" step="0.01" min="0" required
                 value="<?= isset($nilaiData[$k['id']]) ? htmlspecialchars($nilaiData[$k['id']]) : '' ?>"
                 class="w-full px-4 py-2 border rounded bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-sm shadow-sm focus:ring-2 focus:ring-indigo-500">
        </div>
      <?php endforeach; ?>

      <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
        <a href="index.php" class="text-sm text-gray-500 dark:text-gray-300 hover:underline">← Kembali</a>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded text-sm font-medium shadow">Simpan Nilai</button>
      </div>
    </form>
  </div>
</main>

<script>
  function validateForm() {
    const inputs = document.querySelectorAll('input[type="number"]');
    for (const input of inputs) {
      if (input.value.trim() === '') {
        alert("Semua nilai wajib diisi!");
        input.focus();
        return false;
      }
    }
    return true;
  }
</script>

</body>
</html>
