<?php
require '../../../config/db.php';

// Set header supaya browser langsung download file
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=hasil_ranking.csv");
header("Pragma: no-cache");
header("Expires: 0");

$output = fopen("php://output", "w");

// Header kolom
fputcsv($output, ['Peringkat', 'Alternatif', 'Skor']);

$alternatif = $conn->query("SELECT * FROM alternatif")->fetchAll(PDO::FETCH_ASSOC);
$ranking = [];
foreach ($alternatif as $a) {
  $ranking[$a['id']] = $a['skor'] ?? 0;
}
arsort($ranking);

// Data isi
$i = 1;
foreach ($ranking as $id => $skor) {
  $alt = array_filter($alternatif, fn($a) => $a['id'] == $id);
  $alt = reset($alt);
  fputcsv($output, [$i++, $alt['nama'], number_format($skor, 4)]);
}

fclose($output);
exit;
