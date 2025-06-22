<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "🔶 Matriks Discordance";
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo = $pengaturan['logo'] ?? 'logo-batik.png';

$alternatifs = $conn->query("SELECT id, nama FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT id FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$nilaiStmt = $conn->query("SELECT id_alternatif, id_kriteria, nilai FROM nilai");
$nilai = [];
while ($row = $nilaiStmt->fetch(PDO::FETCH_ASSOC)) {
  $nilai[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
}

function hitungDiscordance($a, $b, $nilai, $kriteria) {
  $pembilang = 0;
  $penyebut = 0;
  foreach ($kriteria as $k) {
    $ka = $nilai[$a][$k['id']] ?? 0;
    $kb = $nilai[$b][$k['id']] ?? 0;
    $selisih = abs($ka - $kb);
    $penyebut = max($penyebut, $selisih);
    if ($ka < $kb) {
      $pembilang = max($pembilang, $selisih);
    }
  }
  return $penyebut == 0 ? 0 : $pembilang / $penyebut;
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
  <script>tailwind.config = { darkMode: 'class' };</script>
  <style>.fade-in { animation: fadeIn .5s ease-out forwards; opacity: 0; transform: translateY(10px); }
  @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }</style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">
<?php include '../layouts/splash.php'; include '../layouts/sidebar.php'; ?>
<div class="ml-64 flex-1 flex flex-col">
  <?php include '../layouts/topbar.php'; ?>
  <main class="flex-1 px-6 py-6 fade-in">
    <h1 class="text-2xl font-bold mb-2"><?= $pageTitle ?></h1>
    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Nilai selisih maksimum antar alternatif i → j jika i < j.</p>
    <div class="overflow-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm text-center text-gray-800 dark:text-gray-200">
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
            <tr class="border-b border-gray-200 dark:border-gray-700">
              <th class="px-4 py-3 bg-rose-50 dark:bg-rose-900"><?= htmlspecialchars($a['nama']) ?></th>
              <?php foreach ($alternatifs as $b): ?>
                <td class="px-4 py-3">
                  <?= $a['id'] == $b['id'] ? '—' : number_format(hitungDiscordance($a['id'], $b['id'], $nilai, $kriteria), 2) ?>
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
