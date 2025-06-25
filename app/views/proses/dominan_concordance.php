<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "✅ Matriks Dominan Concordance";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT id, kode, bobot FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Ambil nilai xij
$nilai = [];
foreach ($conn->query("SELECT id_alternatif, id_kriteria, nilai FROM nilai") as $row) {
  $nilai[$row['id_alternatif']][$row['id_kriteria']] = floatval($row['nilai']);
}

// Hitung Cij + detail perhitungan
$concordance = [];
$penjabaran = [];
foreach ($alternatifs as $a) {
  foreach ($alternatifs as $b) {
    if ($a['id'] === $b['id']) continue;
    $total = 0;
    $steps = [];
    foreach ($kriteria as $k) {
      $xa = $nilai[$a['id']][$k['id']] ?? 0;
      $xb = $nilai[$b['id']][$k['id']] ?? 0;
      if ($xa >= $xb) {
        $total += $k['bobot'];
        $steps[] = "✔ {$k['kode']}: {$xa} ≥ {$xb} → +{$k['bobot']}";
      } else {
        $steps[] = "✘ {$k['kode']}: {$xa} < {$xb} → +0";
      }
    }
    $concordance[$a['id']][$b['id']] = $total;
    $penjabaran[$a['id']][$b['id']] = $steps;
  }
}

// Hitung threshold
$sum = 0; $count = 0;
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
    function toggleTheme() {
      const html = document.documentElement;
      const newTheme = html.classList.contains("dark") ? "light" : "dark";
      html.classList.toggle("dark");
      localStorage.setItem("theme", newTheme);
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
      background: #1f2937; color: #fff;
      text-align: left; padding: 8px; border-radius: 6px;
      font-size: 0.75rem; white-space: pre-line;
      position: absolute; z-index: 10; bottom: 125%; left: 50%;
      transform: translateX(-50%); transition: opacity 0.3s;
      min-width: 200px;
    }
    .tooltip:hover .tooltip-text { visibility: visible; opacity: 1; }
    .manual-box {
      @apply bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded p-4 mb-4 shadow;
    }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">

<?php include '../layouts/splash.php'; include '../layouts/sidebar.php'; ?>
<div class="ml-64 flex flex-col">
  <?php include '../layouts/topbar.php'; ?>

  <div class="flex justify-end px-6 pt-4">
    <button onclick="toggleTheme()" class="text-sm bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1 rounded hover:shadow">
      🌗 Ganti Tema
    </button>
  </div>

  <main class="px-6 py-6 fade-in">
    <h1 class="text-2xl font-bold mb-4"><?= $pageTitle ?></h1>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
      Matriks Dominan Concordance menunjukkan apakah alternatif <strong>i</strong> mendominasi <strong>j</strong> berdasarkan bobot kriteria yang nilainya lebih besar atau sama.<br>
      Rumus: <code>C<sub>ij</sub> = ∑ w<sub>k</sub> jika x<sub>ik</sub> ≥ x<sub>jk</sub></code><br>
      <strong>Threshold rata-rata:</strong> <?= number_format($threshold, 4) ?> → Jika <code>C<sub>ij</sub> ≥ threshold</code> maka hasil = <span class="text-green-600 font-semibold">1</span>, else <span class="text-red-600 font-semibold">0</span>.
    </p>

    <h2 class="text-lg font-semibold mb-3">📘 Penjabaran Manual:</h2>
    <?php foreach ($alternatifs as $a): foreach ($alternatifs as $b):
      if ($a['id'] === $b['id']) continue;
      echo "<div class='manual-box'>";
      echo "<strong>{$a['nama']} → {$b['nama']}</strong><br><ul class='list-disc pl-5 mt-1 text-sm'>";
      foreach ($penjabaran[$a['id']][$b['id']] as $item) {
        echo "<li>$item</li>";
      }
      echo "</ul>";
      echo "<div class='mt-2 font-semibold text-indigo-600'>C<sub>{$a['id']}{$b['id']}</sub> = " . number_format($concordance[$a['id']][$b['id']], 4) . "</div>";
      echo "</div>";
    endforeach; endforeach; ?>

    <h2 class="text-lg font-semibold mb-2 mt-8">📊 Matriks Dominan Concordance</h2>
    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center border border-gray-300 dark:border-gray-700">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th class="px-4 py-3 text-left">i → j</th>
            <?php foreach ($alternatifs as $b): ?>
              <th class="px-4 py-3"><?= htmlspecialchars($b['nama']) ?></th>
            <?php endforeach ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alternatifs as $a): ?>
            <tr class="border-t border-gray-200 dark:border-gray-600">
              <th class="px-4 py-2 bg-indigo-50 dark:bg-indigo-900 text-left"><?= htmlspecialchars($a['nama']) ?></th>
              <?php foreach ($alternatifs as $b): ?>
                <td class="px-3 py-2">
                  <?php
                    if ($a['id'] === $b['id']) {
                      echo '<span class="text-gray-400">–</span>';
                    } else {
                      $cij = $concordance[$a['id']][$b['id']];
                      $val = $cij >= $threshold ? 1 : 0;
                      echo "<div class='tooltip'>$val<div class='tooltip-text'>" . implode("\n", $penjabaran[$a['id']][$b['id']]) . "\nTotal = " . number_format($cij, 4) . "</div></div>";
                    }
                  ?>
                </td>
              <?php endforeach ?>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <div class="mt-6 text-sm text-gray-500 dark:text-gray-400 max-w-3xl">
      <strong>Catatan:</strong>
      <ul class="list-disc pl-5 mt-1 space-y-1">
        <li><code>1</code> = i mendominasi j, jika nilai C<sub>ij</sub> ≥ threshold</li>
        <li><code>0</code> = dominasi i tidak cukup kuat terhadap j</li>
        <li>Tooltip pada tabel menunjukkan detail kontribusi bobot tiap kriteria</li>
      </ul>
    </div>
  </main>

  <?php include '../layouts/footer.php'; ?>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
