<!-- <div class="fixed top-4 right-4 z-40">
  <button id="toggleTheme" class="bg-white dark:bg-gray-800 text-indigo-600 dark:text-yellow-300 border px-3 py-1 rounded shadow text-sm">
    <span id="themeIcon">🌙</span>
  </button>
</div>
<script>
  const html = document.getElementById("htmlRoot");
  const toggle = document.getElementById("toggleTheme");
  const icon = document.getElementById("themeIcon");

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
</script> -->
