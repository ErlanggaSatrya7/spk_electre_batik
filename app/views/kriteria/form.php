<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = isset($_GET['id']) ? "Edit Kriteria" : "Tambah Kriteria";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$data = [
  'id' => '',
  'kode' => '',
  'nama' => '',
  'bobot' => '',
  'tipe' => 'benefit'
];

if (isset($_GET['id'])) {
  $stmt = $conn->prepare("SELECT * FROM kriteria WHERE id = ?");
  $stmt->execute([$_GET['id']]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title><?= $pageTitle ?> | <?= $pengaturan['nama_aplikasi'] ?? 'SPK' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
  </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white transition min-h-screen">

<main class="min-h-screen flex items-center justify-center px-6 py-12 fade-in">
  <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-xl">
    <h1 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mb-2"><?= $pageTitle ?></h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Lengkapi data kriteria untuk perhitungan SPK.</p>

    <?php if (isset($_GET['success'])): ?>
      <script>window.location.href = 'index.php';</script>
    <?php endif; ?>

    <form action="../../../app/controllers/KriteriaController.php?action=<?= isset($_GET['id']) ? 'update&id=' . $_GET['id'] : 'store' ?>"
          method="POST" class="space-y-5" onsubmit="return validateForm()">

      <input type="hidden" name="id" value="<?= $data['id'] ?>">

      <div>
        <label class="block text-sm font-medium mb-1">Kode Kriteria <span class="text-red-500">*</span></label>
        <input type="text" name="kode" value="<?= htmlspecialchars($data['kode']) ?>" required
               class="w-full px-4 py-2 border rounded bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-sm">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Nama Kriteria <span class="text-red-500">*</span></label>
        <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required
               class="w-full px-4 py-2 border rounded bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-sm">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Bobot <span class="text-red-500">*</span></label>
        <input type="number" name="bobot" id="bobot" step="0.01"
               value="<?= is_numeric($data['bobot']) ? number_format($data['bobot'], 2, '.', '') : '' ?>"
               required class="w-full px-4 py-2 border rounded bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-sm">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Tipe Kriteria <span class="text-red-500">*</span></label>
        <select name="tipe" required
                class="w-full px-4 py-2 border rounded bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-sm">
          <option value="benefit" <?= $data['tipe'] === 'benefit' ? 'selected' : '' ?>>Benefit</option>
          <option value="cost" <?= $data['tipe'] === 'cost' ? 'selected' : '' ?>>Cost</option>
        </select>
      </div>

      <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
        <a href="index.php" class="text-sm text-gray-500 dark:text-gray-300 hover:underline">← Kembali</a>
        <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded text-sm font-medium shadow">
          Simpan
        </button>
      </div>
    </form>
  </div>
</main>

<script>
  function validateForm() {
    const bobot = document.getElementById("bobot");
    if (bobot.value.trim() === "") {
      alert("Bobot tidak boleh kosong.");
      return false;
    }
    bobot.value = parseFloat(bobot.value).toFixed(2);
    return true;
  }
</script>

</body>
</html>
