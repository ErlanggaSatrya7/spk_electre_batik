<?php
require_once '../../../config/db.php';
require_once '../../../vendor/autoload.php';

use TCPDF;

$pdf = new TCPDF();
$pdf->SetTitle('Data Alternatif');
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

$html = '<h2>Data Alternatif Batik</h2><br>';
$stmt = $conn->query("SELECT * FROM alternatif ORDER BY id DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($data as $alt) {
  $html .= '<b>' . htmlspecialchars($alt['nama']) . '</b><br>';
  $html .= 'Kategori: ' . htmlspecialchars($alt['kategori']) . '<br>';
  $html .= 'Asal: ' . htmlspecialchars($alt['asal_daerah']) . '<br>';
  $html .= 'Deskripsi: ' . htmlspecialchars($alt['deskripsi']) . '<br>';

  $imgs = $conn->prepare("SELECT filename FROM gambar_alternatif WHERE alternatif_id = ?");
  $imgs->execute([$alt['id']]);
  foreach ($imgs->fetchAll(PDO::FETCH_COLUMN) as $img) {
    $filePath = '../../../assets/images/' . $img;
    if (file_exists($filePath)) {
      $pdf->Image($filePath, '', '', 30, 30);
    }
  }
  $html .= '<br><br>';
}

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('data-alternatif.pdf', 'I');
