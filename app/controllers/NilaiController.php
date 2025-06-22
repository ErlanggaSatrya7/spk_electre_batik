<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo "Method not allowed.";
  exit;
}

$id_alternatif = $_POST['id_alternatif'] ?? null;
$nilaiArray    = $_POST['nilai'] ?? [];

if (!$id_alternatif || !is_array($nilaiArray)) {
  http_response_code(400);
  echo "Data tidak lengkap.";
  exit;
}

try {
  $conn->beginTransaction();

  foreach ($nilaiArray as $id_kriteria => $nilai) {
    // Validasi angka positif
    if (!is_numeric($nilai) || floatval($nilai) < 0) {
      throw new Exception("Nilai untuk kriteria ID $id_kriteria tidak valid.");
    }

    $nilai = round(floatval($nilai), 2); // dibulatkan ke 2 desimal

    // Cek apakah data sudah ada
    $cek = $conn->prepare("SELECT COUNT(*) FROM nilai WHERE id_alternatif = ? AND id_kriteria = ?");
    $cek->execute([$id_alternatif, $id_kriteria]);
    $exists = $cek->fetchColumn() > 0;

    if ($exists) {
      // Update nilai
      $stmt = $conn->prepare("UPDATE nilai SET nilai = ? WHERE id_alternatif = ? AND id_kriteria = ?");
      $stmt->execute([$nilai, $id_alternatif, $id_kriteria]);
    } else {
      // Insert nilai baru
      $stmt = $conn->prepare("INSERT INTO nilai (id_alternatif, id_kriteria, nilai) VALUES (?, ?, ?)");
      $stmt->execute([$id_alternatif, $id_kriteria, $nilai]);
    }
  }

  $conn->commit();
  header("Location: ../../app/views/nilai/form.php?id=$id_alternatif&success=1");
  exit;

} catch (Exception $e) {
  $conn->rollBack();
  http_response_code(500);
  echo "Terjadi kesalahan: " . htmlspecialchars($e->getMessage());
  exit;
}
