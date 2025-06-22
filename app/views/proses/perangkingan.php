<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "🏆 Perangkingan Alternatif";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$altIDs = array_column($alternatifs, 'id');
$altNames = array_column($alternatifs, 'nama', 'id');

// Ambil nilai
$nilai = [];
foreach ($conn->query("SELECT * FROM nilai") as $row) {
  $nilai[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
}

// Ambil bobot
$bobot = [];
foreach ($conn->query("SELECT id, bobot FROM kriteria") as $row) {
  $bobot[$row['id']] = $row['bobot'];
}

// Hitung matriks dominan concordance
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
$dominC = [];
foreach ($concordance as $i => $row) {
  foreach ($row as $j => $val) {
    $dominC[$i][$j] = $val >= $thresholdC ? 1 : 0;
  }
}

// Hitung matriks dominan discordance
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
$dominD = [];
foreach ($discordance as $i => $row) {
  foreach ($row as $j => $val) {
    $dominD[$i][$j] = $val >= $thresholdD ? 1 : 0;
  }
}

// Matriks agregat
$aggregate = [];
foreach ($altIDs as $i) {
  $aggregate[$i] = [];
  foreach ($altIDs as $j) {
    if ($i === $j) continue;
    $aggregate[$i][$j] = $dominC[$i][$j] && $dominD[$i][$j] ? 1 : 0;
  }
}

// Hitung skor dominasi
$skor = [];
foreach ($aggregate as $i => $row) {
  $skor[$i] = array_sum($row);
}
arsort($skor); // urut descending

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
    .fade-in {
      animation: fadeIn 0.5s ease-out forwards;
      opacity: 0;
      transform: translateY(10px);
    }
    @keyframes fadeIn {
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">
<?php include '../layouts/splash.php'; include '../layouts/sidebar.php'; ?>
<div class="ml-64 flex-1">
  <?php include '../layouts/topbar.php'; ?>
  <main class="px-6 py-6 fade-in">
    <h1 class="text-2xl font-bold mb-4"><?= $pageTitle ?></h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Semakin tinggi skor dominasi, maka semakin tinggi rankingnya.</p>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center border border-gray-300 dark:border-gray-600">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th class="px-4 py-2">🏅 Ranking</th>
            <th class="px-4 py-2">Alternatif</th>
            <th class="px-4 py-2">Skor Dominasi</th>
          </tr>
        </thead>
        <tbody>
          <?php $rank = 1; foreach ($skor as $id => $s): ?>
            <tr class="border-t border-gray-200 dark:border-gray-700 <?= $rank === 1 ? 'bg-yellow-50 dark:bg-yellow-900' : '' ?>">
              <td class="px-4 py-2 font-semibold">
                <?= $rank === 1 ? '👑' : '' ?> <?= $rank++ ?>
              </td>
              <td class="px-4 py-2"><?= htmlspecialchars($altNames[$id]) ?></td>
              <td class="px-4 py-2"><?= number_format($s, 0) ?></td>
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
