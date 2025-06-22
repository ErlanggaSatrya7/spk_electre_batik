<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "📊 Proses Perhitungan ELECTRE";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';
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
    .fade-in {
      animation: fadeIn 0.5s ease-out forwards;
      opacity: 0;
      transform: translateY(10px);
    }
    @keyframes fadeIn {
      to { opacity: 1; transform: translateY(0); }
    }
    .tab:hover {
      background-color: rgba(99, 102, 241, 0.08);
    }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex transition">

<?php include '../layouts/splash.php'; ?>
<?php include '../layouts/sidebar.php'; ?>

<div class="ml-64 flex-1 flex flex-col">
  <?php include '../layouts/topbar.php'; ?>

  <main class="flex-1 px-6 py-6 fade-in">
    <div class="mb-6">
      <h1 class="text-2xl font-bold"><?= $pageTitle ?></h1>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Proses lengkap dimulai dari normalisasi hingga perangkingan hasil akhir berdasarkan metode ELECTRE.
      </p>
    </div>

    <?php
    $steps = [
      ['file' => 'normalisasi.php',        'icon' => 'table',         'title' => 'Langkah 1: Normalisasi',           'desc' => '🔢 Normalisasi matriks keputusan.'],
      ['file' => 'pembobotan.php',         'icon' => 'scale',         'title' => 'Langkah 2: Pembobotan',            'desc' => '⚖️ Pembobotan berdasarkan bobot kriteria.'],
      ['file' => 'concordance_index.php',  'icon' => 'check-circle',  'title' => 'Langkah 3: Concordance Index',     'desc' => '📈 Indeks dominasi keunggulan.'],
      ['file' => 'discordance_index.php',  'icon' => 'x-circle',      'title' => 'Langkah 3: Discordance Index',     'desc' => '📉 Indeks dominasi kelemahan.'],
      ['file' => 'matriks_concordance.php','icon' => 'grid',          'title' => 'Langkah 4: Matriks Concordance',   'desc' => '🔷 Matriks perbandingan antar alternatif.'],
      ['file' => 'matriks_discordance.php','icon' => 'grid',          'title' => 'Langkah 4: Matriks Discordance',   'desc' => '🔶 Ketidaksesuaian antar alternatif.'],
      ['file' => 'dominan_concordance.php','icon' => 'check',         'title' => 'Langkah 5: Dominan Concordance',   'desc' => '✅ Dominasi keunggulan antar alternatif.'],
      ['file' => 'dominan_discordance.php','icon' => 'x',             'title' => 'Langkah 5: Dominan Discordance',   'desc' => '❌ Dominasi kelemahan antar alternatif.'],
      ['file' => 'aggregate.php',          'icon' => 'layers',        'title' => 'Langkah 6: Matriks Agregat',       'desc' => '💠 Gabungan dominan untuk perankingan.'],
      ['file' => 'perangkingan.php',       'icon' => 'star',          'title' => 'Langkah 7: Perangkingan',          'desc' => '🏆 Urutan akhir alternatif terbaik.'],
    ];
    ?>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
      <?php foreach ($steps as $i => $step): ?>
        <a href="<?= $step['file'] ?>" class="tab bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-lg shadow flex items-start gap-4 hover:shadow-lg transition">
          <div class="flex flex-col items-center justify-center">
            <div class="bg-indigo-600 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center"><?= $i + 1 ?></div>
            <i data-lucide="<?= $step['icon'] ?>" class="text-indigo-600 dark:text-indigo-400 w-5 h-5 mt-1"></i>
          </div>
          <div>
            <h3 class="text-md font-semibold"><?= $step['title'] ?></h3>
            <p class="text-sm text-gray-600 dark:text-gray-400"><?= $step['desc'] ?></p>
          </div>
        </a>
      <?php endforeach ?>
    </div>
  </main>

  <?php include '../layouts/footer.php'; ?>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
