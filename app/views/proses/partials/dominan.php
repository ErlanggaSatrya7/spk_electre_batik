<?php
require_once __DIR__ . '/../../../../config/db.php';
require_once __DIR__ . '/../../../../helpers/auth.php';

$pageTitle = "Threshold & Matriks Dominan";
include __DIR__ . '/../../layouts/header.php';
include __DIR__ . '/../../layouts/sidebar.php';
include __DIR__ . '/../../layouts/topbar.php';

// Ambil data dari database
$stmt = $conn->query("SELECT a.id AS id_alt, a.nama AS nama_alt, k.id AS id_kriteria, k.bobot, k.jenis, n.nilai
                      FROM nilai n
                      JOIN alternatif a ON n.id_alternatif = a.id
                      JOIN kriteria k ON n.id_kriteria = k.id
                      ORDER BY a.id, k.id");

$data = [];
$bobot = [];
$jenis = [];
$alt_list = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[$row['id_alt']][$row['id_kriteria']] = $row['nilai'];
    $bobot[$row['id_kriteria']] = $row['bobot'];
    $jenis[$row['id_kriteria']] = $row['jenis'];
    $alt_list[$row['id_alt']] = $row['nama_alt'];
}

// Hitung Concordance Matrix
$concordance = [];
$concordance_sum = 0;
$concordance_count = 0;

foreach ($data as $i => $nilai_i) {
    foreach ($data as $j => $nilai_j) {
        if ($i === $j) {
            $concordance[$i][$j] = 0;
            continue;
        }

        $totalBobot = 0;
        foreach ($bobot as $id_kriteria => $b) {
            if (
                ($jenis[$id_kriteria] === 'benefit' && $nilai_i[$id_kriteria] >= $nilai_j[$id_kriteria]) ||
                ($jenis[$id_kriteria] === 'cost' && $nilai_i[$id_kriteria] <= $nilai_j[$id_kriteria])
            ) {
                $totalBobot += $b;
            }
        }
        $concordance[$i][$j] = $totalBobot;
        $concordance_sum += $totalBobot;
        $concordance_count++;
    }
}

$threshold_concordance = $concordance_sum / $concordance_count;

// Hitung Discordance Matrix
$discordance = [];
$discordance_sum = 0;
$discordance_count = 0;

foreach ($data as $i => $nilai_i) {
    foreach ($data as $j => $nilai_j) {
        if ($i === $j) {
            $discordance[$i][$j] = 0;
            continue;
        }

        $pembilang = 0;
        $penyebut = 0;

        foreach ($bobot as $id_kriteria => $_) {
            $diff = abs($nilai_i[$id_kriteria] - $nilai_j[$id_kriteria]);
            $penyebut = max($penyebut, $diff);
            if (
                ($jenis[$id_kriteria] === 'benefit' && $nilai_i[$id_kriteria] < $nilai_j[$id_kriteria]) ||
                ($jenis[$id_kriteria] === 'cost' && $nilai_i[$id_kriteria] > $nilai_j[$id_kriteria])
            ) {
                $pembilang = max($pembilang, $diff);
            }
        }

        $d_value = $penyebut > 0 ? $pembilang / $penyebut : 0;
        $discordance[$i][$j] = $d_value;
        $discordance_sum += $d_value;
        $discordance_count++;
    }
}

$threshold_discordance = $discordance_sum / $discordance_count;

// Matriks dominasi
$matriks_dominan = [];
foreach ($data as $i => $row_i) {
    foreach ($data as $j => $row_j) {
        if ($i === $j) {
            $matriks_dominan[$i][$j] = "-";
        } else {
            $c = $concordance[$i][$j];
            $d = $discordance[$i][$j];
            $matriks_dominan[$i][$j] = ($c >= $threshold_concordance && $d <= $threshold_discordance) ? 1 : 0;
        }
    }
}
?>

<div class="flex-1 min-h-screen bg-gray-50 px-6 py-6">
  <div class="bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold text-indigo-700 mb-4">Matriks Dominan</h1>

    <p class="text-gray-600 mb-4">
      Threshold Concordance: <strong><?= number_format($threshold_concordance, 4) ?></strong><br>
      Threshold Discordance: <strong><?= number_format($threshold_discordance, 4) ?></strong>
    </p>

    <div class="overflow-auto mb-6">
      <table class="table-auto w-full text-sm text-center border">
        <thead>
          <tr class="bg-indigo-100 text-indigo-700">
            <th class="border px-4 py-2">Alternatif</th>
            <?php foreach ($alt_list as $j => $_): ?>
              <th class="border px-4 py-2">A<?= $j ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($matriks_dominan as $i => $row): ?>
            <tr class="bg-white hover:bg-gray-50">
              <td class="border px-4 py-2 font-medium text-left">A<?= $i ?></td>
              <?php foreach ($row as $val): ?>
                <td class="border px-4 py-2"><?= $val ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <a href="agregat.php" class="inline-block mt-4 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow">
      Lanjut ke Agregat Dominasi →
    </a>
  </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
