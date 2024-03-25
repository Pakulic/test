"use strict";
function initAcc(elem) {
  document.addEventListener("click", function (e) {
    if (!e.target.matches(elem + " .faq-question")) return;
    else {
      if (!e.target.parentElement.classList.contains("active")) {
        var elementList = document.querySelectorAll(elem + " .faq-container");
        Array.prototype.forEach.call(elementList, function (e) {
          e.classList.remove("active");
        });
        e.target.parentElement.classList.add("active");
      } else {
        e.target.parentElement.classList.remove("active");
      }
    }
  });
}

initAcc(".faq-block");
