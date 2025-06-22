<?php
require_once '../../../config/db.php';

// Ambil data
$kriteria   = $conn->query("SELECT * FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$alternatif = $conn->query("SELECT * FROM alternatif ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Ambil nilai (nilai[a][k] = x)
$nilai = [];
foreach ($alternatif as $a) {
    foreach ($kriteria as $k) {
        $stmt = $conn->prepare("SELECT nilai FROM nilai WHERE id_alternatif = ? AND id_kriteria = ?");
        $stmt->execute([$a['id'], $k['id']]);
        $nilai[$a['id']][$k['id']] = (float) ($stmt->fetchColumn() ?? 0);
    }
}

// Hitung Concordance & Discordance
$totalBobot = array_sum(array_column($kriteria, 'bobot'));
$concordance = $discordance = [];

foreach ($alternatif as $a1) {
    foreach ($alternatif as $a2) {
        if ($a1['id'] === $a2['id']) continue;

        $bobot = 0;
        $numerator = 0;
        $denominator = 0;

        foreach ($kriteria as $k) {
            $v1 = $nilai[$a1['id']][$k['id']];
            $v2 = $nilai[$a2['id']][$k['id']];
            if ($v1 >= $v2) $bobot += $k['bobot'];
            $diff = abs($v1 - $v2);
            if ($v1 < $v2) $numerator = max($numerator, $diff);
            $denominator = max($denominator, $diff);
        }

        $concordance[$a1['id']][$a2['id']] = $totalBobot ? $bobot / $totalBobot : 0;
        $discordance[$a1['id']][$a2['id']] = $denominator ? $numerator / $denominator : 0;
    }
}

// Threshold
function totalMatrixSum($matrix) {
    $sum = 0;
    foreach ($matrix as $row) $sum += array_sum($row);
    return $sum;
}

$totalPerbandingan = count($alternatif) * (count($alternatif) - 1);
$c_threshold = $totalPerbandingan ? totalMatrixSum($concordance) / $totalPerbandingan : 0;
$d_threshold = $totalPerbandingan ? totalMatrixSum($discordance) / $totalPerbandingan : 0;

// Agregat Dominan
$agregat = [];
foreach ($alternatif as $a1) {
    foreach ($alternatif as $a2) {
        if ($a1['id'] === $a2['id']) continue;
        $agregat[$a1['id']][$a2['id']] = (
            $concordance[$a1['id']][$a2['id']] >= $c_threshold &&
            $discordance[$a1['id']][$a2['id']] <= $d_threshold
        ) ? 1 : 0;
    }
}

// Ranking
$ranking = [];
foreach ($alternatif as $a) {
    $ranking[$a['id']] = array_sum($agregat[$a['id']] ?? []);
}
arsort($ranking);
?>

<div class="bg-white rounded shadow p-4">
  <h2 class="text-lg font-semibold mb-4 text-indigo-700 flex items-center gap-2">
    <i data-lucide="list-ordered" class="w-5 h-5"></i>
    Hasil Perangkingan Alternatif
  </h2>
  <table class="min-w-full text-sm text-left">
    <thead class="bg-gray-100 text-gray-700">
      <tr>
        <th class="px-4 py-2">Peringkat</th>
        <th class="px-4 py-2">Nama Alternatif</th>
        <th class="px-4 py-2">Skor Dominasi</th>
      </tr>
    </thead>
    <tbody class="text-gray-700">
      <?php
      $i = 1;
      foreach ($ranking as $id => $skor):
        $nama = '';
        foreach ($alternatif as $a) {
            if ($a['id'] == $id) {
                $nama = $a['nama'];
                break;
            }
        }

        $highlight = $i === 1 ? 'bg-yellow-100 font-bold text-yellow-800' : '';
        $icon = $i === 1 ? '<i data-lucide="crown" class="inline-block w-4 h-4 text-yellow-500 mr-1"></i>' : '';
      ?>
        <tr class="border-t <?= $highlight ?>">
          <td class="px-4 py-2"><?= $icon ?><?= $i ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($nama) ?></td>
          <td class="px-4 py-2"><?= number_format($skor, 4) ?></td>
        </tr>
      <?php $i++; endforeach; ?>
    </tbody>
  </table>
</div>

<script>lucide.createIcons();</script>
