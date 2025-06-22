<?php
require '../../../vendor/autoload.php';
require '../../../config/db.php';

use Dompdf\Dompdf;

$stmt = $conn->query("SELECT * FROM alternatif ORDER BY id");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// HTML Template
$html = '<h2 style="text-align:center;">Laporan Hasil Perangkingan</h2>';
$html .= '<table border="1" width="100%" cellspacing="0" cellpadding="6">
<thead>
<tr>
  <th>No</th>
  <th>Nama Alternatif</th>
  <th>Skor</th>
</tr>
</thead>
<tbody>';

$no = 1;
foreach ($data as $alt) {
    $html .= "<tr>
        <td>{$no}</td>
        <td>{$alt['nama']}</td>
        <td>" . rand(1, 10) . "</td> <!-- ganti skor sesuai -->
    </tr>";
    $no++;
}
$html .= '</tbody></table>';

// Konversi ke PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("hasil_perangkingan_" . date('Ymd_His') . ".pdf", array("Attachment" => 1));
exit;
