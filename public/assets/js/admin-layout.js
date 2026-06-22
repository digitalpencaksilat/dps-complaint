(() => {
  const body = document.body;
  const toggle = document.getElementById("adminMenuToggle");
  const overlay = document.getElementById("adminOverlay");
  const sidebar = document.getElementById("adminSidebar");

  if (!toggle || !overlay || !sidebar) return;

  const closeSidebar = () => body.classList.remove("sidebar-open");

  toggle.addEventListener("click", () => {
    body.classList.toggle("sidebar-open");
  });

  overlay.addEventListener("click", closeSidebar);
  sidebar
    .querySelectorAll("a")
    .forEach((link) => link.addEventListener("click", closeSidebar));
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") closeSidebar();
  });
})();
