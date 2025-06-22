<?php
function logAktivitas($conn, $aktivitas) {
    if (!isset($_SESSION['user'])) return;
    $id_user = $_SESSION['user']['id'];
    $stmt = $conn->prepare("INSERT INTO log_aktivitas (id_user, aktivitas) VALUES (?, ?)");
    $stmt->execute([$id_user, $aktivitas]);
}
