<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "🏆 Perangkingan Alternatif";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$altIDs = array_column($alternatifs, 'id');
$altNames = array_column($alternatifs, 'nama', 'id');
$kriteria = $conn->query("SELECT id, bobot FROM kriteria")->fetchAll(PDO::FETCH_ASSOC);
$bobot = array_column($kriteria, 'bobot', 'id');

// Nilai
$nilai = [];
foreach ($conn->query("SELECT id_alternatif, id_kriteria, nilai FROM nilai") as $n) {
  $nilai[$n['id_alternatif']][$n['id_kriteria']] = floatval($n['nilai']);
}

// Matriks Concordance
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
$thresholdC = array_sum(array_map('array_sum', $concordance)) / (count($altIDs) * (count($altIDs) - 1));
$dominC = [];
foreach ($concordance as $i => $row) {
  foreach ($row as $j => $val) {
    $dominC[$i][$j] = $val >= $thresholdC ? 1 : 0;
  }
}

// Matriks Discordance
$discordance = [];
foreach ($altIDs as $i) {
  foreach ($altIDs as $j) {
    if ($i === $j) continue;
    $maxAll = $maxDiff = 0;
    foreach ($bobot as $kid => $b) {
      $a = $nilai[$i][$kid] ?? 0;
      $bVal = $nilai[$j][$kid] ?? 0;
      $diff = abs($a - $bVal);
      $maxAll = max($maxAll, $diff);
      if ($a < $bVal) $maxDiff = max($maxDiff, $diff);
    }
    $discordance[$i][$j] = $maxAll ? $maxDiff / $maxAll : 0;
  }
}
$thresholdD = array_sum(array_map('array_sum', $discordance)) / (count($altIDs) * (count($altIDs) - 1));
$dominD = [];
foreach ($discordance as $i => $row) {
  foreach ($row as $j => $val) {
    $dominD[$i][$j] = $val >= $thresholdD ? 1 : 0;
  }
}

// Matriks Agregat
$aggregate = [];
foreach ($altIDs as $i) {
  foreach ($altIDs as $j) {
    $aggregate[$i][$j] = ($i === $j) ? '-' : ($dominC[$i][$j] && $dominD[$i][$j] ? 1 : 0);
  }
}

// Skor Dominasi
$skor = [];
foreach ($aggregate as $i => $row) {
  $skor[$i] = is_array($row) ? array_sum(array_filter($row, fn($v) => $v === 1)) : 0;
}
arsort($skor);
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
    .manual-box {
      @apply bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded p-4 mb-4 shadow;
    }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">
<?php include '../layouts/splash.php'; include '../layouts/sidebar.php'; ?>
<div class="ml-64 flex-1">
  <?php include '../layouts/topbar.php'; ?>
  <main class="px-6 py-6 fade-in">
    <h1 class="text-2xl font-bold mb-4"><?= $pageTitle ?></h1>

    <div class="text-sm text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
      <p>Perangkingan berdasarkan skor dominasi dari hasil irisan dominan Concordance dan Discordance.</p>
      <p class="mt-2">🧮 Threshold C: <strong><?= number_format($thresholdC, 4) ?></strong> |
         Threshold D: <strong><?= number_format($thresholdD, 4) ?></strong></p>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow mb-6">
      <table class="min-w-full text-sm text-center border border-gray-300 dark:border-gray-600">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th class="px-4 py-2">Ranking</th>
            <th class="px-4 py-2">Alternatif</th>
            <th class="px-4 py-2">Skor Dominasi</th>
          </tr>
        </thead>
        <tbody>
          <?php $rank = 1; foreach ($skor as $id => $val): ?>
            <tr class="border-t <?= $rank === 1 ? 'bg-yellow-100 dark:bg-yellow-900 font-semibold' : '' ?>">
              <td class="px-4 py-2"><?= $rank === 1 ? '👑' : $rank ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($altNames[$id]) ?></td>
              <td class="px-4 py-2"><?= $val ?></td>
              <?php $rank++; ?>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <!-- Perhitungan Manual -->
    <div class="text-sm text-gray-600 dark:text-gray-300 mb-10">
      <h2 class="text-lg font-semibold mb-2">📘 Perhitungan Manual (Dominasi per Alternatif):</h2>
      <?php foreach ($altIDs as $i): ?>
        <div class="manual-box">
          <strong><?= htmlspecialchars($altNames[$i]) ?> mendominasi:</strong>
          <ul class="list-disc pl-5 mt-2">
            <?php
              $total = 0;
              foreach ($altIDs as $j) {
                if ($i === $j) continue;
                if ($aggregate[$i][$j] === 1) {
                  echo "<li>{$altNames[$j]}</li>";
                  $total++;
                }
              }
              if ($total === 0) echo "<li><em>Tidak ada</em></li>";
            ?>
          </ul>
          <p class="mt-2 font-semibold text-indigo-600">Skor Dominasi: <?= $skor[$i] ?? 0 ?></p>
        </div>
      <?php endforeach ?>
    </div>

    <div class="text-sm text-gray-500 dark:text-gray-400">
      <h3 class="font-semibold mb-1 text-gray-700 dark:text-white">🔁 Langkah Penentuan Ranking:</h3>
      <ol class="list-decimal list-inside space-y-1">
        <li>Hitung Matriks Concordance dan Discordance</li>
        <li>Tentukan threshold: rata-rata nilai C<sub>ij</sub> dan D<sub>ij</sub></li>
        <li>Bentuk Matriks Dominan Concordance dan Discordance</li>
        <li>Lakukan irisan → Matriks Agregat</li>
        <li>Hitung skor dominasi per alternatif (jumlah 1 di baris)</li>
        <li>Urutkan skor dari tinggi ke rendah sebagai peringkat</li>
      </ol>
    </div>
  </main>
  <?php include '../layouts/footer.php'; ?>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
