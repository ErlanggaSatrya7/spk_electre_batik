<?php
require_once '../../../config/db.php';
require_once '../../../helpers/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $admin = $_SESSION['user']['nama'] ?? 'admin';

  // Ambil semua data alternatif
  $data = $conn->query("SELECT * FROM alternatif")->fetchAll(PDO::FETCH_ASSOC);

  if (count($data) > 0) {
    $conn->beginTransaction();

    try {
      // Backup ke tabel backup_alternatif
      $stmtBackup = $conn->prepare("
        INSERT INTO backup_alternatif (id, nama, gambar, deskripsi, kategori, backup_by, backup_at)
        VALUES (:id, :nama, :gambar, :deskripsi, :kategori, :backup_by, NOW())
      ");

      foreach ($data as $row) {
        $stmtBackup->execute([
          ':id' => $row['id'],
          ':nama' => $row['nama'],
          ':gambar' => $row['gambar'],
          ':deskripsi' => $row['deskripsi'],
          ':kategori' => $row['kategori'],
          ':backup_by' => $admin,
        ]);
      }

      // Catat log
      $log = $conn->prepare("INSERT INTO log_alternatif (aksi, keterangan, dilakukan_oleh, waktu) VALUES (?, ?, ?, NOW())");
      $log->execute(['hapus_semua', 'Menghapus seluruh data alternatif', $admin]);

      // Hapus dari tabel utama
      $conn->exec("DELETE FROM alternatif");

      $conn->commit();
      header("Location: ../../../app/views/alternatif/index.php?hapus_semua=success");
      exit;
    } catch (Exception $e) {
      $conn->rollBack();
      header("Location: ../../../app/views/alternatif/index.php?hapus_semua=fail");
      exit;
    }
  } else {
    header("Location: ../../../app/views/alternatif/index.php?hapus_semua=kosong");
    exit;
  }
} else {
  header("Location: ../../../app/views/alternatif/index.php");
  exit;
}
