<?php
session_start();
require_once '../../config/db.php';

$action = $_GET['action'] ?? '';

// Fungsi untuk merespons JSON (untuk AJAX)
function sendJson($status, $message)
{
  echo json_encode([
    'success' => $status,
    'message' => $message
  ]);
  exit;
}

if ($action === 'ajaxLogin') {
  // Cek jika input dalam format JSON
  $data = json_decode(file_get_contents("php://input"), true);
  $username = $data['username'] ?? '';
  $password = $data['password'] ?? '';

  if (!$username || !$password) {
    sendJson(false, "Username dan Password wajib diisi.");
  }

  try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Bandingkan password (hash SHA256)
    if ($user && hash('sha256', $password) === $user['PASSWORD']) {
      $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username']
      ];
      sendJson(true, "Login berhasil.");
    } else {
      sendJson(false, "Username atau Password salah.");
    }
  } catch (PDOException $e) {
    sendJson(false, "Kesalahan sistem.");
  }
}

if ($action === 'login') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && hash('sha256', $password) === $user['PASSWORD']) {
    $_SESSION['user'] = [
      'id' => $user['id'],
      'username' => $user['username']
    ];
    header('Location: ../views/dashboard/index.php');
    exit;
  } else {
    header('Location: ../views/auth/login.php?error=Username atau Password salah');
    exit;
  }
}

if ($action === 'logout') {
  session_destroy();
  header('Location: ../views/auth/login.php');
  exit;
}
