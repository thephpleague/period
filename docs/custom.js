(() => {
  let uri = new URL(location.href);
  let versions = document.querySelector('.versions');
  versions.addEventListener(
    'change',
    () => {
      uri.hash = '';
      uri.pathname = versions.options[versions.options.selectedIndex].dataset.url;
      location.href = uri.toString();
    }
  , false);

  document.querySelector('main').querySelectorAll("h2[id]").forEach((header) => {
    uri.hash = header.id;
    let link = document.createElement("a");
    link.className = "header-permalink";
    link.title = "Permalink";
    link.href = uri.toString();
    link.innerHTML = "&#182;";
    header.appendChild(link);
  });
})();