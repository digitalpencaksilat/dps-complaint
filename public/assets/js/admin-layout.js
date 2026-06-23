(() => {
  const body = document.body;
  const toggle = document.getElementById("adminMenuToggle");
  const collapse = document.getElementById("adminSidebarCollapse");
  const overlay = document.getElementById("adminOverlay");
  const sidebar = document.getElementById("adminSidebar");

  if (!toggle || !overlay || !sidebar) return;

  const storageKey = "dpsComplainAdminSidebarMini";
  const isDesktop = () => window.matchMedia("(min-width: 992px)").matches;
  const applyMiniState = (enabled) => {
    body.classList.toggle("admin-sidebar-mini", enabled && isDesktop());

    if (!collapse) return;

    collapse.setAttribute("aria-expanded", enabled ? "false" : "true");
    collapse.setAttribute("title", enabled ? "Expand menu admin" : "Minimize menu admin");
  };

  applyMiniState(window.localStorage?.getItem(storageKey) === "1");

  const closeSidebar = () => body.classList.remove("sidebar-open");

  toggle.addEventListener("click", () => {
    body.classList.toggle("sidebar-open");
  });

  collapse?.addEventListener("click", () => {
    const nextState = !body.classList.contains("admin-sidebar-mini");
    window.localStorage?.setItem(storageKey, nextState ? "1" : "0");
    applyMiniState(nextState);
  });

  window.addEventListener("resize", () => {
    applyMiniState(window.localStorage?.getItem(storageKey) === "1");
  });

  overlay.addEventListener("click", closeSidebar);
  sidebar
    .querySelectorAll("a")
    .forEach((link) => link.addEventListener("click", closeSidebar));
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") closeSidebar();
  });
})();

(() => {
  document.addEventListener("submit", (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.dataset.confirm !== "true") return;
    if (form.dataset.confirmSubmitted === "true") return;

    event.preventDefault();

    const title = form.dataset.confirmTitle || "Konfirmasi aksi?";
    const text = form.dataset.confirmText || "Aksi ini tidak dapat dibatalkan.";
    const confirmButtonText = form.dataset.confirmButton || "Ya, lanjutkan";

    if (!window.Swal) {
      console.error("SweetAlert2 belum dimuat. Aksi dibatalkan agar tidak melewati dialog konfirmasi.");
      return;
    }

    window.Swal.fire({
      title,
      text,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc3545",
      cancelButtonColor: "#6c757d",
      confirmButtonText,
      cancelButtonText: "Batal",
      reverseButtons: true,
    }).then((result) => {
      if (!result.isConfirmed) return;

      form.dataset.confirmSubmitted = "true";
      form.submit();
    });
  });
})();


(() => {
  const actionDropdownSelector = ".admin-table-wrap .dropdown, .table-responsive .dropdown";

  const restoreMenu = (dropdown) => {
    const menu = dropdown?._dpsPortalMenu;
    if (!menu) return;

    dropdown.appendChild(menu);
    menu.classList.remove("admin-dropdown-portal");
    menu.style.removeProperty("position");
    menu.style.removeProperty("top");
    menu.style.removeProperty("left");
    menu.style.removeProperty("right");
    menu.style.removeProperty("bottom");
    menu.style.removeProperty("width");
    menu.style.removeProperty("z-index");
    menu.style.removeProperty("transform");

    delete dropdown._dpsPortalMenu;
    delete dropdown._dpsPortalUpdate;
  };

  const placeMenu = (dropdown) => {
    const menu = dropdown._dpsPortalMenu;
    const button = dropdown.querySelector('[data-bs-toggle="dropdown"]');
    if (!menu || !button) return;

    const buttonRect = button.getBoundingClientRect();
    const menuRect = menu.getBoundingClientRect();
    const margin = 8;
    const verticalGap = 4;
    const viewportWidth = document.documentElement.clientWidth;
    const viewportHeight = document.documentElement.clientHeight;
    const spaceBelow = viewportHeight - buttonRect.bottom;
    const openUp = spaceBelow < menuRect.height + verticalGap && buttonRect.top > menuRect.height;
    const top = openUp ? buttonRect.top - menuRect.height - verticalGap : buttonRect.bottom + verticalGap;
    const maxLeft = viewportWidth - menuRect.width - margin;
    const buttonCenter = buttonRect.left + buttonRect.width / 2;
    const rightAlignedLeft = buttonRect.right - menuRect.width;
    const centeredLeft = buttonCenter - menuRect.width / 2;
    const preferredLeft = Math.abs(rightAlignedLeft - buttonRect.left) > 80 ? centeredLeft : rightAlignedLeft;
    const left = Math.max(margin, Math.min(preferredLeft, maxLeft));

    menu.style.position = "fixed";
    menu.style.top = `${Math.max(margin, top)}px`;
    menu.style.left = `${left}px`;
    menu.style.right = "auto";
    menu.style.bottom = "auto";
    menu.style.zIndex = "2000";
    menu.style.transform = "none";
  };

  document.addEventListener("show.bs.dropdown", (event) => {
    const dropdown = event.target.closest(actionDropdownSelector);
    if (!dropdown) return;

    const menu = dropdown.querySelector(".dropdown-menu");
    if (!menu) return;

    dropdown._dpsPortalMenu = menu;
    menu.classList.add("admin-dropdown-portal");
    document.body.appendChild(menu);
    menu.removeAttribute("data-popper-placement");

    requestAnimationFrame(() => placeMenu(dropdown));

    dropdown._dpsPortalUpdate = () => placeMenu(dropdown);
    window.addEventListener("scroll", dropdown._dpsPortalUpdate, true);
    window.addEventListener("resize", dropdown._dpsPortalUpdate);
  });

  document.addEventListener("shown.bs.dropdown", (event) => {
    const dropdown = event.target.closest(actionDropdownSelector);
    if (dropdown) placeMenu(dropdown);
  });

  document.addEventListener("hide.bs.dropdown", (event) => {
    const dropdown = event.target.closest(actionDropdownSelector);
    if (!dropdown) return;

    if (dropdown._dpsPortalUpdate) {
      window.removeEventListener("scroll", dropdown._dpsPortalUpdate, true);
      window.removeEventListener("resize", dropdown._dpsPortalUpdate);
    }
  });

  document.addEventListener("hidden.bs.dropdown", (event) => {
    restoreMenu(event.target.closest(actionDropdownSelector));
  });
})();
