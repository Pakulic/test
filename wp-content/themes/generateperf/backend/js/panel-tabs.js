"use strict";
if ("querySelector" in document && "addEventListener" in window) {
  document.addEventListener("DOMContentLoaded", function () {
    var touchEvent = "click",
      tablinks = document.body.querySelectorAll(".tab-link"),
      tabzones = document.body.querySelectorAll(".tab-body");
    [].forEach.call(tablinks, function (el) {
      el.addEventListener(
        touchEvent,
        function (e) {
          e.preventDefault();
          e.stopPropagation();
          [].forEach.call(tablinks, function (tmp) {
            tmp.classList.remove("active");
          });
          [].forEach.call(tabzones, function (tmp) {
            tmp.classList.remove("active");
          });
          this.classList.add("active");
          document.body
            .querySelectorAll(this.getAttribute("href"))[0]
            .classList.add("active");
          if(this.dataset.submit == "true"){
            document.body
            .querySelectorAll(".submit-container")[0]
            .classList.remove("hidden");
          } else {
            document.body
            .querySelectorAll(".submit-container")[0]
            .classList.add("hidden");
          }
        },
      );
    });
  });
}
