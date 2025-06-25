<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "❌ Matriks Dominan Discordance";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT id, kode FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Ambil nilai xij
$nilai = [];
foreach ($conn->query("SELECT id_alternatif, id_kriteria, nilai FROM nilai") as $row) {
  $nilai[$row['id_alternatif']][$row['id_kriteria']] = floatval($row['nilai']);
}

// Hitung Discordance + penjabaran
$discordance = [];
$penjabaran = [];
foreach ($alternatifs as $a) {
  foreach ($alternatifs as $b) {
    if ($a['id'] === $b['id']) continue;
    $maxDiff = 0;
    $maxAll = 0;
    $steps = [];
    foreach ($kriteria as $k) {
      $ka = $nilai[$a['id']][$k['id']] ?? 0;
      $kb = $nilai[$b['id']][$k['id']] ?? 0;
      $selisih = abs($ka - $kb);
      $maxAll = max($maxAll, $selisih);
      if ($ka < $kb) {
        $maxDiff = max($maxDiff, $selisih);
        $steps[] = "✘ {$k['kode']}: {$ka} < {$kb} → pembilang = " . number_format($selisih, 2);
      } else {
        $steps[] = "✔ {$k['kode']}: {$ka} ≥ {$kb} → diabaikan";
      }
    }
    $val = $maxAll == 0 ? 0 : $maxDiff / $maxAll;
    $discordance[$a['id']][$b['id']] = $val;
    $penjabaran[$a['id']][$b['id']] = $steps;
  }
}

// Hitung threshold
$sum = 0; $count = 0;
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
    .tooltip {
      position: relative; display: inline-block; cursor: help;
    }
    .tooltip .tooltip-text {
      visibility: hidden; opacity: 0;
      position: absolute; z-index: 20;
      background-color: #1f2937; color: white;
      padding: 6px 10px; border-radius: 4px;
      bottom: 125%; left: 50%; transform: translateX(-50%);
      font-size: 0.75rem; white-space: pre-line;
      transition: opacity 0.3s ease;
    }
    .tooltip:hover .tooltip-text {
      visibility: visible; opacity: 1;
    }
    .manual-box {
      @apply bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded p-4 mb-4 shadow;
    }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">

<?php include '../layouts/splash.php'; ?>
<?php include '../layouts/sidebar.php'; ?>
<div class="ml-64 flex flex-col">
  <?php include '../layouts/topbar.php'; ?>

  <main class="px-6 py-6 fade-in">
    <h1 class="text-2xl font-bold mb-4"><?= $pageTitle ?></h1>

    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
      Matriks ini mengukur penolakan dominasi alternatif <strong>i terhadap j</strong> jika i lebih buruk dari j pada kriteria tertentu.<br>
      Rumus: <code>D<sub>ij</sub> = max(|x<sub>ik</sub> - x<sub>jk</sub>| jika x<sub>ik</sub> &lt; x<sub>jk</sub>) / max(|x<sub>ik</sub> - x<sub>jk</sub>|)</code><br>
      Threshold: <strong><?= number_format($threshold, 4) ?></strong> → <code>D<sub>ij</sub> ≥ threshold</code> = <span class="text-red-600 font-semibold">1</span>, else <span class="text-gray-500 font-semibold">0</span>
    </p>

    <h2 class="text-lg font-semibold mb-2">📘 Penjabaran Manual</h2>
    <?php foreach ($alternatifs as $a): foreach ($alternatifs as $b):
      if ($a['id'] === $b['id']) continue;
      echo "<div class='manual-box'>";
      echo "<strong>{$a['nama']} → {$b['nama']}</strong><br><ul class='list-disc pl-5 mt-1 text-sm'>";
      foreach ($penjabaran[$a['id']][$b['id']] as $p) {
        echo "<li>$p</li>";
      }
      echo "</ul>";
      echo "<div class='mt-2 font-semibold text-rose-600'>D<sub>{$a['id']}{$b['id']}</sub> = " . number_format($discordance[$a['id']][$b['id']], 4) . "</div>";
      echo "</div>";
    endforeach; endforeach; ?>

    <h2 class="text-lg font-semibold mt-6 mb-2">📊 Matriks Dominan Discordance</h2>
    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center border border-gray-300 dark:border-gray-700">
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
            <tr class="border-t border-gray-200 dark:border-gray-600">
              <th class="text-left px-4 py-3 bg-rose-50 dark:bg-rose-900"><?= htmlspecialchars($a['nama']) ?></th>
              <?php foreach ($alternatifs as $b): ?>
                <td class="px-4 py-3">
                  <?php
                  if ($a['id'] === $b['id']) {
                    echo '<span class="text-gray-400">–</span>';
                  } else {
                    $v = $discordance[$a['id']][$b['id']];
                    $result = $v >= $threshold ? 1 : 0;
                    echo "<div class='tooltip'>$result
                            <div class='tooltip-text'>D<sub>{$a['id']}{$b['id']}</sub>: " . number_format($v, 4) . "\n" . implode("\n", $penjabaran[$a['id']][$b['id']]) . "</div>
                          </div>";
                  }
                  ?>
                </td>
              <?php endforeach ?>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <div class="mt-6 text-sm text-gray-600 dark:text-gray-400 leading-relaxed max-w-2xl">
      <h3 class="font-semibold mb-1 text-gray-800 dark:text-white">📌 Interpretasi:</h3>
      <ul class="list-disc pl-5 space-y-1">
        <li><strong>D<sub>ij</sub> = 1</strong>: i sangat lemah terhadap j (penolakan dominasi tinggi)</li>
        <li><strong>D<sub>ij</sub> = 0</strong>: i tidak lebih lemah secara signifikan dari j</li>
        <li>Tooltip pada nilai menampilkan perhitungan lengkap dan selisih kriteria</li>
      </ul>
    </div>
  </main>

  <?php include '../layouts/footer.php'; ?>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
