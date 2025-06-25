<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "📈 Langkah 3: Concordance Index";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT id, kode, nama, bobot FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Ambil nilai dan hitung normalisasi terbobot V_ij
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

foreach ($alternatifs as $alt) {
  foreach ($kriteria as $k) {
    $x = $nilai[$alt['id']][$k['id']] ?? 0;
    $r = $akar[$k['id']] > 0 ? $x / $akar[$k['id']] : 0;
    $V[$alt['id']][$k['id']] = $r * $k['bobot'];
  }
}

// Fungsi Concordance Set dengan pembulatan
function getConcordanceSet($i, $j, $V, $kriteria) {
  $set = [];
  foreach ($kriteria as $k) {
    $vik = round($V[$i][$k['id']] ?? 0, 6);
    $vjk = round($V[$j][$k['id']] ?? 0, 6);
    if ($vik >= $vjk) {
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
    .set-box {
      background-color: #eef2ff;
      padding: 4px 8px;
      border-radius: 4px;
      font-family: monospace;
      font-size: 0.85rem;
      display: inline-block;
    }
    .step-box {
      background-color: #f9fafb;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    .dark .set-box { background-color: #3730a3; color: white; }
    .dark .step-box { background-color: #1f2937; border-color: #374151; }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">
<?php include '../layouts/splash.php'; ?>
<?php include '../layouts/sidebar.php'; ?>

<div class="ml-64 flex flex-col w-full">
  <?php include '../layouts/topbar.php'; ?>
  <main class="flex-1 px-6 py-6 fade-in">
    <h1 class="text-2xl font-bold mb-3"><?= $pageTitle ?></h1>
    <p class="text-sm text-gray-500 dark:text-gray-300 mb-4 leading-relaxed">
      Tahapan ini menentukan himpunan <strong>Concordance</strong> untuk tiap pasangan alternatif berdasarkan:<br>
      <code>C<sub>ij</sub> = { k | V<sub>ik</sub> ≥ V<sub>jk</sub> }</code><br>
      Nilai yang digunakan adalah hasil normalisasi terbobot (V<sub>ij</sub>).
    </p>

    <h2 class="text-lg font-semibold mb-3">🧮 Langkah Perhitungan Manual:</h2>
    <div class="text-sm mb-8">
      <?php foreach ($alternatifs as $a): foreach ($alternatifs as $b): if ($a['id'] === $b['id']) continue;
        echo "<div class='step-box'>";
        echo "<strong>🔸 {$a['nama']} vs {$b['nama']}:</strong><br>";
        $set = [];
        foreach ($kriteria as $k) {
          $vik = round($V[$a['id']][$k['id']] ?? 0, 6);
          $vjk = round($V[$b['id']][$k['id']] ?? 0, 6);
          if ($vik >= $vjk) {
            echo "C{$k['kode']}: " . number_format($vik, 4) . " ≥ " . number_format($vjk, 4) . " → ✅ Masuk<br>";
            $set[] = "C{$k['kode']}";
          } else {
            echo "C{$k['kode']}: " . number_format($vik, 4) . " < " . number_format($vjk, 4) . " → ❌ Lewat<br>";
          }
        }
        echo "<div class='mt-2'>✔️ Himpunan Concordance: <span class='set-box'>{ " . implode(', ', $set) . " }</span></div>";
        echo "</div>";
      endforeach; endforeach; ?>
    </div>

    <h2 class="text-lg font-semibold mb-3">📋 Tabel Himpunan Concordance (C<sub>ij</sub>)</h2>
    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow mb-6">
      <table class="min-w-full text-sm text-center border">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th class="px-4 py-2 text-left">i → j</th>
            <?php foreach ($alternatifs as $b): ?>
              <th class="px-4 py-2"><?= htmlspecialchars($b['nama']) ?></th>
            <?php endforeach ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alternatifs as $a): ?>
            <tr class="border-b dark:border-gray-700">
              <th class="px-4 py-2 text-left bg-indigo-50 dark:bg-indigo-900"><?= htmlspecialchars($a['nama']) ?></th>
              <?php foreach ($alternatifs as $b): ?>
                <td class="px-2 py-2">
                  <?php
                    if ($a['id'] === $b['id']) {
                      echo '<span class="text-gray-400">–</span>';
                    } else {
                      $set = getConcordanceSet($a['id'], $b['id'], $V, $kriteria);
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

    <div class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed max-w-3xl">
      <strong>Keterangan:</strong>
      <ul class="list-disc pl-5 space-y-1 mt-2">
        <li>Data yang digunakan adalah <strong>V<sub>ij</sub></strong>, hasil pembobotan dari matriks normalisasi.</li>
        <li>Jika V<sub>ik</sub> ≥ V<sub>jk</sub>, maka kriteria k masuk ke dalam C<sub>ij</sub>.</li>
        <li>Simbol "–" menunjukkan perbandingan dengan diri sendiri.</li>
      </ul>
    </div>
  </main>
  <?php include '../layouts/footer.php'; ?>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
