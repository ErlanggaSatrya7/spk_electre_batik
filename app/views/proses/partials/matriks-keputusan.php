<?php
require_once '../../../config/db.php';

// Ambil data
$kriteria = $conn->query("SELECT * FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$alternatif = $conn->query("SELECT * FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$nilai = $conn->query("SELECT * FROM nilai ORDER BY id_alternatif, id_kriteria")->fetchAll(PDO::FETCH_ASSOC);

// Susun nilai jadi matriks [id_alternatif][id_kriteria] => nilai
$matriks = [];
foreach ($nilai as $n) {
  $matriks[$n['id_alternatif']][$n['id_kriteria']] = $n['nilai'];
}
?>

<!-- Matriks Keputusan -->
<h2 class="text-lg font-semibold text-indigo-700 mb-4">Matriks Keputusan</h2>
<div class="overflow-auto">
  <table class="min-w-full bg-white border">
    <thead>
      <tr class="bg-gray-100">
        <th class="border px-4 py-2">Alternatif</th>
        <?php foreach ($kriteria as $k): ?>
          <th class="border px-4 py-2"><?= htmlspecialchars($k['kode']) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($alternatif as $alt): ?>
        <tr>
          <td class="border px-4 py-2 font-medium text-gray-700"><?= htmlspecialchars($alt['nama']) ?></td>
          <?php foreach ($kriteria as $k): ?>
            <td class="border px-4 py-2 text-center">
              <?= isset($matriks[$alt['id']][$k['id']]) ? $matriks[$alt['id']][$k['id']] : '-' ?>
            </td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
