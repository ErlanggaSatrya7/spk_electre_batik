<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pageTitle = "Riwayat Perubahan Pengaturan";

$logs = $conn->query("SELECT l.*, u.username FROM log_pengaturan l JOIN users u ON l.id_user = u.id ORDER BY waktu DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">
  <?php include '../layouts/sidebar.php'; ?>

  <div class="ml-64 flex-1 flex flex-col">
    <?php include '../layouts/topbar.php'; ?>

    <main class="flex-1 px-6 py-6 bg-gray-50">
      <h1 class="text-2xl font-bold mb-4 text-gray-800"><?= $pageTitle ?></h1>

      <div class="space-y-4">
        <?php foreach ($logs as $log): ?>
          <div class="bg-white p-4 rounded shadow text-sm">
            <div class="mb-1 text-gray-600">
              <strong><?= htmlspecialchars($log['username']) ?></strong> - <?= $log['waktu'] ?>
            </div>
            <pre class="bg-gray-100 p-3 rounded overflow-auto max-w-full text-xs"><?= htmlspecialchars($log['isi_lama']) ?></pre>
          </div>
        <?php endforeach; ?>
      </div>
    </main>

    <?php include '../layouts/footer.php'; ?>
  </div>
</body>
</html>
