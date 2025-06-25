<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "🔶 Matriks Discordance";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT id, kode, nama FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$nilai = [];
foreach ($conn->query("SELECT id_alternatif, id_kriteria, nilai FROM nilai") as $row) {
  $nilai[$row['id_alternatif']][$row['id_kriteria']] = floatval($row['nilai']);
}

function hitungDiscordance($a, $b, $nilai, $kriteria, &$penjabaran) {
  $maxDiff = 0;
  $maxAll = 0;
  $penjabaran = [];

  foreach ($kriteria as $k) {
    $xik = $nilai[$a][$k['id']] ?? 0;
    $xjk = $nilai[$b][$k['id']] ?? 0;
    $selisih = abs($xik - $xjk);
    $maxAll = max($maxAll, $selisih);

    if ($xik < $xjk) {
      $maxDiff = max($maxDiff, $selisih);
      $penjabaran[] = "❌ {$k['kode']}: {$xik} < {$xjk} → pembilang: {$selisih}";
    } else {
      $penjabaran[] = "✅ {$k['kode']}: {$xik} ≥ {$xjk} → hanya penyebut: {$selisih}";
    }
  }

  $result = $maxAll == 0 ? 0 : $maxDiff / $maxAll;
  $penjabaran[] = "➤ maxDiff = {$maxDiff}, maxAll = {$maxAll}";
  $penjabaran[] = "🔶 D = {$maxDiff} / {$maxAll} = " . number_format($result, 4);
  return $result;
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
    .fade-in { animation: fadeIn 0.5s ease-out forwards; opacity: 0; transform: translateY(10px); }
    @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
    .tooltip {
      position: relative;
      display: inline-block;
      cursor: help;
    }
    .tooltip .tooltip-text {
      visibility: hidden;
      background-color: #1f2937;
      color: #fff;
      text-align: left;
      padding: 8px;
      border-radius: 4px;
      position: absolute;
      z-index: 10;
      bottom: 130%; left: 50%;
      transform: translateX(-50%);
      font-size: 0.75rem;
      white-space: pre-line;
      opacity: 0;
      transition: opacity 0.3s;
      min-width: 200px;
    }
    .tooltip:hover .tooltip-text {
      visibility: visible;
      opacity: 1;
    }
    .manual-box {
      @apply bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded p-4 mb-4 shadow;
    }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">
<?php include '../layouts/splash.php'; ?>
<?php include '../layouts/sidebar.php'; ?>
<div class="ml-64 flex-1 flex flex-col">
  <?php include '../layouts/topbar.php'; ?>

  <main class="flex-1 px-6 py-6 fade-in">
    <h1 class="text-2xl font-bold mb-4"><?= $pageTitle ?></h1>

    <p class="text-sm text-gray-500 dark:text-gray-300 mb-6 leading-relaxed">
      Matriks Discordance <strong>D<sub>ij</sub></strong> menunjukkan seberapa besar alternatif i <u>lebih buruk</u> daripada j.<br>
      Rumus:<br>
      <code>D<sub>ij</sub> = max{|x<sub>ik</sub> - x<sub>jk</sub>| jika x<sub>ik</sub> &lt; x<sub>jk</sub>} / max{|x<sub>ik</sub> - x<sub>jk</sub>|}</code>
    </p>

    <div class="mb-8">
      <h2 class="text-lg font-semibold mb-2">📘 Perhitungan Manual Lengkap:</h2>
      <?php foreach ($alternatifs as $a): foreach ($alternatifs as $b):
        if ($a['id'] === $b['id']) continue;
        $penjabaran = [];
        $dij = hitungDiscordance($a['id'], $b['id'], $nilai, $kriteria, $penjabaran);
        echo "<div class='manual-box'>";
        echo "<strong>{$a['nama']} → {$b['nama']}</strong><br>";
        echo "<ul class='list-disc pl-5 text-sm mt-1'>";
        foreach ($penjabaran as $p) echo "<li>$p</li>";
        echo "</ul>";
        echo "<div class='mt-2 font-semibold text-rose-600'>D<sub>{$a['id']}{$b['id']}</sub> = " . number_format($dij, 4) . "</div>";
        echo "</div>";
      endforeach; endforeach; ?>
    </div>

    <h2 class="text-lg font-semibold mb-2">📊 Matriks Discordance (D<sub>ij</sub>)</h2>
    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center border border-gray-300 dark:border-gray-600">
        <thead class="bg-rose-100 dark:bg-rose-900 text-rose-700 dark:text-rose-300">
          <tr>
            <th class="px-4 py-2">i → j</th>
            <?php foreach ($alternatifs as $b): ?>
              <th class="px-4 py-2"><?= htmlspecialchars($b['nama']) ?></th>
            <?php endforeach ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alternatifs as $a): ?>
            <tr class="border-t border-gray-200 dark:border-gray-700">
              <th class="px-3 py-2 bg-rose-50 dark:bg-rose-900 text-left"><?= htmlspecialchars($a['nama']) ?></th>
              <?php foreach ($alternatifs as $b): ?>
                <td class="px-3 py-2">
                  <?php
                  if ($a['id'] === $b['id']) {
                    echo '<span class="text-gray-400">—</span>';
                  } else {
                    $penjabaran = [];
                    $dij = hitungDiscordance($a['id'], $b['id'], $nilai, $kriteria, $penjabaran);
                    echo "<div class='tooltip'>" . number_format($dij, 2) .
                      "<div class='tooltip-text'>" . implode("\n", $penjabaran) . "</div></div>";
                  }
                  ?>
                </td>
              <?php endforeach ?>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <div class="mt-6 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
      <strong>Keterangan:</strong>
      <ul class="list-disc pl-5 mt-1 space-y-1">
        <li>Nilai D<sub>ij</sub> besar → i sangat kalah dibanding j dalam kriteria penting</li>
        <li>Hover di nilai tabel menampilkan proses manual lengkap per kriteria</li>
        <li>Digunakan untuk membentuk Matriks Dominan Discordance</li>
      </ul>
    </div>
  </main>

  <?php include '../layouts/footer.php'; ?>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
