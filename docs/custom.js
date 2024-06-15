(() => {
  let contentHeaders = document.querySelectorAll("main h2[id]");
  if (!document.querySelector('html').classList.contains('homepage') && contentHeaders) {
    const uri = new URL(location.href);
    contentHeaders.forEach((header) => {
      uri.hash = header.id;
      let link = document.createElement("a");
      link.classList.add("header-permalink");
      link.title = "Permalink";
      link.href = uri.toString();
      link.innerHTML = "&#182;";
      header.appendChild(link);
    });
  }
})();
