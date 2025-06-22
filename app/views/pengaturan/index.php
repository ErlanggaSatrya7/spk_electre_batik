<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pageTitle = "Pengaturan";
$stmt = $conn->prepare("SELECT * FROM pengaturan WHERE id = 1");
$stmt->execute();
$pengaturan = $stmt->fetch(PDO::FETCH_ASSOC);

$logo = $pengaturan['logo'] ?? null;
$bg   = $pengaturan['background_image'] ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?> | <?= htmlspecialchars($pengaturan['nama_aplikasi'] ?? 'SPK') ?></title>
  <?php if ($logo): ?>
    <link rel="icon" href="../../assets/logo/<?= $logo ?>">
  <?php endif; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex transition">

<?php include '../layouts/splash.php'; ?>
<?php include '../layouts/sidebar.php'; ?>

<div class="ml-64 flex-1 flex flex-col">
  <?php include '../layouts/topbar.php'; ?>

  <main class="flex-1 px-6 py-6 max-w-3xl">
    <h1 class="text-2xl font-bold mb-4"><?= $pageTitle ?> Aplikasi</h1>

    <?php if (!empty($_SESSION['success'])): ?>
      <div class="mb-4 p-4 bg-green-100 text-green-800 rounded shadow text-sm">
        ✅ <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
      <div class="mb-4 p-4 bg-red-100 text-red-800 rounded shadow text-sm">
        ⚠️ <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <form action="../../controllers/pengaturan/update.php" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-5">
      <input type="hidden" name="id" value="<?= $pengaturan['id'] ?? 1 ?>">

      <div>
        <label class="block text-sm font-medium">Nama Aplikasi</label>
        <input type="text" name="nama_aplikasi" required
               value="<?= htmlspecialchars($pengaturan['nama_aplikasi']) ?>"
               class="mt-1 block w-full px-4 py-2 border rounded text-sm dark:bg-gray-900 dark:border-gray-600">
      </div>

      <div>
        <label class="block text-sm font-medium">Deskripsi</label>
        <textarea name="deskripsi" rows="3" required
                  class="mt-1 block w-full px-4 py-2 border rounded text-sm dark:bg-gray-900 dark:border-gray-600"><?= htmlspecialchars($pengaturan['deskripsi']) ?></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium">Versi Aplikasi</label>
        <input type="text" name="versi"
               value="<?= htmlspecialchars($pengaturan['versi']) ?>"
               class="mt-1 block w-full px-4 py-2 border rounded text-sm dark:bg-gray-900 dark:border-gray-600">
      </div>

      <div>
        <label class="block text-sm font-medium">Logo Aplikasi</label>
        <input type="file" name="logo" accept="image/*"
               class="mt-1 block w-full text-sm file:py-2 file:px-4 file:rounded file:border-0
                      file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
               onchange="previewImage(this, 'logoPreview')">
        <div class="mt-3">
          <img id="logoPreview" src="<?= $logo ? '../../assets/logo/' . htmlspecialchars($logo) : '' ?>" alt="Logo Preview"
               class="h-20 rounded shadow <?= $logo ? '' : 'hidden' ?>">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium">Gambar Latar Login (background)</label>
        <input type="file" name="background_image" accept="image/*"
               class="mt-1 block w-full text-sm file:py-2 file:px-4 file:rounded file:border-0
                      file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
               onchange="previewImage(this, 'bgPreview')">
        <div class="mt-3">
          <img id="bgPreview" src="<?= $bg ? '../../assets/images/' . htmlspecialchars($bg) : '' ?>" alt="Preview Background"
               class="h-32 rounded shadow <?= $bg ? '' : 'hidden' ?>">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium">Judul Halaman Login</label>
        <input type="text" name="login_judul"
               value="<?= htmlspecialchars($pengaturan['login_judul'] ?? '') ?>"
               class="mt-1 block w-full px-4 py-2 border rounded text-sm dark:bg-gray-900 dark:border-gray-600">
      </div>

      <div>
        <label class="block text-sm font-medium">Deskripsi Halaman Login</label>
        <textarea name="login_deskripsi" rows="3"
                  class="mt-1 block w-full px-4 py-2 border rounded text-sm dark:bg-gray-900 dark:border-gray-600"><?= htmlspecialchars($pengaturan['login_deskripsi'] ?? '') ?></textarea>
      </div>

      <div>
        <label class="inline-flex items-center cursor-pointer">
          <input type="checkbox" name="maintenance" value="1"
                 <?= ($pengaturan['maintenance'] ?? 0) ? 'checked' : '' ?>
                 class="form-checkbox text-indigo-600 rounded">
          <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Aktifkan Mode Maintenance</span>
        </label>
      </div>

      <div class="pt-4 flex items-center gap-4">
        <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded text-sm font-medium shadow">
          💾 Simpan Perubahan
        </button>
        <a href="../../controllers/pengaturan/reset.php"
           onclick="return confirm('Yakin ingin reset ke default?')"
           class="text-sm text-red-600 hover:underline">🔄 Reset ke Default</a>
      </div>
    </form>
  </main>

  <?php include '../layouts/footer.php'; ?>
</div>

<script>
  lucide.createIcons();

  function previewImage(input, targetId) {
    const img = document.getElementById(targetId);
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = e => {
        img.src = e.target.result;
        img.classList.remove('hidden');
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>

</body>
</html>
