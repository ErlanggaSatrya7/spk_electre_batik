<script>
  lucide?.createIcons();

  const html = document.getElementById("htmlRoot");
  const toggle = document.getElementById("toggleTheme");
  const icon = document.getElementById("themeIcon");

  if (localStorage.getItem("theme") === "dark") {
    html.classList.add("dark");
    if (icon) icon.textContent = "☀️";
  }

  toggle?.addEventListener("click", () => {
    html.classList.toggle("dark");
    const isDark = html.classList.contains("dark");
    localStorage.setItem("theme", isDark ? "dark" : "light");
    if (icon) icon.textContent = isDark ? "☀️" : "🌙";
  });
</script>
