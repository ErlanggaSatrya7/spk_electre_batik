<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "✅ Matriks Dominan Concordance";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT id, bobot FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Ambil nilai
$nilai = [];
foreach ($conn->query("SELECT id_alternatif, id_kriteria, nilai FROM nilai") as $row) {
  $nilai[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
}

// Hitung matriks concordance
$concordance = [];
foreach ($alternatifs as $a) {
  foreach ($alternatifs as $b) {
    if ($a['id'] == $b['id']) continue;
    $total = 0;
    foreach ($kriteria as $k) {
      $valA = $nilai[$a['id']][$k['id']] ?? 0;
      $valB = $nilai[$b['id']][$k['id']] ?? 0;
      if ($valA >= $valB) {
        $total += $k['bobot'];
      }
    }
    $concordance[$a['id']][$b['id']] = $total;
  }
}

// Hitung threshold (rata-rata nilai concordance)
$sum = 0;
$count = 0;
foreach ($concordance as $row) {
  foreach ($row as $val) {
    $sum += $val;
    $count++;
  }
}
$threshold = $count ? $sum / $count : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?> | <?= $pengaturan['nama_aplikasi'] ?></title>
  <link rel="icon" href="../../../assets/logo/<?= $logo ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>.fade-in { animation: fadeIn .5s ease-out forwards; opacity: 0; transform: translateY(10px); } @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }</style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">
<?php include '../layouts/splash.php'; include '../layouts/sidebar.php'; ?>
<div class="ml-64 flex-1">
  <?php include '../layouts/topbar.php'; ?>
  <main class="px-6 py-6 fade-in">
    <h1 class="text-2xl font-bold mb-2"><?= $pageTitle ?></h1>
    <p class="text-sm mb-4 text-gray-500 dark:text-gray-400">Threshold: <?= number_format($threshold, 4) ?> — nilai ≥ threshold → 1, else 0</p>

    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center text-gray-800 dark:text-gray-200">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th class="px-4 py-3">i → j</th>
            <?php foreach ($alternatifs as $b): ?>
              <th class="px-4 py-3"><?= htmlspecialchars($b['nama']) ?></th>
            <?php endforeach ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alternatifs as $a): ?>
            <tr class="border-b border-gray-200 dark:border-gray-700">
              <th class="px-4 py-3 bg-indigo-50 dark:bg-indigo-900"><?= htmlspecialchars($a['nama']) ?></th>
              <?php foreach ($alternatifs as $b): ?>
                <td class="px-4 py-3">
                  <?php
                    if ($a['id'] == $b['id']) echo '–';
                    else {
                      $v = $concordance[$a['id']][$b['id']];
                      echo $v >= $threshold ? '1' : '0';
                    }
                  ?>
                </td>
              <?php endforeach ?>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </main>
  <?php include '../layouts/footer.php'; ?>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
