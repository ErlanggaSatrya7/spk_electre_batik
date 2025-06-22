<?php if (!isset($_SESSION)) session_start(); ?>

<div class="fixed top-0 left-0 w-64 h-screen bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 shadow-lg z-40">
  <div class="px-6 py-4 text-indigo-600 dark:text-white text-xl font-bold border-b dark:border-gray-700">
    SPK Batik
  </div>

  <nav class="mt-4 px-4 text-sm space-y-2">
    <?php
    $menuItems = [
      "Dashboard" => ["home", "dashboard/index.php"],
      "Alternatif" => ["layers", "alternatif/index.php"],
      "Kriteria" => ["list", "kriteria/index.php"],
      "Nilai" => ["edit-3", "nilai/index.php"],
      "Proses" => ["calculator", "proses/index.php"],
      "Hasil Ranking" => ["bar-chart-3", "hasil/index.php"],
      "Pengaturan" => ["settings", "pengaturan/index.php"],
    ];
    foreach ($menuItems as $title => [$icon, $href]) {
      $isActive = $pageTitle === $title;
      $classes = $isActive
        ? "bg-indigo-100 font-semibold text-indigo-700 dark:bg-gray-800 dark:text-white"
        : "text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-gray-800";
      echo "<a href='../$href' class='flex items-center py-2 px-3 rounded $classes'>
              <i data-lucide='$icon' class='w-4 h-4 mr-2'></i> $title
            </a>";
    }
    ?>
    <a href="../../../controllers/AuthController.php?action=logout"
       class="flex items-center py-2 px-3 rounded hover:bg-red-100 dark:hover:bg-red-900 text-red-600 mt-6">
      <i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Logout
    </a>
  </nav>
</div>
