const progressJS = {
  defaults: {
    attach: null,
    round: 2
  },
  start: function(configs = {}) {
    const { attach: attachSelector, round: roundto } = { ...this.defaults, ...configs };
    const progressJSelem = document.createElement("div");
    progressJSelem.classList.add("progress-bar");
    document.body.appendChild(progressJSelem);
    const attachElem = attachSelector ? document.querySelector(attachSelector) : null;
    document.addEventListener("scroll", (e) => {
      const maxHeight = document.body.scrollHeight;
      const sizeHeight = window.innerHeight;
      const scrolls = window.scrollY;
      const percentageValue = (scrolls / (maxHeight - sizeHeight)) * 100;
      const formattedPercentage = percentageValue.toFixed(roundto);
      progressJSelem.style.width = `${formattedPercentage}%`;
      if (attachElem) {
        attachElem.innerHTML = formattedPercentage;
      }
    }, { passive: true });
  }
}
progressJS.start();