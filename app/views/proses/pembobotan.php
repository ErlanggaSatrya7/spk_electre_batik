<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "⚖️ Langkah 2: Matriks Terbobot";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT id, nama, kode, tipe, bobot FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Ambil nilai asli, dan siapkan kuadrat dan minimum per kriteria
$nilai = [];
$kuadrat = [];
$akar = [];
$min = [];

foreach ($kriteria as $k) {
  $idk = $k['id'];
  $kuadrat[$idk] = 0;
  $min[$idk] = null;

  $stmt = $conn->prepare("SELECT id_alternatif, nilai FROM nilai WHERE id_kriteria = ?");
  $stmt->execute([$idk]);
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $x = floatval($row['nilai']);
    $nilai[$row['id_alternatif']][$idk] = $x;
    $kuadrat[$idk] += pow($x, 2);
    if ($min[$idk] === null || $x < $min[$idk]) $min[$idk] = $x;
  }

  $akar[$idk] = sqrt($kuadrat[$idk]);
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
    .subtext { font-size: 0.75rem; color: #94a3b8; }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex">
<?php include '../layouts/splash.php'; ?>
<?php include '../layouts/sidebar.php'; ?>

<div class="ml-64 flex-1 flex flex-col">
  <?php include '../layouts/topbar.php'; ?>

  <main class="flex-1 px-6 py-6 fade-in">
    <div class="mb-6">
      <h1 class="text-2xl font-bold mb-2"><?= $pageTitle ?></h1>
      <p class="text-sm text-gray-500 dark:text-gray-300 leading-relaxed mb-4">
        Matriks terbobot (<strong>V<sub>ij</sub></strong>) diperoleh dari normalisasi dikali bobot:<br>
        <code>V<sub>ij</sub> = R<sub>ij</sub> × W<sub>j</sub></code>
      </p>

      <div class="text-sm bg-blue-50 dark:bg-blue-900 text-blue-800 dark:text-blue-200 p-4 rounded mb-6 leading-relaxed">
        <strong>📘 Langkah Perhitungan:</strong>
        <ol class="list-decimal pl-5 mt-1 space-y-1">
          <li>Jika <strong>benefit</strong>: <code>R<sub>ij</sub> = x<sub>ij</sub> / √∑x<sub>ij</sub><sup>2</sup></code></li>
          <li>Jika <strong>cost</strong>: <code>R<sub>ij</sub> = min(x<sub>ij</sub>) / x<sub>ij</sub></code></li>
          <li>Kalikan R<sub>ij</sub> × bobot W<sub>j</sub> → dapatkan V<sub>ij</sub></li>
        </ol>
      </div>
    </div>

    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center text-gray-700 dark:text-gray-200 border">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th rowspan="2" class="border px-4 py-2">#</th>
            <th rowspan="2" class="border px-4 py-2">Alternatif</th>
            <?php foreach ($kriteria as $k): ?>
              <th colspan="3" class="border px-2 py-1">
                <?= $k['kode'] ?><br>
                <span class="subtext">w = <?= number_format($k['bobot'], 2) ?> | <?= $k['tipe'] ?></span>
              </th>
            <?php endforeach ?>
          </tr>
          <tr class="text-xs bg-indigo-50 dark:bg-indigo-800 text-gray-600 dark:text-gray-400">
            <?php foreach ($kriteria as $k): ?>
              <th class="border">x<sub>ij</sub></th>
              <th class="border">R<sub>ij</sub></th>
              <th class="border">V<sub>ij</sub></th>
            <?php endforeach ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alternatifs as $i => $alt): ?>
            <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-800">
              <td class="border px-2 py-2"><?= $i + 1 ?></td>
              <td class="border px-2 py-2 text-left"><?= htmlspecialchars($alt['nama']) ?></td>
              <?php foreach ($kriteria as $k): 
                $x = $nilai[$alt['id']][$k['id']] ?? 0;
                if ($k['tipe'] === 'cost') {
                  $rij = $x == 0 ? 0 : $min[$k['id']] / $x;
                } else {
                  $rij = $akar[$k['id']] > 0 ? $x / $akar[$k['id']] : 0;
                }
                $vij = $rij * $k['bobot'];
              ?>
                <td class="border"><?= number_format($x, 2) ?></td>
                <td class="border"><?= number_format($rij, 4) ?></td>
                <td class="border font-semibold text-indigo-600"><?= number_format($vij, 4) ?></td>
              <?php endforeach ?>
            </tr>
          <?php endforeach ?>
        </tbody>
        <tfoot class="bg-gray-50 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-300">
          <tr>
            <td colspan="2" class="text-right font-semibold pr-3">Referensi</td>
            <?php foreach ($kriteria as $k): ?>
              <td colspan="3" class="text-center text-indigo-500 font-bold">
                <?= $k['tipe'] === 'cost' ? 'min = ' . number_format($min[$k['id']], 2) : '√∑² = ' . number_format($akar[$k['id']], 4) ?>
              </td>
            <?php endforeach ?>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="mt-8 text-sm text-gray-500 dark:text-gray-400 leading-relaxed max-w-3xl">
      <h3 class="font-semibold text-gray-800 dark:text-white mb-2">📊 Ringkasan Matriks Terbobot (V<sub>ij</sub>):</h3>
      <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded overflow-x-auto">
<?php
foreach ($alternatifs as $alt) {
  $baris = '';
  foreach ($kriteria as $k) {
    $x = $nilai[$alt['id']][$k['id']] ?? 0;
    $rij = $k['tipe'] === 'cost'
      ? ($x == 0 ? 0 : $min[$k['id']] / $x)
      : ($akar[$k['id']] > 0 ? $x / $akar[$k['id']] : 0);
    $vij = $rij * $k['bobot'];
    $baris .= str_pad(number_format($vij, 3), 8);
  }
  echo htmlspecialchars($baris) . "\n";
}
?>
      </pre>
    </div>
  </main>
  <?php include '../layouts/footer.php'; ?>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
