<?php
// Ambil semua alternatif & kriteria
$alternatif = $conn->query("SELECT * FROM alternatif")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $conn->query("SELECT * FROM kriteria ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="text-lg font-semibold text-indigo-700 mb-3">1. Matriks Keputusan</h2>

<div class="overflow-x-auto">
  <table class="min-w-full text-sm border">
    <thead class="bg-indigo-100 text-indigo-700">
      <tr>
        <th class="px-4 py-2">Alternatif</th>
        <?php foreach ($kriteria as $k): ?>
          <th class="px-4 py-2"><?= htmlspecialchars($k['kode']) ?></th>
        <?php endforeach ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($alternatif as $a): ?>
        <tr class="border-b">
          <td class="px-4 py-2 font-medium"><?= htmlspecialchars($a['nama']) ?></td>
          <?php foreach ($kriteria as $k): ?>
            <?php
              $stmt = $conn->prepare("SELECT nilai FROM nilai WHERE id_alternatif = ? AND id_kriteria = ?");
              $stmt->execute([$a['id'], $k['id']]);
              $value = $stmt->fetchColumn();
            ?>
            <td class="px-4 py-2 text-center"><?= $value ?? '-' ?></td>
          <?php endforeach ?>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
