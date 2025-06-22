<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$isEdit = isset($_GET['id']);
$pageTitle = $isEdit ? "Edit Alternatif" : "Tambah Alternatif";

$data = [
  'id' => '',
  'nama' => '',
  'kategori' => '',
  'asal_daerah' => '',
  'deskripsi' => ''
];
$existingImages = [];

if ($isEdit) {
  $stmt = $conn->prepare("SELECT * FROM alternatif WHERE id = ?");
  $stmt->execute([$_GET['id']]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);

  $imgStmt = $conn->prepare("SELECT filename FROM gambar_alternatif WHERE alternatif_id = ?");
  $imgStmt->execute([$data['id']]);
  $existingImages = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
}

$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';
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
    if (localStorage.getItem("theme") === "dark") {
      document.documentElement.classList.add("dark");
    }
  </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">

<main class="min-h-screen flex items-center justify-center px-6 py-12 fade-in">
  <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-2xl">
    <h1 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mb-2"><?= $pageTitle ?></h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Lengkapi informasi batik untuk alternatif SPK.</p>

    <?php if (isset($_GET['success'])): ?>
      <script>window.location.href = 'index.php';</script>
    <?php endif; ?>

    <form action="../../../app/controllers/AlternatifController.php?action=<?= $isEdit ? 'update&id=' . $_GET['id'] : 'store' ?>" method="POST" enctype="multipart/form-data" class="space-y-5" onsubmit="return validateForm()">

      <input type="hidden" name="id" value="<?= $data['id'] ?>">

      <div>
        <label class="block text-sm font-medium">Nama Batik <span class="text-red-500">*</span></label>
        <input type="text" name="nama" id="nama" required minlength="3" value="<?= htmlspecialchars($data['nama']) ?>"
               class="w-full px-4 py-2 border rounded bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500">
      </div>

      <div>
        <label class="block text-sm font-medium">Kategori</label>
        <input type="text" name="kategori" value="<?= htmlspecialchars($data['kategori']) ?>"
               class="w-full px-4 py-2 border rounded bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-sm">
      </div>

      <div>
        <label class="block text-sm font-medium">Asal Daerah</label>
        <input type="text" name="asal_daerah" value="<?= htmlspecialchars($data['asal_daerah']) ?>"
               class="w-full px-4 py-2 border rounded bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-sm">
      </div>

      <div>
        <label class="block text-sm font-medium">Deskripsi</label>
        <textarea name="deskripsi" rows="4"
                  class="w-full px-4 py-2 border rounded bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-sm"><?= htmlspecialchars($data['deskripsi']) ?></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium">Gambar Batik (boleh lebih dari 1)</label>
        <div id="dropzone" class="relative w-full px-4 py-10 border-2 border-dashed rounded cursor-pointer bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 text-center transition">
          <span class="text-sm text-gray-500 dark:text-gray-400">Seret gambar ke sini atau klik untuk memilih</span>
          <input type="file" name="gambar[]" id="gambar" accept="image/*" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format JPG, PNG. Bisa upload lebih dari satu.</p>
        <div id="previewContainer" class="flex flex-wrap gap-3 mt-4">
          <?php foreach ($existingImages as $img): ?>
            <img src="../../../assets/images/<?= htmlspecialchars($img) ?>" class="w-24 h-24 object-cover rounded shadow">
          <?php endforeach ?>
        </div>
      </div>

      <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
        <a href="index.php" class="text-sm text-gray-500 dark:text-gray-300 hover:underline">← Kembali</a>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded text-sm font-medium shadow">Simpan</button>
      </div>
    </form>
  </div>
</main>

<script>
  function validateForm() {
    const nama = document.getElementById('nama').value.trim();
    if (nama.length < 3) {
      alert("Nama batik minimal 3 karakter.");
      return false;
    }
    return true;
  }

  const dropzone = document.getElementById('dropzone');
  const fileInput = document.getElementById('gambar');
  const previewContainer = document.getElementById('previewContainer');

  dropzone.addEventListener('dragover', e => {
    e.preventDefault();
    dropzone.classList.add('bg-indigo-50', 'dark:bg-gray-700');
  });

  dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('bg-indigo-50', 'dark:bg-gray-700');
  });

  dropzone.addEventListener('drop', e => {
    e.preventDefault();
    dropzone.classList.remove('bg-indigo-50', 'dark:bg-gray-700');
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(file => {
      if (file.type.startsWith('image/')) {
        dt.items.add(file);
      }
    });
    fileInput.files = dt.files;
    showPreviews(dt.files);
  });

  fileInput.addEventListener('change', function () {
    showPreviews(this.files);
  });

  function showPreviews(files) {
    previewContainer.innerHTML = '';
    Array.from(files).forEach(file => {
      const reader = new FileReader();
      reader.onload = e => {
        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = "w-24 h-24 object-cover rounded shadow";
        previewContainer.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
  }
</script>

</body>
</html>
