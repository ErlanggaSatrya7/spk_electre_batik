<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "Nilai Alternatif";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'baru';
$order = $sort === 'lama' ? 'id ASC' : 'id DESC';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Hitung total
$where = "WHERE 1=1";
$params = [];

if (!empty($search)) {
  $where .= " AND nama LIKE :search";
  $params[':search'] = "%$search%";
}

$countStmt = $conn->prepare("SELECT COUNT(*) FROM alternatif $where");
foreach ($params as $key => $val) {
  $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$totalData = $countStmt->fetchColumn();
$totalPages = ceil($totalData / $limit);

// Ambil alternatif
$altStmt = $conn->prepare("SELECT id, nama FROM alternatif $where ORDER BY $order LIMIT :limit OFFSET :offset");
foreach ($params as $key => $val) {
  $altStmt->bindValue($key, $val);
}
$altStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$altStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$altStmt->execute();
$alternatifs = $altStmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil kriteria
$kriteria = $conn->query("SELECT id, kode, nama FROM kriteria ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua nilai
$nilaiQuery = $conn->query("SELECT id_alternatif, id_kriteria, nilai FROM nilai");
$nilaiData = [];
while ($row = $nilaiQuery->fetch(PDO::FETCH_ASSOC)) {
  $nilaiData[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?> | <?= htmlspecialchars($pengaturan['nama_aplikasi']) ?></title>
  <link rel="icon" href="../../../assets/logo/<?= $logo ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
    if (localStorage.getItem("theme") === "dark") {
      document.documentElement.classList.add("dark");
    }
  </script>
  <style>
    .fade-in { animation: fadeIn 0.5s ease-out forwards; opacity: 0; transform: translateY(10px); }
    @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex">

<?php include '../layouts/splash.php'; ?>
<?php include '../layouts/sidebar.php'; ?>

<div class="ml-64 flex-1 flex flex-col">
  <?php include '../layouts/topbar.php'; ?>

  <main class="flex-1 px-6 py-6 fade-in">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-4">
      <div>
        <h1 class="text-2xl font-bold"><?= $pageTitle ?></h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Matriks nilai alternatif terhadap kriteria.</p>
      </div>
      <div class="flex items-center gap-2">
        <form method="GET" class="flex gap-2 items-center">
          <input type="text" name="search" placeholder="Cari nama..." value="<?= htmlspecialchars($search) ?>"
                 class="px-3 py-2 border rounded text-sm dark:bg-gray-800 dark:border-gray-700" />
          <select name="sort" onchange="this.form.submit()" class="px-3 py-2 border rounded text-sm dark:bg-gray-800 dark:border-gray-700">
            <option value="baru" <?= $sort === 'baru' ? 'selected' : '' ?>>Terbaru</option>
            <option value="lama" <?= $sort === 'lama' ? 'selected' : '' ?>>Terlama</option>
          </select>
        </form>
        <a href="export.php" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">📄 PDF</a>
        <a href="export_excel.php" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm">📊 Excel</a>
      </div>
    </div>

    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Alternatif</th>
            <?php foreach ($kriteria as $k): ?>
              <th class="px-4 py-3"><?= htmlspecialchars($k['kode']) ?></th>
            <?php endforeach ?>
            <th class="px-4 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alternatifs as $i => $alt): ?>
            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">
              <td class="px-4 py-3"><?= ($i + 1) + $offset ?></td>
              <td class="px-4 py-3"><?= htmlspecialchars($alt['nama']) ?></td>
              <?php foreach ($kriteria as $k): ?>
                <td class="px-4 py-3">
                  <?= isset($nilaiData[$alt['id']][$k['id']])
                        ? htmlspecialchars($nilaiData[$alt['id']][$k['id']])
                        : '<span class="text-gray-400">–</span>' ?>
                </td>
              <?php endforeach ?>
              <td class="px-4 py-3">
                <a href="form.php?id=<?= $alt['id'] ?>" class="text-indigo-600 hover:underline text-sm">Edit Nilai</a>
              </td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <div class="mt-6 flex justify-center space-x-1 text-sm">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
             class="px-3 py-1 rounded border <?= $i == $page ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-300 border-gray-300 dark:border-gray-600' ?> hover:bg-indigo-500 hover:text-white transition">
            <?= $i ?>
          </a>
        <?php endfor ?>
      </div>
    <?php endif ?>
  </main>

  <?php include '../layouts/footer.php'; ?>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
