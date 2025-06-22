<?php
require_once __DIR__ . '/../../../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

try {
  $stmt = $conn->prepare("UPDATE pengaturan SET
    nama_aplikasi = 'Sistem SPK ELECTRE',
    deskripsi = 'Aplikasi SPK berbasis metode ELECTRE',
    versi = '1.0.0',
    logo = NULL,
    maintenance = 0
    WHERE id = 1");
  $stmt->execute();

  $_SESSION['success'] = "Pengaturan berhasil direset ke default.";
  header("Location: ../../views/pengaturan/index.php");
  exit;

} catch (Exception $e) {
  $_SESSION['error'] = "Gagal reset: " . $e->getMessage();
  header("Location: ../../views/pengaturan/index.php");
  exit;
}
