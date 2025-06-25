<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "📉 Langkah 3: Discordance Index";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT id, kode, nama, bobot FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Ambil nilai asli dan hitung V_ij
$nilai = [];
$kuadrat = [];
$akar = [];
$V = [];

foreach ($kriteria as $k) {
  $idk = $k['id'];
  $kuadrat[$idk] = 0;
  $stmt = $conn->prepare("SELECT id_alternatif, nilai FROM nilai WHERE id_kriteria = ?");
  $stmt->execute([$idk]);

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $nilai[$row['id_alternatif']][$idk] = floatval($row['nilai']);
    $kuadrat[$idk] += pow(floatval($row['nilai']), 2);
  }

  $akar[$idk] = sqrt($kuadrat[$idk]);
}

// Hitung V_ij
foreach ($alternatifs as $alt) {
  $idAlt = $alt['id'];
  foreach ($kriteria as $k) {
    $idk = $k['id'];
    $xij = $nilai[$idAlt][$idk] ?? 0;
    $rij = $akar[$idk] > 0 ? $xij / $akar[$idk] : 0;
    $V[$idAlt][$idk] = $rij * $k['bobot'];
  }
}

// Fungsi discordance
function getDiscordanceSet($i, $j, $V, $kriteria) {
  $set = [];
  foreach ($kriteria as $k) {
    $vik = $V[$i][$k['id']] ?? 0;
    $vjk = $V[$j][$k['id']] ?? 0;
    if ($vik < $vjk) {
      $set[] = "C" . $k['kode'];
    }
  }
  return $set;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?> | <?= htmlspecialchars($pengaturan['nama_aplikasi'] ?? 'SPK ELECTRE') ?></title>
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
    .set-box {
      background-color: #ffe4e6;
      padding: 4px 8px;
      border-radius: 6px;
      font-size: 0.85rem;
      font-family: monospace;
    }
    .dark .set-box {
      background-color: #7f1d1d;
      color: #fef2f2;
    }
    .step-box {
      background-color: #fff1f2;
      border: 1px solid #fecdd3;
      border-radius: 6px;
      padding: 1rem;
    }
    .dark .step-box {
      background-color: #7f1d1d;
      border-color: #fca5a5;
      color: white;
    }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">
<?php include '../layouts/splash.php'; ?>
<?php include '../layouts/sidebar.php'; ?>

<div class="ml-64 flex-1 flex flex-col">
  <?php include '../layouts/topbar.php'; ?>
  <main class="flex-1 px-6 py-6 fade-in">
    <h1 class="text-2xl font-bold mb-2"><?= $pageTitle ?></h1>
    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 leading-relaxed">
      Tahap ini menentukan <strong>Himpunan Discordance</strong> yaitu kriteria di mana alternatif i lebih buruk daripada j:<br>
      <code>D<sub>ij</sub> = { k | V<sub>ik</sub> &lt; V<sub>jk</sub> }</code>
    </p>

    <div class="text-sm text-gray-700 dark:text-gray-200 mb-8">
      <h2 class="text-lg font-semibold mb-2">🧮 Langkah Perhitungan Manual:</h2>
      <div class="space-y-4">
        <?php foreach ($alternatifs as $a): foreach ($alternatifs as $b): if ($a['id'] === $b['id']) continue;
          echo "<div class='step-box'>";
          echo "<strong>🔸 {$a['nama']} vs {$b['nama']}:</strong><br>";
          $set = [];
          foreach ($kriteria as $k) {
            $vik = $V[$a['id']][$k['id']] ?? 0;
            $vjk = $V[$b['id']][$k['id']] ?? 0;
            if ($vik < $vjk) {
              echo "C{$k['kode']}: {$vik} < {$vjk} → ❌ Masuk<br>";
              $set[] = "C{$k['kode']}";
            } else {
              echo "C{$k['kode']}: {$vik} ≥ {$vjk} → ✅ Lewat<br>";
            }
          }
          echo "<div class='mt-2'>✔️ Himpunan Discordance: <span class='set-box'>{ " . implode(', ', $set) . " }</span></div>";
          echo "</div>";
        endforeach; endforeach; ?>
      </div>
    </div>

    <h2 class="text-lg font-semibold mb-3">📋 Tabel Himpunan Discordance (D<sub>ij</sub>)</h2>
    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center border">
        <thead class="bg-rose-100 dark:bg-rose-900 text-rose-700 dark:text-rose-300">
          <tr>
            <th class="px-4 py-2 text-left">i → j</th>
            <?php foreach ($alternatifs as $b): ?>
              <th class="px-4 py-2"><?= htmlspecialchars($b['nama']) ?></th>
            <?php endforeach ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alternatifs as $a): ?>
            <tr class="border-t border-gray-200 dark:border-gray-700">
              <th class="px-4 py-2 text-left bg-rose-50 dark:bg-rose-900"><?= htmlspecialchars($a['nama']) ?></th>
              <?php foreach ($alternatifs as $b): ?>
                <td class="px-2 py-2">
                  <?php
                  if ($a['id'] === $b['id']) {
                    echo '<span class="text-gray-400">–</span>';
                  } else {
                    $set = getDiscordanceSet($a['id'], $b['id'], $V, $kriteria);
                    echo '<div class="set-box">{ ' . implode(', ', $set) . ' }</div>';
                  }
                  ?>
                </td>
              <?php endforeach ?>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <div class="mt-6 text-xs text-gray-500 dark:text-gray-400 max-w-3xl leading-relaxed">
      <strong>Keterangan:</strong>
      <ul class="list-disc pl-5 mt-1 space-y-1">
        <li>Jika <code>V<sub>ik</sub> &lt; V<sub>jk</sub></code>, maka kriteria k masuk ke <strong>D<sub>ij</sub></strong></li>
        <li>Digunakan untuk menghitung nilai indeks Discordance</li>
        <li>Simbol "–" artinya i = j (tidak dibandingkan)</li>
      </ul>
    </div>
  </main>
  <?php include '../layouts/footer.php'; ?>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
