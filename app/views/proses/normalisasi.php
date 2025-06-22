<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "🔢 Normalisasi Matriks Keputusan";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

// Ambil data
$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT id, nama, kode FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Hitung denominator tiap kriteria
$denominators = [];
foreach ($kriteria as $k) {
  $stmt = $conn->prepare("SELECT nilai FROM nilai WHERE id_kriteria = ?");
  $stmt->execute([$k['id']]);
  $squares = 0;
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $squares += pow(floatval($row['nilai']), 2);
  }
  $denominators[$k['id']] = sqrt($squares);
}

// Ambil nilai tiap alternatif
$nilai = [];
$stmt = $conn->query("SELECT id_alternatif, id_kriteria, nilai FROM nilai");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $nilai[$row['id_alternatif']][$row['id_kriteria']] = floatval($row['nilai']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?> | <?= $pengaturan['nama_aplikasi'] ?? 'SPK' ?></title>
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
    .fade-in { animation: fadeIn .5s ease-out forwards; opacity: 0; transform: translateY(10px); }
    @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex">

<?php include '../layouts/splash.php'; ?>
<?php include '../layouts/sidebar.php'; ?>

<div class="ml-64 flex-1 flex flex-col">
  <?php include '../layouts/topbar.php'; ?>

  <main class="flex-1 px-6 py-6 fade-in">
    <div class="mb-6">
      <h1 class="text-2xl font-bold"><?= $pageTitle ?></h1>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Perhitungan normalisasi berdasarkan rumus: R<sub>ij</sub> = x<sub>ij</sub> / √(∑x<sub>ij</sub><sup>2</sup>)
      </p>
    </div>

    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-gray-700 dark:text-gray-200 text-left">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Alternatif</th>
            <?php foreach ($kriteria as $k): ?>
              <th class="px-4 py-3"><?= htmlspecialchars($k['kode']) ?></th>
            <?php endforeach ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alternatifs as $i => $alt): ?>
            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
              <td class="px-4 py-3"><?= $i + 1 ?></td>
              <td class="px-4 py-3"><?= htmlspecialchars($alt['nama']) ?></td>
              <?php foreach ($kriteria as $k): ?>
                <td class="px-4 py-3">
                  <?php
                    $raw = $nilai[$alt['id']][$k['id']] ?? 0;
                    $denom = $denominators[$k['id']] ?: 1;
                    $normalized = $raw / $denom;
                    echo number_format($normalized, 4);
                  ?>
                </td>
              <?php endforeach ?>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </main>

  <?php include '../layouts/footer.php'; ?>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
