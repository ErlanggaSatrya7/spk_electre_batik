<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "⚖️ Matriks Terbobot";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

// Ambil data alternatif dan kriteria
$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT id, nama, kode, bobot FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Ambil nilai tiap alternatif
$nilai = [];
$stmt = $conn->query("SELECT id_alternatif, id_kriteria, nilai FROM nilai");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $nilai[$row['id_alternatif']][$row['id_kriteria']] = floatval($row['nilai']);
}

// Hitung normalisasi dan pembobotan
$denominators = [];
foreach ($kriteria as $k) {
  $stmt = $conn->prepare("SELECT nilai FROM nilai WHERE id_kriteria = ?");
  $stmt->execute([$k['id']]);
  $sum = 0;
  while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sum += pow(floatval($r['nilai']), 2);
  }
  $denominators[$k['id']] = sqrt($sum);
}
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
    .fade-in { animation: fadeIn .5s ease-out forwards; opacity: 0; transform: translateY(10px); }
    @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
    .bobot-label {
      font-size: 0.75rem;
      color: #64748b;
    }
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
        Nilai normalisasi dikalikan dengan bobot kriteria.<br>R<sub>ij</sub> × w<sub>j</sub>
      </p>
    </div>

    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-gray-700 dark:text-gray-200 text-left">
        <thead class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">
          <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Alternatif</th>
            <?php foreach ($kriteria as $k): ?>
              <th class="px-4 py-3">
                <?= htmlspecialchars($k['kode']) ?>
                <div class="bobot-label">(bobot: <?= number_format($k['bobot'], 2) ?>)</div>
              </th>
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
                    $norm = $denominators[$k['id']] != 0 ? $raw / $denominators[$k['id']] : 0;
                    $bobot = $norm * $k['bobot'];
                    echo number_format($bobot, 4);
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
