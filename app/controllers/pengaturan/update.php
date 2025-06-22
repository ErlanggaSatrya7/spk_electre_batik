<?php
require_once __DIR__ . '/../../../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

try {
  // Validasi input wajib
  if (!isset($_POST['id'], $_POST['nama_aplikasi'], $_POST['deskripsi'], $_POST['versi'])) {
    throw new Exception("Data tidak lengkap.");
  }

  $id          = $_POST['id'];
  $nama        = trim($_POST['nama_aplikasi']);
  $deskripsi   = trim($_POST['deskripsi']);
  $versi       = trim($_POST['versi']);
  $maintenance = isset($_POST['maintenance']) ? 1 : 0;

  // Ambil data lama
  $stmt = $conn->prepare("SELECT * FROM pengaturan WHERE id = ?");
  $stmt->execute([$id]);
  $data_lama = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$data_lama) {
    throw new Exception("Pengaturan tidak ditemukan.");
  }

  $logo = $data_lama['logo'];

  // Jika ada upload logo baru
  if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType     = mime_content_type($_FILES['logo']['tmp_name']);
    $ext          = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

    if (!in_array($fileType, $allowedTypes)) {
      throw new Exception("File logo harus berupa gambar (jpg, png, gif, webp).");
    }

    // Simpan logo baru
    $filename   = 'logo_' . time() . '.' . $ext;
    $uploadPath = __DIR__ . '/../../../assets/logo/' . $filename;

    // Hapus logo lama jika ada
    if (!empty($logo)) {
      $oldLogoPath = __DIR__ . '/../../../assets/logo/' . $logo;
      if (file_exists($oldLogoPath)) unlink($oldLogoPath);
    }

    move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath);
    $logo = $filename;
  }

  // Simpan perubahan ke pengaturan
  $stmt = $conn->prepare("UPDATE pengaturan SET nama_aplikasi=?, deskripsi=?, versi=?, logo=?, maintenance=? WHERE id=?");
  $stmt->execute([$nama, $deskripsi, $versi, $logo, $maintenance, $id]);

  // Simpan log perubahan
  $log = $conn->prepare("INSERT INTO log_pengaturan (id_user, isi_lama, waktu) VALUES (?, ?, NOW())");
  $log->execute([$_SESSION['user_id'], json_encode($data_lama)]);

  $_SESSION['success'] = "Pengaturan berhasil diperbarui.";
  header("Location: ../../views/pengaturan/index.php");
  exit;

} catch (Exception $e) {
  $_SESSION['error'] = "Gagal: " . $e->getMessage();
  header("Location: ../../views/pengaturan/index.php");
  exit;
}
