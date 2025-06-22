<?php
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

$pageTitle = "Hasil Perangkingan";

// Ambil data alternatif dan skor
$alternatif = $conn->query("SELECT * FROM alternatif")->fetchAll(PDO::FETCH_ASSOC);
$ranking = [];
foreach ($alternatif as $a) {
  $ranking[$a['id']] = $a['skor'] ?? 0;
}
arsort($ranking); // Urutkan skor tertinggi ke bawah
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title><?= $pageTitle ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">

  <?php include '../layouts/sidebar.php'; ?>

  <div class="ml-64 flex-1 flex flex-col min-h-screen">
    <?php include '../layouts/topbar.php'; ?>

    <main class="flex-1 px-6 py-6 bg-gray-50">
      <h1 class="text-2xl font-bold mb-4 text-gray-800"><?= $pageTitle ?></h1>

      <!-- Tabs -->
      <div>
        <div class="flex border-b border-gray-200 mb-4">
          <button onclick="showTab('ranking')" class="tab-button px-4 py-2 border-b-2 font-semibold text-sm text-indigo-600 border-indigo-600">Ranking</button>
          <button onclick="showTab('chart')" class="tab-button px-4 py-2 text-sm text-gray-600 hover:text-indigo-600">Chart</button>
          <button onclick="showTab('export')" class="tab-button px-4 py-2 text-sm text-gray-600 hover:text-indigo-600">Export</button>
        </div>

        <!-- Tab: Ranking -->
        <div id="tab-ranking" class="tab-content">
          <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full text-sm text-left">
              <thead class="bg-indigo-100 text-indigo-700">
                <tr>
                  <th class="px-6 py-3">Peringkat</th>
                  <th class="px-6 py-3">Alternatif</th>
                  <th class="px-6 py-3">Skor Dominasi</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1;
                foreach ($ranking as $id => $skor):
                  $alt = array_filter($alternatif, fn($a) => $a['id'] == $id);
                  $alt = reset($alt);
                  $highlight = $i === 1 ? 'bg-yellow-100 font-semibold' : '';
                ?>
                  <tr class="border-t <?= $highlight ?>">
                    <td class="px-6 py-4"><?= $i ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($alt['nama']) ?></td>
                    <td class="px-6 py-4"><?= number_format($skor, 4) ?></td>
                  </tr>
                <?php $i++; endforeach ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab: Chart -->
        <div id="tab-chart" class="tab-content hidden">
          <div class="bg-white rounded shadow p-6">
            <canvas id="rankingChart" width="400" height="200"></canvas>
          </div>
        </div>

        <!-- Tab: Export -->
        <div id="tab-export" class="tab-content hidden">
          <div class="bg-white rounded shadow p-6 space-y-4">
            <a href="export_excel.php" class="inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow text-sm">📄 Export ke Excel (CSV)</a>
            <a href="export_pdf.php" class="inline-block bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow text-sm">📕 Export ke PDF</a>
          </div>
        </div>
      </div>
    </main>

    <?php include '../layouts/footer.php'; ?>
  </div>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  let chartInstance = null;

  function showTab(id) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('border-b-2', 'text-indigo-600', 'border-indigo-600'));
    document.getElementById('tab-' + id).classList.remove('hidden');
    event.target.classList.add('border-b-2', 'text-indigo-600', 'border-indigo-600');

    // Inisialisasi Chart hanya saat tab chart dibuka
    if (id === 'chart' && !chartInstance) {
      const labels = [<?= implode(',', array_map(function($id) use ($alternatif) {
        $alt = array_filter($alternatif, fn($a) => $a['id'] == $id);
        return json_encode(reset($alt)['nama']);
      }, array_keys($ranking))) ?>];

      const data = {
        labels: labels,
        datasets: [{
          label: 'Skor Dominasi',
          data: [<?= implode(',', array_map('floatval', array_values($ranking))) ?>],
          backgroundColor: 'rgba(99, 102, 241, 0.7)',
          borderRadius: 6
        }]
      };

      const ctx = document.getElementById('rankingChart');
      if (ctx) {
        chartInstance = new Chart(ctx, {
          type: 'bar',
          data: data,
          options: {
            responsive: true,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: ctx => `${ctx.raw.toFixed(4)} skor`
                }
              }
            },
            scales: {
              y: { beginAtZero: true, ticks: { stepSize: 0.1 } }
            }
          }
        });
      }
    }
  }

  showTab('ranking'); // Default tab
</script>

  <script>lucide.createIcons();</script>
</body>
</html>
