// assets/header.js
(function () {

  const media = window.matchMedia("(max-width: 900px)");

  /* ===== DROPDOWN (Leistungen) ===== */
  const dropdownItem = document.querySelector(".has-dropdown");
  const trigger = dropdownItem?.querySelector("a");

  trigger?.addEventListener("click", e => {
    if (!media.matches) return;

    e.preventDefault();
    e.stopPropagation();
    dropdownItem.classList.toggle("open");
  });

  /* ===== MOBILE BURGER ===== */
  const toggle = document.querySelector(".mobile-toggle");
  const nav = document.querySelector("header nav");

  toggle?.addEventListener("click", e => {
    e.stopPropagation();
    nav.classList.toggle("open");
  });

  document.addEventListener("click", () => {
    nav?.classList.remove("open");
  });

  nav?.addEventListener("click", e => {
    e.stopPropagation();
  });

})();
