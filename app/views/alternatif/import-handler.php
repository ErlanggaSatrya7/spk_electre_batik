<?php
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
  die("Gagal upload file. Silakan coba lagi.");
}

$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
if (!in_array(strtolower($ext), ['xlsx', 'xls'])) {
  die("Format file tidak didukung. Hanya .xls dan .xlsx yang diperbolehkan.");
}

// Arahkan ke controller
require_once '../../../app/controllers/ImportAlternatifController.php';
