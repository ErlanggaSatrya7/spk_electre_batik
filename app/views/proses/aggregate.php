<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "💠 Matriks Agregat (Langkah 6)";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$altIDs = array_column($alternatifs, 'id');
$altNames = array_column($alternatifs, 'nama', 'id');
$kriteria = $conn->query("SELECT id, kode, bobot FROM kriteria")->fetchAll(PDO::FETCH_ASSOC);
$bobot = array_column($kriteria, 'bobot', 'id');

// Ambil nilai
$nilai = [];
foreach ($conn->query("SELECT * FROM nilai") as $row) {
  $nilai[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
}

// Matriks Concordance
$concordance = [];
$logConcordance = [];
foreach ($altIDs as $i) {
  foreach ($altIDs as $j) {
    if ($i === $j) continue;
    $total = 0;
    $detail = [];
    foreach ($kriteria as $k) {
      $xik = $nilai[$i][$k['id']] ?? 0;
      $xjk = $nilai[$j][$k['id']] ?? 0;
      if ($xik >= $xjk) {
        $total += $k['bobot'];
        $detail[] = "✔ {$k['kode']}: {$xik} ≥ {$xjk} → +{$k['bobot']}";
      } else {
        $detail[] = "✘ {$k['kode']}: {$xik} < {$xjk} → +0";
      }
    }
    $concordance[$i][$j] = $total;
    $logConcordance[$i][$j] = $detail;
  }
}
$thresholdC = array_sum(array_map('array_sum', $concordance)) / (count($altIDs) * (count($altIDs) - 1));

// Matriks Discordance
$discordance = [];
$logDiscordance = [];
foreach ($altIDs as $i) {
  foreach ($altIDs as $j) {
    if ($i === $j) continue;
    $maxDiff = 0;
    $maxAll = 0;
    $detail = [];
    foreach ($kriteria as $k) {
      $xik = $nilai[$i][$k['id']] ?? 0;
      $xjk = $nilai[$j][$k['id']] ?? 0;
      $selisih = abs($xik - $xjk);
      $maxAll = max($maxAll, $selisih);
      if ($xik < $xjk) {
        $maxDiff = max($maxDiff, $selisih);
        $detail[] = "❌ {$k['kode']}: {$xik} < {$xjk} → maxDiff update: {$selisih}";
      } else {
        $detail[] = "✅ {$k['kode']}: {$xik} ≥ {$xjk}";
      }
    }
    $dVal = $maxAll > 0 ? $maxDiff / $maxAll : 0;
    $discordance[$i][$j] = $dVal;
    $logDiscordance[$i][$j] = $detail;
  }
}
$thresholdD = array_sum(array_map('array_sum', $discordance)) / (count($altIDs) * (count($altIDs) - 1));

// Dominan Matriks
$dominC = $dominD = $aggregate = [];
foreach ($altIDs as $i) {
  foreach ($altIDs as $j) {
    if ($i === $j) {
      $aggregate[$i][$j] = '-';
      continue;
    }
    $dominC[$i][$j] = $concordance[$i][$j] >= $thresholdC ? 1 : 0;
    $dominD[$i][$j] = $discordance[$i][$j] >= $thresholdD ? 1 : 0;
    $aggregate[$i][$j] = $dominC[$i][$j] && $dominD[$i][$j] ? 1 : 0;
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
  <script>
    tailwind.config = { darkMode: 'class' };
    if (localStorage.getItem("theme") === "dark") {
      document.documentElement.classList.add("dark");
    }
  </script>
  <style>
    .fade-in { animation: fadeIn .5s ease-out forwards; opacity: 0; transform: translateY(10px); }
    @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
    .tooltip {
      position: relative; display: inline-block; cursor: help;
    }
    .tooltip .tooltip-text {
      visibility: hidden;
      opacity: 0;
      position: absolute;
      background-color: #1f2937;
      color: white;
      padding: 6px 10px;
      border-radius: 4px;
      font-size: 0.75rem;
      bottom: 125%;
      left: 50%;
      transform: translateX(-50%);
      transition: opacity 0.3s ease;
      white-space: pre-line;
      z-index: 20;
    }
    .tooltip:hover .tooltip-text { visibility: visible; opacity: 1; }
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
      Matriks Agregat adalah hasil <code>Dominan Concordance ∩ Dominan Discordance</code>. Nilai <code>1</code> berarti alternatif i mengalahkan j.
      <p class="mt-2">
        ✅ Threshold Concordance: <strong><?= number_format($thresholdC, 4) ?></strong> &nbsp;&nbsp;
        ❌ Threshold Discordance: <strong><?= number_format($thresholdD, 4) ?></strong>
      </p>
    </div>

    <!-- TABEL -->
    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center border border-gray-300 dark:border-gray-600">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th class="px-4 py-2 bg-gray-50 dark:bg-gray-700">i → j</th>
            <?php foreach ($altIDs as $j): ?>
              <th class="px-4 py-2"><?= htmlspecialchars($altNames[$j]) ?></th>
            <?php endforeach ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($altIDs as $i): ?>
            <tr class="border-t border-gray-200 dark:border-gray-700">
              <th class="px-4 py-2 text-left bg-gray-50 dark:bg-gray-700"><?= htmlspecialchars($altNames[$i]) ?></th>
              <?php foreach ($altIDs as $j): ?>
                <td class="px-4 py-2">
                  <?php
                  if ($i === $j) echo '<span class="text-gray-400">–</span>';
                  else {
                    $tooltip = "C = " . number_format($concordance[$i][$j], 4) . "\nD = " . number_format($discordance[$i][$j], 4);
                    echo "<div class='tooltip'>{$aggregate[$i][$j]}<div class='tooltip-text'>{$tooltip}</div></div>";
                  }
                  ?>
                </td>
              <?php endforeach ?>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <!-- RINCIAN -->
    <div class="mt-8 text-sm text-gray-500 dark:text-gray-400">
      <h3 class="font-semibold mb-2 text-gray-800 dark:text-white">📘 Perhitungan Manual:</h3>
      <?php foreach ($altIDs as $i): foreach ($altIDs as $j):
        if ($i === $j) continue;
        echo "<div class='manual-box'>";
        echo "<strong>{$altNames[$i]} → {$altNames[$j]}</strong>";
        echo "<div class='mt-1 mb-1 text-xs text-gray-500 dark:text-gray-300'>C<sub>{$i}{$j}</sub> = " . number_format($concordance[$i][$j], 4) . " → " . ($dominC[$i][$j] ? "✅" : "❌") . " | ";
        echo "D<sub>{$i}{$j}</sub> = " . number_format($discordance[$i][$j], 4) . " → " . ($dominD[$i][$j] ? "✅" : "❌") . "</div>";
        echo "<ul class='list-disc pl-5 text-xs leading-relaxed'>";
        foreach ($logConcordance[$i][$j] as $line) echo "<li class='text-green-600'>$line</li>";
        foreach ($logDiscordance[$i][$j] as $line) echo "<li class='text-red-600'>$line</li>";
        echo "</ul>";
        echo "<div class='mt-2 text-indigo-600 font-semibold'>Agregat E<sub>{$i}{$j}</sub> = " . $aggregate[$i][$j] . "</div>";
        echo "</div>";
      endforeach; endforeach ?>
    </div>
  </main>
  <?php include '../layouts/footer.php'; ?>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
