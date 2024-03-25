document.addEventListener("DOMContentLoaded", () => {
  const headers = document.querySelectorAll("h2[id^='toc-'], h3[id^='toc-']");
  const touchEvent = "click";
  headers.forEach((header) => {
    header.addEventListener(
      touchEvent,
      (e) => {
        const originalContent = header.textContent;
        const url = `${document.location.href.split("#")[0]}#${header.id}`;
        navigator.clipboard.writeText(url).then(() => {
          header.classList.add("copied");
          setTimeout(() => {
            header.classList.remove("copied");
            header.textContent = originalContent;
          }, 1500);
        });
      },
      { passive: true }
    );
  });
});