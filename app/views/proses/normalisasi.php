<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "🔢 Langkah 1: Normalisasi Matriks Keputusan";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT id, nama, kode, tipe FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Ambil nilai asli, hitung kuadrat dan akar total per kriteria
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
    <h1 class="text-2xl font-bold mb-2">Langkah 1: Normalisasi Matriks Keputusan</h1>
    <p class="text-sm text-gray-500 dark:text-gray-300 leading-relaxed mb-4">
      Tahap ini mengubah nilai asli <code>x<sub>ij</sub></code> ke dalam skala seragam.<br>
      Rumus:
      <ul class="list-disc pl-5 mt-2 text-xs">
        <li><code>Benefit</code>: R<sub>ij</sub> = x<sub>ij</sub> / √(∑x<sub>ij</sub><sup>2</sup>)</li>
        <li><code>Cost</code>: R<sub>ij</sub> = min(x<sub>ij</sub>) / x<sub>ij</sub></li>
      </ul>
    </p>

    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center border text-gray-800 dark:text-gray-200">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th rowspan="2" class="border px-4 py-2">#</th>
            <th rowspan="2" class="border px-4 py-2">Alternatif</th>
            <?php foreach ($kriteria as $k): ?>
              <th colspan="3" class="border px-2 py-1"><?= $k['kode'] ?><br><span class="text-xs font-normal"><?= $k['nama'] ?></span></th>
            <?php endforeach ?>
          </tr>
          <tr class="text-xs bg-indigo-50 dark:bg-indigo-800 text-gray-600 dark:text-gray-400">
            <?php foreach ($kriteria as $k): ?>
              <th class="border">x<sub>ij</sub></th>
              <th class="border">x<sub>ij</sub><sup>2</sup></th>
              <th class="border">R<sub>ij</sub></th>
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
                $x2 = $x ** 2;
                if ($k['tipe'] === 'cost') {
                  $rij = $x == 0 ? 0 : ($min[$k['id']] / $x);
                } else {
                  $rij = $akar[$k['id']] == 0 ? 0 : $x / $akar[$k['id']];
                }
              ?>
                <td class="border"><?= number_format($x, 2) ?></td>
                <td class="border"><?= number_format($x2, 2) ?></td>
                <td class="border font-semibold text-indigo-600"><?= number_format($rij, 4) ?></td>
              <?php endforeach ?>
            </tr>
          <?php endforeach ?>
        </tbody>
        <tfoot class="bg-gray-50 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-300">
          <tr>
            <td colspan="2" class="text-right font-semibold pr-3">√∑x<sub>ij</sub><sup>2</sup></td>
            <?php foreach ($kriteria as $k): ?>
              <td colspan="3" class="text-center font-bold text-indigo-500">
                <?= $k['tipe'] === 'cost' ? 'min = ' . number_format($min[$k['id']], 2) : number_format($akar[$k['id']], 4) ?>
              </td>
            <?php endforeach ?>
          </tr>
        </tfoot>
      </table>
    </div>
  </main>

  <?php include '../layouts/footer.php'; ?>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
