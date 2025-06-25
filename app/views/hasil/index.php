<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "Hasil Ranking";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

// Ambil data alternatif
$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$altIDs = array_column($alternatifs, 'id');
$altNames = array_column($alternatifs, 'nama', 'id');

// Ambil nilai
$nilai = [];
foreach ($conn->query("SELECT * FROM nilai") as $n) {
  $nilai[$n['id_alternatif']][$n['id_kriteria']] = floatval($n['nilai']);
}

// Bobot kriteria
$kriteria = $conn->query("SELECT id, bobot FROM kriteria")->fetchAll(PDO::FETCH_ASSOC);
$bobot = array_column($kriteria, 'bobot', 'id');

// Matriks Concordance & Discordance
$concordance = $discordance = [];
foreach ($altIDs as $i) {
  foreach ($altIDs as $j) {
    if ($i === $j) continue;
    // Concordance
    $cij = 0;
    foreach ($bobot as $kid => $b) {
      if (($nilai[$i][$kid] ?? 0) >= ($nilai[$j][$kid] ?? 0)) $cij += $b;
    }
    $concordance[$i][$j] = $cij;

    // Discordance
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

$thresholdC = array_sum(array_map('array_sum', $concordance)) / (count($altIDs) * (count($altIDs) - 1));
$thresholdD = array_sum(array_map('array_sum', $discordance)) / (count($altIDs) * (count($altIDs) - 1));

// Dominan matriks dan agregat
$skor = [];
foreach ($altIDs as $i) {
  $skor[$i] = 0;
  foreach ($altIDs as $j) {
    if ($i === $j) continue;
    $isDominantC = $concordance[$i][$j] >= $thresholdC;
    $isDominantD = $discordance[$i][$j] >= $thresholdD;
    if ($isDominantC && $isDominantD) $skor[$i]++;
  }
}
arsort($skor);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>📊 <?= $pageTitle ?> | <?= $pengaturan['nama_aplikasi'] ?? 'SPK Batik' ?></title>
  <link rel="icon" href="../../../assets/logo/<?= $logo ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .fade-in { animation: fadeIn .5s ease-out forwards; opacity: 0; transform: translateY(10px); }
    @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">
<?php include '../layouts/splash.php'; include '../layouts/sidebar.php'; ?>
<div class="ml-64 flex flex-col">
  <?php include '../layouts/topbar.php'; ?>
  <main class="flex-1 px-6 py-6 fade-in">
    <h1 class="text-2xl font-bold mb-4">📊 Hasil Akhir Perangkingan</h1>

    <div class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
      <p>Hasil akhir dari proses metode <strong>ELECTRE</strong>. Alternatif terbaik ditentukan berdasarkan jumlah dominasi yang memenuhi threshold <em>Concordance</em> dan <em>Discordance</em>.</p>
      <p class="mt-2">🔹 Threshold Concordance: <strong><?= number_format($thresholdC, 4) ?></strong><br>
         🔻 Threshold Discordance: <strong><?= number_format($thresholdD, 4) ?></strong></p>
    </div>

    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow mb-8">
      <table class="min-w-full text-sm text-center border border-gray-300 dark:border-gray-600">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th class="px-4 py-2">🏅 Ranking</th>
            <th class="px-4 py-2">Alternatif</th>
            <th class="px-4 py-2">Skor Dominasi</th>
          </tr>
        </thead>
        <tbody>
          <?php $rank = 1; foreach ($skor as $id => $score): ?>
            <tr class="border-t <?= $rank === 1 ? 'bg-yellow-100 dark:bg-yellow-900 font-semibold' : '' ?>">
              <td class="px-4 py-2"><?= $rank === 1 ? '👑' : $rank ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($altNames[$id]) ?></td>
              <td class="px-4 py-2"><?= $score ?></td>
              <?php $rank++; ?>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <div class="text-sm text-gray-500 dark:text-gray-400">
      <h3 class="font-semibold mb-2 text-gray-700 dark:text-white">📝 Keterangan:</h3>
      <ul class="list-disc pl-5 space-y-1">
        <li><strong>Ranking 1</strong> adalah alternatif terbaik menurut dominasi agregat.</li>
        <li>Skor dominasi didapat dari banyaknya alternatif lain yang didominasi oleh tiap alternatif.</li>
        <li>Semakin tinggi skor, semakin baik performa alternatif terhadap kriteria.</li>
      </ul>
    </div>
  </main>
  <?php include '../layouts/footer.php'; ?>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
