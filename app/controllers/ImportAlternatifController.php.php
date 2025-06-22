<?php
require_once '../../config/db.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
  die("File tidak ditemukan atau terjadi error saat upload.");
}

$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['xls', 'xlsx'])) {
  die("Format file tidak valid. Hanya file .xls atau .xlsx yang diperbolehkan.");
}

// Baca file Excel
$spreadsheet = IOFactory::load($_FILES['file']['tmp_name']);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

$totalInserted = 0;

// Mulai dari baris ke-2 (abaikan header)
foreach ($data as $i => $row) {
  if ($i < 2) continue; // baris 1 header

  $nama         = trim($row['A'] ?? '');
  $kategori     = trim($row['B'] ?? '');
  $asal         = trim($row['C'] ?? '');
  $deskripsi    = trim($row['D'] ?? '');

  if (!$nama) continue; // skip jika nama kosong

  $stmt = $conn->prepare("INSERT INTO alternatif (nama, kategori, asal_daerah, deskripsi) VALUES (?, ?, ?, ?)");
  $stmt->execute([$nama, $kategori, $asal, $deskripsi]);
  $totalInserted++;
}

header("Location: ../../app/views/alternatif/index.php?import_success=$totalInserted");
exit;
