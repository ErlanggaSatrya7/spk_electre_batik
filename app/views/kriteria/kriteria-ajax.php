<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$search = $_GET['search'] ?? '';
$tipe   = $_GET['tipe'] ?? '';
$sort   = $_GET['sort'] ?? 'baru';
$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit  = 10;
$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";
$params = [];

if (!empty($search)) {
  $where .= " AND (nama LIKE :search1 OR kode LIKE :search2)";
  $params[':search1'] = "%$search%";
  $params[':search2'] = "%$search%";
}

if (!empty($tipe)) {
  $where .= " AND tipe = :tipe";
  $params[':tipe'] = $tipe;
}

$order = ($sort === 'lama') ? "ORDER BY created_at ASC" : "ORDER BY created_at DESC";

// Hitung total data
$countStmt = $conn->prepare("SELECT COUNT(*) FROM kriteria $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Ambil data dengan pagination
$stmt = $conn->prepare("SELECT * FROM kriteria $where $order LIMIT :limit OFFSET :offset");

// Binding parameter
foreach ($params as $key => $val) {
  $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$kriterias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<table class="min-w-full text-sm text-gray-800 dark:text-gray-200">
  <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-semibold">
    <tr>
      <th class="px-4 py-3 text-left">#</th>
      <th class="px-4 py-3 text-left">Kode</th>
      <th class="px-4 py-3 text-left">Nama</th>
      <th class="px-4 py-3 text-left">Bobot</th>
      <th class="px-4 py-3 text-left">Tipe</th>
      <th class="px-4 py-3 text-left">Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($kriterias as $i => $kr): ?>
      <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
        <td class="px-4 py-3"><?= ($offset + $i + 1) ?></td>
        <td class="px-4 py-3 font-medium"><?= htmlspecialchars($kr['kode']) ?></td>
        <td class="px-4 py-3"><?= htmlspecialchars($kr['nama']) ?></td>
        <td class="px-4 py-3"><?= number_format((float)$kr['bobot'], 2) ?></td>
        <td class="px-4 py-3 capitalize"><?= $kr['tipe'] === 'benefit' ? 'Benefit' : 'Cost' ?></td>
        <td class="px-4 py-3">
          <div class="flex space-x-2">
            <a href="form.php?id=<?= $kr['id'] ?>" class="text-indigo-600 hover:underline">Edit</a>
            <a href="../../../app/controllers/KriteriaController.php?action=delete&id=<?= $kr['id'] ?>" onclick="return confirm('Hapus kriteria ini?')" class="text-red-600 hover:underline">Hapus</a>
          </div>
        </td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>

<?php if ($totalPages > 1): ?>
  <div class="mt-4 flex justify-center space-x-1 text-sm">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <button onclick="loadKriteriaPage(<?= $i ?>)"
              class="px-3 py-1 rounded border <?= $i == $page ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600' ?> hover:bg-indigo-500 hover:text-white transition">
        <?= $i ?>
      </button>
    <?php endfor ?>
  </div>
<?php endif ?>
