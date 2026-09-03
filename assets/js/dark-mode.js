// ============================================
// Dark/Light Mode Toggle
// ============================================

document.addEventListener("DOMContentLoaded", function () {
  const themeToggle = document.getElementById("themeToggle");
  const themeIcon = document.getElementById("themeIcon");
  const themeText = document.getElementById("themeText");

  // Check saved theme preference
  const savedTheme = localStorage.getItem("theme") || "light";
  applyTheme(savedTheme);

  if (themeToggle) {
    themeToggle.addEventListener("click", function (e) {
      e.preventDefault();
      const currentTheme = document.body.classList.contains("dark-mode")
        ? "dark"
        : "light";
      const newTheme = currentTheme === "dark" ? "light" : "dark";
      applyTheme(newTheme);
      localStorage.setItem("theme", newTheme);
    });
  }

  function applyTheme(theme) {
    if (theme === "dark") {
      document.body.classList.add("dark-mode");
      if (themeIcon) {
        themeIcon.className = "fas fa-sun me-2";
      }
      if (themeText) {
        themeText.textContent = "Light Mode";
      }
    } else {
      document.body.classList.remove("dark-mode");
      if (themeIcon) {
        themeIcon.className = "fas fa-moon me-2";
      }
      if (themeText) {
        themeText.textContent = "Dark Mode";
      }
    }
  }

  // System preference detection (optional)
  if (!localStorage.getItem("theme")) {
    const prefersDark = window.matchMedia(
      "(prefers-color-scheme: dark)",
    ).matches;
    if (prefersDark) {
      applyTheme("dark");
      localStorage.setItem("theme", "dark");
    }
  }
});

// Update meta tag for dark mode
function updateMetaThemeColor(theme) {
  const meta = document.querySelector('meta[name="theme-color"]');
  if (meta) {
    meta.content = theme === "dark" ? "#1a1a2e" : "#f8f9fa";
  }
}

// Watch for system theme changes
window
  .matchMedia("(prefers-color-scheme: dark)")
  .addEventListener("change", function (e) {
    if (!localStorage.getItem("theme")) {
      applyTheme(e.matches ? "dark" : "light");
    }
  });
