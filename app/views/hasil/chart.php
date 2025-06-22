<?php include '../layouts/admin/sidebar.php'; ?>
<?php include '../layouts/admin/topbar.php'; ?>

<main class="p-6 md:ml-64">
  <h1 class="text-2xl font-bold mb-4 text-blue-700">Visualisasi Hasil Ranking</h1>
  <div class="bg-white p-6 shadow rounded">
    <canvas id="chart"></canvas>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('chart');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [<?= implode(",", array_map(fn($a) => json_encode($a['nama']), $alternatif)) ?>],
      datasets: [{
        label: 'Skor',
        data: [<?= implode(",", array_values($ranking)) ?>],
        backgroundColor: 'rgba(99,102,241,0.7)'
      }]
    },
    options: { responsive: true }
  });
</script>
