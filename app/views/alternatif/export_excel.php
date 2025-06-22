<?php
require_once '../../../config/db.php';

// Set header agar browser tahu ini file CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data_alternatif.csv');

// Buka output stream
$output = fopen('php://output', 'w');

// Header kolom
fputcsv($output, ['No', 'Nama Batik', 'Kategori', 'Asal Daerah', 'Deskripsi']);

// Ambil data dari database
$stmt = $conn->query("SELECT * FROM alternatif ORDER BY id ASC");
$alternatifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$no = 1;
foreach ($alternatifs as $alt) {
  fputcsv($output, [
    $no++,
    $alt['nama'],
    $alt['kategori'],
    $alt['asal_daerah'],
    $alt['deskripsi']
  ]);
}
exit;
