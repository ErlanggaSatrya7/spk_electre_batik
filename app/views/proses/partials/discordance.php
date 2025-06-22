<?php
require_once '../../../config/db.php';

// Ambil data kriteria dan alternatif
$kriteria   = $conn->query("SELECT * FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$alternatif = $conn->query("SELECT * FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Ambil nilai
$nilai = [];
foreach ($alternatif as $a) {
    $row = [];
    foreach ($kriteria as $k) {
        $stmt = $conn->prepare("SELECT nilai FROM nilai WHERE id_alternatif = ? AND id_kriteria = ?");
        $stmt->execute([$a['id'], $k['id']]);
        $row[$k['id']] = $stmt->fetchColumn();
    }
    $nilai[$a['id']] = $row;
}

// Matriks Discordance
$discordance = [];
foreach ($alternatif as $a1) {
    $discordance[$a1['id']] = [];

    foreach ($alternatif as $a2) {
        if ($a1['id'] === $a2['id']) {
            $discordance[$a1['id']][$a2['id']] = '-';
            continue;
        }

        $numerator = 0;
        $denominator = 0;

        foreach ($kriteria as $k) {
            $diff = abs($nilai[$a1['id']][$k['id']] - $nilai[$a2['id']][$k['id']]);
            $denominator = max($denominator, $diff);
            if ($nilai[$a1['id']][$k['id']] < $nilai[$a2['id']][$k['id']]) {
                $numerator = max($numerator, $diff);
            }
        }

        $discordance[$a1['id']][$a2['id']] = $denominator == 0 ? 0 : $numerator / $denominator;
    }
}
?>

<div class="overflow-x-auto bg-white rounded shadow p-4">
  <h2 class="text-lg font-semibold mb-4 text-indigo-700">Matriks Discordance</h2>
  <table class="min-w-full text-sm text-left">
    <thead class="bg-gray-100 text-gray-700">
      <tr>
        <th class="px-4 py-2">A → A</th>
        <?php foreach ($alternatif as $a): ?>
          <th class="px-4 py-2"><?= htmlspecialchars($a['nama']) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody class="text-gray-700">
      <?php foreach ($alternatif as $a1): ?>
        <tr class="border-t">
          <td class="px-4 py-2 font-medium"><?= htmlspecialchars($a1['nama']) ?></td>
          <?php foreach ($alternatif as $a2): ?>
            <td class="px-4 py-2 text-center">
              <?= is_numeric($discordance[$a1['id']][$a2['id']]) ? number_format($discordance[$a1['id']][$a2['id']], 4) : '-' ?>
            </td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
