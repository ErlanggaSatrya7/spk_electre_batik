<?php if (!isset($_SESSION)) session_start(); ?>

<header class="bg-white dark:bg-gray-800 shadow px-6 py-4 flex justify-between items-center border-b border-gray-200 dark:border-gray-700">
  <div class="flex items-center space-x-2">
    <img src="../../../assets/images/logo-batik.png" alt="Logo" class="w-8 h-8" />
    <h1 class="text-lg font-semibold text-indigo-600 dark:text-white">SPK Batik - ELECTRE</h1>
  </div>

  <div class="flex items-center gap-4">
    <!-- Toggle Dark Mode -->
    <button id="toggleTheme"
            class="text-sm text-indigo-600 dark:text-yellow-300 hover:underline focus:outline-none"
            title="Ganti Tema">
      <span id="themeIcon">🌙</span>
    </button>

    <!-- Profile Dropdown -->
    <div class="relative">
      <button onclick="toggleDropdown()" class="flex items-center space-x-2 focus:outline-none">
        <img src="../../../assets/images/profile.png" alt="Avatar" class="w-8 h-8 rounded-full border border-gray-300 dark:border-gray-600" />
        <span class="text-sm text-gray-700 dark:text-white font-medium">
          <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Admin') ?>
        </span>
        <svg class="w-4 h-4 text-gray-500 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <div id="dropdownMenu"
           class="hidden absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded shadow-lg border border-gray-100 dark:border-gray-700 z-50">
        <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Profil</a>
        <div class="border-t my-1 dark:border-gray-700"></div>
        <a href="/app/controllers/AuthController.php?action=logout"
           class="block px-4 py-2 text-sm text-red-600 hover:bg-red-100 dark:hover:bg-red-900">Logout</a>
      </div>
    </div>
  </div>

  <!-- Dropdown Script -->
  <script>
    function toggleDropdown() {
      const dropdown = document.getElementById("dropdownMenu");
      dropdown.classList.toggle("hidden");
    }

    document.addEventListener("click", function (event) {
      const dropdown = document.getElementById("dropdownMenu");
      const button = event.target.closest("button");
      if (!dropdown.contains(event.target) && !button) {
        dropdown.classList.add("hidden");
      }
    });

    // Dark Mode Script
    const html = document.getElementById("htmlRoot") || document.documentElement;
    const toggle = document.getElementById("toggleTheme");
    const icon = document.getElementById("themeIcon");

    // Inisialisasi tema
    if (localStorage.getItem("theme") === "dark") {
      html.classList.add("dark");
      icon.textContent = "☀️";
    }

    toggle.addEventListener("click", () => {
      html.classList.toggle("dark");
      const isDark = html.classList.contains("dark");
      localStorage.setItem("theme", isDark ? "dark" : "light");
      icon.textContent = isDark ? "☀️" : "🌙";
    });
  </script>
</header>
