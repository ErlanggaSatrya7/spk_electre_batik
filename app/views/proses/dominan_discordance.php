<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "❌ Matriks Dominan Discordance";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT id FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$nilai = [];
foreach ($conn->query("SELECT id_alternatif, id_kriteria, nilai FROM nilai") as $row) {
  $nilai[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
}

// Hitung matriks discordance
$discordance = [];
foreach ($alternatifs as $a) {
  foreach ($alternatifs as $b) {
    if ($a['id'] == $b['id']) continue;
    $maxDiff = 0;
    $maxAll = 0;
    foreach ($kriteria as $k) {
      $diff = abs(($nilai[$a['id']][$k['id']] ?? 0) - ($nilai[$b['id']][$k['id']] ?? 0));
      $maxAll = max($maxAll, $diff);
      if (($nilai[$a['id']][$k['id']] ?? 0) < ($nilai[$b['id']][$k['id']] ?? 0)) {
        $maxDiff = max($maxDiff, $diff);
      }
    }
    $discordance[$a['id']][$b['id']] = $maxAll == 0 ? 0 : $maxDiff / $maxAll;
  }
}

// Hitung threshold
$sum = 0;
$count = 0;
foreach ($discordance as $row) {
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
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">
<?php include '../layouts/splash.php'; include '../layouts/sidebar.php'; ?>
<div class="ml-64 flex-1">
  <?php include '../layouts/topbar.php'; ?>
  <main class="px-6 py-6 fade-in">
    <h1 class="text-2xl font-bold mb-2"><?= $pageTitle ?></h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Threshold: <?= number_format($threshold, 4) ?> — nilai ≥ threshold → 1, else 0</p>

    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center">
        <thead class="bg-rose-100 dark:bg-rose-900 text-rose-700 dark:text-rose-300">
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
              <th class="px-4 py-3 bg-rose-50 dark:bg-rose-900"><?= htmlspecialchars($a['nama']) ?></th>
              <?php foreach ($alternatifs as $b): ?>
                <td class="px-4 py-3">
                  <?php
                    if ($a['id'] == $b['id']) echo '–';
                    else {
                      $v = $discordance[$a['id']][$b['id']];
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
