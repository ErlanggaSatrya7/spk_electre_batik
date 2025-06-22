<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../helpers/auth.php';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
include __DIR__ . '/../layouts/topbar.php';

// Ambil data log
$stmt = $conn->query("
  SELECT l.*, u.username 
  FROM log_aktivitas l
  JOIN users u ON l.id_user = u.id
  ORDER BY l.waktu DESC
");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex-1 p-6">
  <h2 class="text-xl font-bold mb-4">Log Aktivitas</h2>
  <div class="overflow-auto bg-white shadow rounded-lg">
    <table class="min-w-full border">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-3 border">No</th>
          <th class="p-3 border">Username</th>
          <th class="p-3 border">Aktivitas</th>
          <th class="p-3 border">Waktu</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data as $i => $row): ?>
          <tr class="hover:bg-gray-50">
            <td class="p-3 border text-center"><?= $i + 1 ?></td>
            <td class="p-3 border"><?= htmlspecialchars($row['username']) ?></td>
            <td class="p-3 border"><?= htmlspecialchars($row['aktivitas']) ?></td>
            <td class="p-3 border"><?= $row['waktu'] ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
