<?php
require_once '../../config/db.php';

$action = $_GET['action'] ?? '';

if ($action === 'store') {
  $nama = $_POST['nama'] ?? '';
  $kategori = $_POST['kategori'] ?? '';
  $asal = $_POST['asal_daerah'] ?? '';
  $deskripsi = $_POST['deskripsi'] ?? '';
  $gambarList = $_FILES['gambar'] ?? null;

  // Simpan data utama ke alternatif
  $stmt = $conn->prepare("INSERT INTO alternatif (nama, kategori, asal_daerah, deskripsi) VALUES (?, ?, ?, ?)");
  $stmt->execute([$nama, $kategori, $asal, $deskripsi]);
  $alternatifId = $conn->lastInsertId();

  // Simpan semua gambar ke gambar_alternatif
  if ($gambarList && !empty($gambarList['name'][0])) {
    for ($i = 0; $i < count($gambarList['name']); $i++) {
      if (!empty($gambarList['tmp_name'][$i])) {
        $ext = pathinfo($gambarList['name'][$i], PATHINFO_EXTENSION);
        $filename = uniqid('batik_') . '.' . $ext;
        $target = '../../assets/images/' . $filename;
        move_uploaded_file($gambarList['tmp_name'][$i], $target);

        $stmtGambar = $conn->prepare("INSERT INTO gambar_alternatif (alternatif_id, filename) VALUES (?, ?)");
        $stmtGambar->execute([$alternatifId, $filename]);
      }
    }
  }

  header('Location: ../views/alternatif/index.php');
  exit;
}

if ($action === 'update' && isset($_GET['id'])) {
  $id = $_GET['id'];
  $nama = $_POST['nama'] ?? '';
  $kategori = $_POST['kategori'] ?? '';
  $asal = $_POST['asal_daerah'] ?? '';
  $deskripsi = $_POST['deskripsi'] ?? '';
  $gambarList = $_FILES['gambar'] ?? null;

  // Update data utama
  $stmt = $conn->prepare("UPDATE alternatif SET nama = ?, kategori = ?, asal_daerah = ?, deskripsi = ? WHERE id = ?");
  $stmt->execute([$nama, $kategori, $asal, $deskripsi, $id]);

  // Upload gambar baru jika ada
  if ($gambarList && !empty($gambarList['name'][0])) {
    for ($i = 0; $i < count($gambarList['name']); $i++) {
      if (!empty($gambarList['tmp_name'][$i])) {
        $ext = pathinfo($gambarList['name'][$i], PATHINFO_EXTENSION);
        $filename = uniqid('batik_') . '.' . $ext;
        $target = '../../assets/images/' . $filename;
        move_uploaded_file($gambarList['tmp_name'][$i], $target);

        $stmtGambar = $conn->prepare("INSERT INTO gambar_alternatif (alternatif_id, filename) VALUES (?, ?)");
        $stmtGambar->execute([$id, $filename]);
      }
    }
  }

  header('Location: ../views/alternatif/index.php');
  exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
  $id = $_GET['id'];

  // Ambil semua gambar untuk dihapus dari folder
  $stmt = $conn->prepare("SELECT filename FROM gambar_alternatif WHERE alternatif_id = ?");
  $stmt->execute([$id]);
  $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($files as $file) {
    $path = '../../assets/images/' . $file['filename'];
    if (file_exists($path)) unlink($path);
  }

  // Hapus data gambar dan alternatif (gambar akan terhapus otomatis karena FK ON DELETE CASCADE)
  $stmt = $conn->prepare("DELETE FROM alternatif WHERE id = ?");
  $stmt->execute([$id]);

  header('Location: ../views/alternatif/index.php');
  exit;
}

// Default fallback
http_response_code(404);
echo "Aksi tidak dikenali atau parameter kurang.";
exit;
