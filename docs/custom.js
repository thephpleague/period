(() => {
  let versions = document.querySelector('.versions');
  versions.addEventListener(
    'change',
    () => location.href = versions.options[versions.options.selectedIndex].dataset.url
  , false);

  let uri = location.href.split('#', 2).pop();
  document.querySelector('main').querySelectorAll("h2[id]").forEach((header) => {
    let link = document.createElement("a");
    link.className = "header-permalink";
    link.title = "Permalink";
    link.href = uri + '#' + header.id;
    link.innerHTML = "&#182;";
    header.appendChild(link);
  });
})();