document.addEventListener("click", e => {
  const nav = document.querySelector("header nav");
  if (!nav) return;
  nav.classList.remove("open");
});

document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.querySelector(".mobile-toggle");
  const nav = document.querySelector("header nav");

  if (!toggle || !nav) return;

  toggle.addEventListener("click", e => {
    e.stopPropagation();
    nav.classList.toggle("open");
  });
});
