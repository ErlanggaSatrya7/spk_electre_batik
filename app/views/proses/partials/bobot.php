<?php
require_once '../../../config/db.php';

// Ambil data kriteria dan alternatif
$kriteria = $conn->query("SELECT * FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$alternatif = $conn->query("SELECT * FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Ambil nilai dan hitung matriks normalisasi
$nilai = [];
$divisors = [];

foreach ($kriteria as $k) {
    $id_kriteria = $k['id'];
    $stmt = $conn->prepare("SELECT nilai FROM nilai WHERE id_kriteria = ?");
    $stmt->execute([$id_kriteria]);
    $squares = array_map(fn($row) => pow($row['nilai'], 2), $stmt->fetchAll(PDO::FETCH_ASSOC));
    $divisors[$id_kriteria] = sqrt(array_sum($squares));
}

// Matriks normalisasi x bobot
$matriksBobot = [];
foreach ($alternatif as $a) {
    $row = [];
    foreach ($kriteria as $k) {
        $stmt = $conn->prepare("SELECT nilai FROM nilai WHERE id_alternatif = ? AND id_kriteria = ?");
        $stmt->execute([$a['id'], $k['id']]);
        $nilaiX = $stmt->fetchColumn();
        $normal = $nilaiX / ($divisors[$k['id']] ?: 1);  // Hindari div 0
        $row[] = $normal * $k['bobot'];
    }
    $matriksBobot[] = [
        'nama' => $a['nama'],
        'data' => $row
    ];
}
?>

<div class="overflow-x-auto bg-white rounded shadow p-4">
  <h2 class="text-lg font-semibold mb-4 text-indigo-700">Matriks Normalisasi Terbobot</h2>
  <table class="min-w-full text-sm text-left">
    <thead class="bg-gray-100 text-gray-700">
      <tr>
        <th class="px-4 py-2">Alternatif</th>
        <?php foreach ($kriteria as $k): ?>
          <th class="px-4 py-2"><?= htmlspecialchars($k['kode']) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody class="text-gray-700">
      <?php foreach ($matriksBobot as $row): ?>
        <tr class="border-t">
          <td class="px-4 py-2 font-medium"><?= htmlspecialchars($row['nama']) ?></td>
          <?php foreach ($row['data'] as $val): ?>
            <td class="px-4 py-2"><?= number_format($val, 4) ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
