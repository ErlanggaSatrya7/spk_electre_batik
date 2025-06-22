<?php
require_once '../../config/db.php';

$action = $_GET['action'] ?? '';

function toFloat($val) {
  // Hilangkan koma dan ubah ke float jika perlu
  $val = str_replace(',', '.', trim($val));
  return floatval($val);
}

if ($action === 'store') {
  $kode  = trim($_POST['kode'] ?? '');
  $nama  = trim($_POST['nama'] ?? '');
  $bobot = toFloat($_POST['bobot'] ?? 0);
  $tipe  = $_POST['tipe'] ?? 'benefit';

  if (empty($kode) || empty($nama) || !$bobot || !in_array($tipe, ['benefit', 'cost'])) {
    header('Location: ../views/kriteria/form.php?error=1');
    exit;
  }

  $stmt = $conn->prepare("INSERT INTO kriteria (kode, nama, bobot, tipe, created_at) VALUES (?, ?, ?, ?, NOW())");
  $stmt->execute([$kode, $nama, $bobot, $tipe]);

  header('Location: ../views/kriteria/index.php');
  exit;
}

if ($action === 'update' && isset($_GET['id'])) {
  $id    = $_GET['id'];
  $kode  = trim($_POST['kode'] ?? '');
  $nama  = trim($_POST['nama'] ?? '');
  $bobot = toFloat($_POST['bobot'] ?? 0);
  $tipe  = $_POST['tipe'] ?? 'benefit';

  if (empty($kode) || empty($nama) || !$bobot || !in_array($tipe, ['benefit', 'cost'])) {
    header('Location: ../views/kriteria/form.php?id=' . $id . '&error=1');
    exit;
  }

  $stmt = $conn->prepare("UPDATE kriteria SET kode = ?, nama = ?, bobot = ?, tipe = ? WHERE id = ?");
  $stmt->execute([$kode, $nama, $bobot, $tipe, $id]);

  header('Location: ../views/kriteria/index.php');
  exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
  $id = $_GET['id'];
  $stmt = $conn->prepare("DELETE FROM kriteria WHERE id = ?");
  $stmt->execute([$id]);

  header('Location: ../views/kriteria/index.php');
  exit;
}

// Aksi tidak dikenali
http_response_code(400);
echo "Aksi tidak valid atau parameter tidak lengkap.";
exit;
