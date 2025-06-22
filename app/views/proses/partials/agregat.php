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

// Matriks Concordance
$concordance = [];
$totalBobot = array_sum(array_column($kriteria, 'bobot'));

foreach ($alternatif as $a1) {
    foreach ($alternatif as $a2) {
        if ($a1['id'] === $a2['id']) {
            $concordance[$a1['id']][$a2['id']] = 0;
            continue;
        }

        $bobot = 0;
        foreach ($kriteria as $k) {
            if ($nilai[$a1['id']][$k['id']] >= $nilai[$a2['id']][$k['id']]) {
                $bobot += $k['bobot'];
            }
        }
        $concordance[$a1['id']][$a2['id']] = $bobot / $totalBobot;
    }
}

// Matriks Discordance
$discordance = [];
foreach ($alternatif as $a1) {
    foreach ($alternatif as $a2) {
        if ($a1['id'] === $a2['id']) {
            $discordance[$a1['id']][$a2['id']] = 0;
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

// Threshold Concordance dan Discordance
$c_threshold = array_sum(array_map('array_sum', $concordance)) / (count($alternatif) * (count($alternatif) - 1));
$d_threshold = array_sum(array_map('array_sum', $discordance)) / (count($alternatif) * (count($alternatif) - 1));

// Matriks Agregat Dominan
$agregat = [];
foreach ($alternatif as $a1) {
    foreach ($alternatif as $a2) {
        if ($a1['id'] === $a2['id']) {
            $agregat[$a1['id']][$a2['id']] = '-';
            continue;
        }

        $agregat[$a1['id']][$a2['id']] =
            ($concordance[$a1['id']][$a2['id']] >= $c_threshold && $discordance[$a1['id']][$a2['id']] <= $d_threshold)
            ? 1 : 0;
    }
}
?>

<div class="overflow-x-auto bg-white rounded shadow p-4">
  <h2 class="text-lg font-semibold mb-4 text-indigo-700">Matriks Agregat Dominan</h2>
  <p class="text-sm text-gray-500 mb-4">
    Threshold Concordance: <strong><?= number_format($c_threshold, 4) ?></strong> |
    Threshold Discordance: <strong><?= number_format($d_threshold, 4) ?></strong>
  </p>
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
            <td class="px-4 py-2 text-center"><?= $agregat[$a1['id']][$a2['id']] ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
