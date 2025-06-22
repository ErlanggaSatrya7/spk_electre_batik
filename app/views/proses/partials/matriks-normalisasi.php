<?php
// Ambil data alternatif
$alternatifStmt = $conn->query("SELECT * FROM alternatif ORDER BY id ASC");
$alternatif = $alternatifStmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data kriteria
$kriteriaStmt = $conn->query("SELECT * FROM kriteria ORDER BY id ASC");
$kriteria = $kriteriaStmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil nilai
$nilaiStmt = $conn->query("SELECT * FROM nilai");
$nilaiData = $nilaiStmt->fetchAll(PDO::FETCH_ASSOC);

// Buat matriks nilai alternatif
$matriks = [];
foreach ($nilaiData as $n) {
  $matriks[$n['id_alternatif']][$n['id_kriteria']] = $n['nilai'];
}

// Hitung nilai maksimal untuk setiap kriteria (normalisasi)
$max = [];
foreach ($kriteria as $k) {
  $id_k = $k['id'];
  $max[$id_k] = 0;
  foreach ($alternatif as $a) {
    $val = $matriks[$a['id']][$id_k] ?? 0;
    if ($val > $max[$id_k]) $max[$id_k] = $val;
  }
}
?>

<div class="overflow-x-auto bg-white rounded shadow p-4">
  <h2 class="text-lg font-bold text-gray-700 mb-4">Normalisasi Matriks</h2>
  <table class="min-w-full text-sm text-center border border-gray-300">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-3 py-2 text-left">Alternatif</th>
        <?php foreach ($kriteria as $k): ?>
          <th class="px-3 py-2"><?= htmlspecialchars($k['kode']) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($alternatif as $a): ?>
        <tr class="border-t">
          <td class="px-3 py-2 text-left"><?= htmlspecialchars($a['nama']) ?></td>
          <?php foreach ($kriteria as $k): 
            $id_k = $k['id'];
            $val = $matriks[$a['id']][$id_k] ?? 0;
            $norm = $max[$id_k] != 0 ? $val / $max[$id_k] : 0;
          ?>
            <td class="px-3 py-2"><?= number_format($norm, 4) ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
