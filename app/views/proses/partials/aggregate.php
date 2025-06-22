<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "💠 Matriks Agregat Dominasi";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$altIDs = array_column($alternatifs, 'id');

// Ambil Matriks Dominan Concordance & Discordance
function getDominanMatrix($table, $nilaiFunc) {
  global $conn, $altIDs;
  $matrix = [];
  foreach ($altIDs as $i) {
    foreach ($altIDs as $j) {
      if ($i == $j) continue;
      $matrix[$i][$j] = $nilaiFunc($i, $j);
    }
  }
  return $matrix;
}

// Ambil bobot kriteria untuk concordance
$kriteria = $conn->query("SELECT id, bobot FROM kriteria")->fetchAll(PDO::FETCH_ASSOC);
$bobot = [];
foreach ($kriteria as $k) {
  $bobot[$k['id']] = $k['bobot'];
}

// Ambil nilai alternatif
$nilai = [];
foreach ($conn->query("SELECT * FROM nilai") as $row) {
  $nilai[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
}

// Hitung matriks dominan concordance
$concordance = [];
foreach ($altIDs as $i) {
  foreach ($altIDs as $j) {
    if ($i == $j) continue;
    $cij = 0;
    foreach ($bobot as $kid => $b) {
      if (($nilai[$i][$kid] ?? 0) >= ($nilai[$j][$kid] ?? 0)) {
        $cij += $b;
      }
    }
    $concordance[$i][$j] = $cij;
  }
}

// Threshold
$totalC = array_sum(array_map('array_sum', $concordance));
$thresholdC = $totalC / (count($altIDs) * (count($altIDs) - 1));

// Dominan Concordance (1 jika ≥ threshold)
$dominC = [];
foreach ($concordance as $i => $row) {
  foreach ($row as $j => $val) {
    $dominC[$i][$j] = $val >= $thresholdC ? 1 : 0;
  }
}

// Hitung matriks discordance
$discordance = [];
foreach ($altIDs as $i) {
  foreach ($altIDs as $j) {
    if ($i == $j) continue;
    $maxDiff = 0;
    $maxAll = 0;
    foreach ($bobot as $kid => $b) {
      $diff = abs(($nilai[$i][$kid] ?? 0) - ($nilai[$j][$kid] ?? 0));
      $maxAll = max($maxAll, $diff);
      if (($nilai[$i][$kid] ?? 0) < ($nilai[$j][$kid] ?? 0)) {
        $maxDiff = max($maxDiff, $diff);
      }
    }
    $discordance[$i][$j] = $maxAll ? $maxDiff / $maxAll : 0;
  }
}

// Threshold
$totalD = array_sum(array_map('array_sum', $discordance));
$thresholdD = $totalD / (count($altIDs) * (count($altIDs) - 1));

// Dominan Discordance
$dominD = [];
foreach ($discordance as $i => $row) {
  foreach ($row as $j => $val) {
    $dominD[$i][$j] = $val >= $thresholdD ? 1 : 0;
  }
}

// Matriks agregat E = C ∩ D (logika AND)
$aggregate = [];
foreach ($altIDs as $i) {
  foreach ($altIDs as $j) {
    if ($i == $j) continue;
    $aggregate[$i][$j] = $dominC[$i][$j] && $dominD[$i][$j] ? 1 : 0;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?> | <?= $pengaturan['nama_aplikasi'] ?></title>
  <link rel="icon" href="../../../assets/logo/<?= $logo ?>">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">
<?php include '../layouts/splash.php'; include '../layouts/sidebar.php'; ?>
<div class="ml-64 flex-1">
  <?php include '../layouts/topbar.php'; ?>
  <main class="px-6 py-6">
    <h1 class="text-2xl font-bold mb-2"><?= $pageTitle ?></h1>
    <p class="text-sm mb-4 text-gray-500 dark:text-gray-400">E<sub>ij</sub> = C̄<sub>ij</sub> ∩ D̄<sub>ij</sub> (logika AND)</p>
    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th class="px-4 py-2">i → j</th>
            <?php foreach ($alternatifs as $b): ?>
              <th class="px-4 py-2"><?= htmlspecialchars($b['nama']) ?></th>
            <?php endforeach ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alternatifs as $a): ?>
            <tr class="border-b border-gray-300 dark:border-gray-700">
              <th class="px-4 py-2 bg-indigo-50 dark:bg-indigo-900"><?= htmlspecialchars($a['nama']) ?></th>
              <?php foreach ($alternatifs as $b): ?>
                <td class="px-4 py-2">
                  <?= $a['id'] === $b['id'] ? '–' : $aggregate[$a['id']][$b['id']] ?>
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
</body>
</html>
