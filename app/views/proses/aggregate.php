<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "💠 Matriks Agregat (Langkah 6)";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$altIDs = array_column($alternatifs, 'id');
$altNames = array_column($alternatifs, 'nama', 'id');
$kriteria = $conn->query("SELECT id, bobot FROM kriteria")->fetchAll(PDO::FETCH_ASSOC);
$bobot = array_column($kriteria, 'bobot', 'id');

$nilai = [];
foreach ($conn->query("SELECT * FROM nilai") as $row) {
  $nilai[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
}

// Hitung Concordance
$concordance = [];
foreach ($altIDs as $i) {
  foreach ($altIDs as $j) {
    if ($i === $j) continue;
    $cij = 0;
    foreach ($bobot as $kid => $b) {
      if (($nilai[$i][$kid] ?? 0) >= ($nilai[$j][$kid] ?? 0)) {
        $cij += $b;
      }
    }
    $concordance[$i][$j] = $cij;
  }
}
$thresholdC = count($altIDs) > 1 ? array_sum(array_map('array_sum', $concordance)) / (count($altIDs) * (count($altIDs) - 1)) : 0;

// Dominan Concordance
$dominC = [];
foreach ($concordance as $i => $row) {
  foreach ($row as $j => $val) {
    $dominC[$i][$j] = $val >= $thresholdC ? 1 : 0;
  }
}

// Hitung Discordance
$discordance = [];
foreach ($altIDs as $i) {
  foreach ($altIDs as $j) {
    if ($i === $j) continue;
    $maxDiff = 0;
    $maxAll = 0;
    foreach ($bobot as $kid => $b) {
      $a = $nilai[$i][$kid] ?? 0;
      $b = $nilai[$j][$kid] ?? 0;
      $diff = abs($a - $b);
      $maxAll = max($maxAll, $diff);
      if ($a < $b) $maxDiff = max($maxDiff, $diff);
    }
    $discordance[$i][$j] = $maxAll > 0 ? $maxDiff / $maxAll : 0;
  }
}
$thresholdD = count($altIDs) > 1 ? array_sum(array_map('array_sum', $discordance)) / (count($altIDs) * (count($altIDs) - 1)) : 0;

// Dominan Discordance
$dominD = [];
foreach ($discordance as $i => $row) {
  foreach ($row as $j => $val) {
    $dominD[$i][$j] = $val >= $thresholdD ? 1 : 0;
  }
}

// Matriks agregat
$aggregate = [];
foreach ($altIDs as $i) {
  foreach ($altIDs as $j) {
    if ($i === $j) {
      $aggregate[$i][$j] = '-';
    } else {
      $aggregate[$i][$j] = ($dominC[$i][$j] && $dominD[$i][$j]) ? 1 : 0;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?> | <?= htmlspecialchars($pengaturan['nama_aplikasi']) ?></title>
  <link rel="icon" href="../../../assets/logo/<?= $logo ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .fade-in { animation: fadeIn .5s ease-out forwards; opacity: 0; transform: translateY(10px); }
    @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
    th, td { min-width: 80px; }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">
<?php include '../layouts/splash.php'; include '../layouts/sidebar.php'; ?>
<div class="ml-64 flex-1">
  <?php include '../layouts/topbar.php'; ?>
  <main class="px-6 py-6 fade-in">
    <h1 class="text-2xl font-bold mb-4"><?= $pageTitle ?></h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
      Matriks Agregat adalah hasil dari irisan <strong>Dominan Concordance ∩ Dominan Discordance</strong>. Nilai 1 menandakan alternatif i mendominasi j.
    </p>

    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center border border-gray-300 dark:border-gray-600">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th class="px-4 py-2 bg-gray-50 dark:bg-gray-700">📌</th>
            <?php foreach ($altIDs as $j): ?>
              <th class="px-4 py-2"><?= htmlspecialchars($altNames[$j]) ?></th>
            <?php endforeach ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($altIDs as $i): ?>
            <tr class="border-t border-gray-200 dark:border-gray-700">
              <th class="px-4 py-2 bg-gray-50 dark:bg-gray-700 font-semibold"><?= htmlspecialchars($altNames[$i]) ?></th>
              <?php foreach ($altIDs as $j): ?>
                <td class="px-4 py-2"><?= $aggregate[$i][$j] ?></td>
              <?php endforeach ?>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <div class="mt-6 text-sm text-gray-600 dark:text-gray-400">
      Threshold Concordance: <strong><?= number_format($thresholdC, 4) ?></strong> &nbsp; | &nbsp;
      Threshold Discordance: <strong><?= number_format($thresholdD, 4) ?></strong>
    </div>
  </main>
  <?php include '../layouts/footer.php'; ?>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
