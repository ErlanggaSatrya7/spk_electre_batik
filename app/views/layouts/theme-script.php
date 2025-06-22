<!-- <script>
  const html = document.getElementById("htmlRoot");
  const toggle = document.getElementById("toggleTheme");
  const icon = document.getElementById("themeIcon");

  // Inisialisasi dark mode dari localStorage
  if (localStorage.getItem("theme") === "dark") {
    html.classList.add("dark");
    icon.textContent = "☀️";
  }

  // Toggle mode
  toggle.addEventListener("click", () => {
    html.classList.toggle("dark");
    const isDark = html.classList.contains("dark");
    localStorage.setItem("theme", isDark ? "dark" : "light");
    icon.textContent = isDark ? "☀️" : "🌙";
  });
</script> -->
