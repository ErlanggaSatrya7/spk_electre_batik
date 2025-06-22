<?php
session_start();
require_once '../../../config/db.php';

$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$logo       = $pengaturan['logo'] ?? 'logo-batik.png';
$bg         = $pengaturan['background_image'] ?? 'bg-login.png';
$judul      = $pengaturan['login_judul'] ?? 'Kontrol Penuh. Keamanan Maksimal.';
$deskripsi  = $pengaturan['login_deskripsi'] ?? 'Panel admin ini dirancang untuk performa dan keamanan dalam mengelola sistem SPK berbasis metode ELECTRE.';
?>

<!DOCTYPE html>
<html lang="id" id="htmlRoot">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pengaturan['nama_aplikasi'] ?? 'Login Admin') ?></title>
  <link rel="icon" href="../../../assets/logo/<?= $logo ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.classList.add('dark');
    }
  </script>
  <style>
    body { font-family: 'Inter', sans-serif; transition: background 0.3s ease; }
    .fade-in { animation: fadeIn 1s ease forwards; opacity: 0; transform: translateY(20px); }
    .fade-slow { animation: fadeSlow 2s ease forwards; opacity: 0; transform: translateY(30px); }
    .glow { animation: glowText 1.5s ease-in-out infinite; }
    @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeSlow { to { opacity: 1; transform: translateY(0); } }
    @keyframes glowText { 0%,100%{opacity:1} 50%{opacity:.4} }
  </style>
</head>
<body class="min-h-screen bg-white dark:bg-gray-900 text-gray-800 dark:text-white flex">

  <!-- Splash Screen -->
  <div id="splash" class="fixed inset-0 z-50 bg-white dark:bg-gray-900 flex items-center justify-center transition duration-700">
    <div class="text-center">
      <img src="../../../assets/logo/<?= $logo ?>" class="h-20 w-20 animate-spin mx-auto mb-4" alt="Splash">
      <p class="text-sm text-gray-600 dark:text-gray-300 glow">Memuat sistem...</p>
    </div>
  </div>

  <!-- Theme Toggle -->
  <div class="fixed top-4 right-4 z-40">
    <button id="toggleTheme" class="bg-white dark:bg-gray-800 text-indigo-600 dark:text-yellow-300 border px-3 py-1 rounded shadow text-sm">
      <span id="themeIcon">🌙</span>
    </button>
  </div>

  <!-- Login Form -->
  <div class="w-full lg:w-1/2 flex items-center justify-center px-6 lg:px-24 py-12">
    <div class="w-full max-w-md fade-in">
      <div class="text-center mb-6">
        <img src="../../../assets/logo/<?= $logo ?>" alt="Logo" class="h-16 mx-auto mb-4">
        <h1 class="text-2xl font-bold tracking-wide text-gray-900 dark:text-white">
          <?= strtoupper($pengaturan['nama_aplikasi'] ?? 'ADMIN PANEL') ?>
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($pengaturan['deskripsi'] ?? '') ?></p>
      </div>

      <div id="alertBox" class="hidden bg-red-100 border border-red-300 text-red-700 p-3 mb-4 rounded text-sm shadow dark:bg-red-900 dark:border-red-700 dark:text-red-300"></div>

      <form id="ajaxLoginForm" class="space-y-5">
        <div>
          <label class="block text-sm font-medium mb-1">Username</label>
          <input type="text" name="username" id="username"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:ring-2 focus:ring-indigo-600 text-sm dark:bg-gray-800" required>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Password</label>
          <input type="password" name="password" id="password"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:ring-2 focus:ring-indigo-600 text-sm dark:bg-gray-800" required>
        </div>
        <button type="submit" id="submitBtn"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-md text-sm flex justify-center items-center gap-2 transition">
          <svg id="spinner" class="hidden animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
          </svg>
          <span id="btnText">Masuk sebagai Admin</span>
        </button>
      </form>

      <div class="text-xs text-center text-gray-400 dark:text-gray-500 mt-6">
        Hak akses administrator • Versi <?= htmlspecialchars($pengaturan['versi'] ?? '1.0') ?>
      </div>
    </div>
  </div>

  <!-- Visual Panel -->
  <div class="hidden lg:block lg:w-1/2 relative">
    <div class="absolute inset-0 bg-indigo-900 bg-opacity-70 z-10 flex items-center justify-center text-center px-12">
      <div class="fade-slow">
        <h2 class="text-white text-3xl font-extrabold leading-snug mb-4 glow"><?= htmlspecialchars($judul) ?></h2>
        <p class="text-indigo-100 text-sm max-w-md mx-auto glow"><?= htmlspecialchars($deskripsi) ?></p>
      </div>
    </div>
    <div class="absolute inset-0 z-0 bg-cover bg-center" style="background-image: url('../../../assets/images/<?= $bg ?>');"></div>
  </div>

  <script>
    // Splash screen
    window.addEventListener("DOMContentLoaded", () => {
      setTimeout(() => {
        document.getElementById("splash").classList.add("opacity-0", "pointer-events-none");
      }, 1000);
    });

    // Dark mode toggle
    const html = document.getElementById("htmlRoot");
    const toggle = document.getElementById("toggleTheme");
    const icon = document.getElementById("themeIcon");
    if (localStorage.getItem("theme") === "dark") {
      html.classList.add("dark"); icon.textContent = "☀️";
    }
    toggle.addEventListener("click", () => {
      html.classList.toggle("dark");
      const isDark = html.classList.contains("dark");
      localStorage.setItem("theme", isDark ? "dark" : "light");
      icon.textContent = isDark ? "☀️" : "🌙";
    });

    // AJAX Login
    document.getElementById("ajaxLoginForm").addEventListener("submit", async function (e) {
      e.preventDefault();
      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();
      const alertBox = document.getElementById("alertBox");
      const btn = document.getElementById("submitBtn");
      const spinner = document.getElementById("spinner");
      const btnText = document.getElementById("btnText");

      if (!username || !password || username.length < 3 || password.length < 5) {
        alertBox.textContent = "Isi username & password yang valid!";
        alertBox.classList.remove("hidden");
        setTimeout(() => alertBox.classList.add("hidden"), 2500);
        return;
      }

      btn.disabled = true;
      spinner.classList.remove("hidden");
      btnText.textContent = "Memproses...";

      try {
        const res = await fetch("../../../app/controllers/AuthController.php?action=ajaxLogin", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ username, password })
        });
        const data = await res.json();
        if (data.success) {
          btnText.textContent = "Berhasil login...";
          spinner.classList.remove("hidden");
          setTimeout(() => window.location.href = "../dashboard/index.php", 1200);
        } else {
          alertBox.textContent = data.message || "Login gagal.";
          alertBox.classList.remove("hidden");
          setTimeout(() => alertBox.classList.add("hidden"), 2500);
          btn.disabled = false;
          spinner.classList.add("hidden");
          btnText.textContent = "Masuk sebagai Admin";
        }
      } catch (err) {
        alertBox.textContent = "Terjadi kesalahan koneksi.";
        alertBox.classList.remove("hidden");
        setTimeout(() => alertBox.classList.add("hidden"), 2500);
        btn.disabled = false;
        spinner.classList.add("hidden");
        btnText.textContent = "Masuk sebagai Admin";
      }
    });
  </script>
</body>
</html>
