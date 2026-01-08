async function loadPartial(id, file) {
  const el = document.getElementById(id);
  if (!el) return;

  const res = await fetch(file);
  el.innerHTML = await res.text();
}

loadPartial("site-header", "/partials/header.html");
loadPartial("site-footer", "/partials/footer.html");
