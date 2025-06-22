<?php
require_once '../../../config/db.php';

$search  = $_GET['search'] ?? '';
$filter  = $_GET['kategori'] ?? '';
$sort    = $_GET['sort'] ?? 'baru';
$page    = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit   = 10;
$offset  = ($page - 1) * $limit;

$where   = "WHERE 1=1";
$params  = [];

// Filter: search by nama
if (!empty($search)) {
  $where .= " AND nama LIKE :search";
  $params[':search'] = "%$search%";
}

// Filter: kategori
if (!empty($filter)) {
  $where .= " AND kategori = :kategori";
  $params[':kategori'] = $filter;
}

// Urutan
$order = ($sort === 'lama') ? 'id ASC' : 'id DESC';

// Total count
$countSQL = "SELECT COUNT(*) FROM alternatif $where";
$countStmt = $conn->prepare($countSQL);
foreach ($params as $key => $val) {
  $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Fetch data
$dataSQL = "SELECT * FROM alternatif $where ORDER BY $order LIMIT :limit OFFSET :offset";
$dataStmt = $conn->prepare($dataSQL);
foreach ($params as $key => $val) {
  $dataStmt->bindValue($key, $val);
}
$dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$dataStmt->execute();
$alternatifs = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<table class="min-w-full text-sm text-gray-700 dark:text-gray-200">
  <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-semibold">
    <tr>
      <th class="px-4 py-3 text-left">#</th>
      <th class="px-4 py-3 text-left">Nama</th>
      <th class="px-4 py-3 text-left">Kategori</th>
      <th class="px-4 py-3 text-left">Asal</th>
      <th class="px-4 py-3 text-left">Deskripsi</th>
      <th class="px-4 py-3 text-left">Gambar</th>
      <th class="px-4 py-3 text-left">Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($alternatifs as $i => $alt): ?>
      <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
        <td class="px-4 py-3"><?= ($i + 1) + $offset ?></td>
        <td class="px-4 py-3"><?= htmlspecialchars($alt['nama']) ?></td>
        <td class="px-4 py-3"><?= htmlspecialchars($alt['kategori']) ?></td>
        <td class="px-4 py-3"><?= htmlspecialchars($alt['asal_daerah']) ?></td>
        <td class="px-4 py-3 max-w-xs">
          <div class="line-clamp-2"><?= htmlspecialchars($alt['deskripsi']) ?></div>
        </td>
        <td class="px-4 py-3">
          <?php
            $imgs = $conn->prepare("SELECT filename FROM gambar_alternatif WHERE alternatif_id = ?");
            $imgs->execute([$alt['id']]);
            $files = $imgs->fetchAll(PDO::FETCH_COLUMN);
            if ($files):
          ?>
            <img src="../../../assets/images/<?= htmlspecialchars($files[0]) ?>" alt="gambar"
                 class="w-12 h-12 object-cover rounded shadow cursor-pointer hover:ring hover:ring-indigo-400"
                 onclick='openGalleryModal(<?= json_encode($files) ?>)'>
          <?php else: ?>
            <span class="text-gray-400 italic">Tidak ada</span>
          <?php endif ?>
        </td>
        <td class="px-4 py-3">
          <div class="flex space-x-2">
            <a href="form.php?id=<?= $alt['id'] ?>" class="text-indigo-600 hover:underline">Edit</a>
            <a href="../../../app/controllers/AlternatifController.php?action=delete&id=<?= $alt['id'] ?>"
               onclick="return confirm('Yakin ingin menghapus data ini?')"
               class="text-red-600 hover:underline">Hapus</a>
          </div>
        </td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>

<?php if ($total > $limit): ?>
  <div class="mt-6 flex justify-center space-x-1 text-sm">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <button onclick="loadPage(<?= $i ?>)"
              class="px-3 py-1 rounded border <?= $i == $page ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-300 border-gray-300 dark:border-gray-600' ?> hover:bg-indigo-500 hover:text-white transition">
        <?= $i ?>
      </button>
    <?php endfor ?>
  </div>
<?php endif ?>
