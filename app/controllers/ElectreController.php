<?php
require_once '../../config/db.php';

// Ambil data
$alternatif = $conn->query("SELECT * FROM alternatif")->fetchAll(PDO::FETCH_ASSOC);
$kriteria   = $conn->query("SELECT * FROM kriteria")->fetchAll(PDO::FETCH_ASSOC);

$data = [];
foreach ($alternatif as $alt) {
  foreach ($kriteria as $kri) {
    $stmt = $conn->prepare("SELECT nilai FROM nilai WHERE id_alternatif = ? AND id_kriteria = ?");
    $stmt->execute([$alt['id'], $kri['id']]);
    $nilai = $stmt->fetchColumn();
    $data[$alt['id']][$kri['id']] = floatval($nilai);
  }
}

// Step 1: Normalisasi
$normalisasi = [];
foreach ($kriteria as $kri) {
  $sum = 0;
  foreach ($alternatif as $alt) {
    $sum += pow($data[$alt['id']][$kri['id']], 2);
  }
  $sqrtSum = sqrt($sum);
  foreach ($alternatif as $alt) {
    $normalisasi[$alt['id']][$kri['id']] = $data[$alt['id']][$kri['id']] / $sqrtSum;
  }
}

// Step 2: Pembobotan
$terbobot = [];
foreach ($alternatif as $alt) {
  foreach ($kriteria as $kri) {
    $terbobot[$alt['id']][$kri['id']] = $normalisasi[$alt['id']][$kri['id']] * $kri['bobot'];
  }
}

// Simpan hasil ke session untuk ditampilkan di views
session_start();
$_SESSION['electre']['alternatif'] = $alternatif;
$_SESSION['electre']['kriteria'] = $kriteria;
$_SESSION['electre']['terbobot'] = $terbobot;

header("Location: ../../app/views/proses/index.php");
exit;
