<?php
$agregat = [];
$jumlahDominasi = [];

foreach ($alternatif as $i) {
  $total = 0;
  foreach ($alternatif as $j) {
    if ($i['id'] == $j['id']) {
      $agregat[$i['id']][$j['id']] = 0;
    } else {
      $agregat[$i['id']][$j['id']] = ($F[$i['id']][$j['id']] == 1 && $G[$i['id']][$j['id']] == 1) ? 1 : 0;
      $total += $agregat[$i['id']][$j['id']];
    }
  }
  $jumlahDominasi[$i['id']] = $total;
}

// Buat ranking
arsort($jumlahDominasi);
$ranking = 1;
$hasilRanking = [];

foreach ($jumlahDominasi as $id => $jumlah) {
  $hasilRanking[] = [
    'nama' => $alternatif[array_search($id, array_column($alternatif, 'id'))]['nama'],
    'nilai' => $jumlah,
    'ranking' => $ranking++
  ];
}
?>

<h2 class="text-lg font-semibold text-purple-700 mb-4">7. Matriks Agregat dan Ranking</h2>

<!-- Matriks Agregat -->
<div class="overflow-x-auto mb-8">
  <h3 class="font-medium mb-2">Matriks Agregat (E)</h3>
  <table class="min-w-full text-sm border">
    <thead class="bg-purple-100 text-purple-700">
      <tr>
        <th class="px-4 py-2">E(i,j)</th>
        <?php foreach ($alternatif as $a): ?>
          <th class="px-4 py-2 text-center"><?= htmlspecialchars($a['nama']) ?></th>
        <?php endforeach ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($alternatif as $i): ?>
        <tr class="border-b">
          <td class="px-4 py-2 font-medium"><?= htmlspecialchars($i['nama']) ?></td>
          <?php foreach ($alternatif as $j): ?>
            <td class="px-4 py-2 text-center"><?= $agregat[$i['id']][$j['id']] ?></td>
          <?php endforeach ?>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>

<!-- Ranking Akhir -->
<div class="overflow-x-auto">
  <h3 class="font-medium mb-2">Hasil Perangkingan</h3>
  <table class="min-w-full text-sm border">
    <thead class="bg-green-100 text-green-700">
      <tr>
        <th class="px-4 py-2 text-left">Ranking</th>
        <th class="px-4 py-2 text-left">Alternatif</th>
        <th class="px-4 py-2 text-left">Jumlah Dominasi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($hasilRanking as $row): ?>
        <tr class="border-b">
          <td class="px-4 py-2"><?= $row['ranking'] ?></td>
          <td class="px-4 py-2"><?= $row['nama'] ?></td>
          <td class="px-4 py-2"><?= $row['nilai'] ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
