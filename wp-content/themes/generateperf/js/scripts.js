document.addEventListener("DOMContentLoaded", function() {
  
  const handleLinkClick = function(e) {
      if (!e.target.href) {
          this.querySelector("a").click();
      }
  };

  const handleGotoClick = function(e) {
      const goto = window.atob(this.dataset.goto);
      if (e.ctrlKey || e.metaKey || this.dataset.external) {
          window.open(goto, "_blank").focus();
      } else {
          window.location.href = goto;
      }
  };

  document.querySelectorAll(".wp-block-latest-posts li, .generate-columns-container article, .wp-block-post, .site-branding-container, .gb-query-loop-item, .nav-previous, .nav-next").forEach(el => {
      el.addEventListener("click", handleLinkClick, {passive: true});
  });

  document.querySelectorAll("[data-goto]").forEach(el => {
      el.addEventListener("click", handleGotoClick, {passive: true});
  });



    const nextButtons = document.querySelectorAll('[data-next-tab]');
    nextButtons.forEach(button => {
      button.addEventListener('click', function() {
        const currentTab = document.querySelector('.gb-block-is-current');
        const nextTabButton = currentTab && currentTab.nextElementSibling;
        if (nextTabButton) {
          nextTabButton.click();
        }
      });
    });

    [].forEach.call(document.body.querySelectorAll(".site-footer .toggler"), function(el) {
      el.addEventListener("click", function(e) {
        this.closest(".toggler-container").classList.toggle("opened");
      }, {passive: true});
    });

    // when clicking on [data-open-main-menu] button, click on .top-menu > button
    document.body.querySelector('[data-open-main-menu]').addEventListener('click', function() {
      document.body.querySelector('.top-menu > button').click();

      // If there is no .polylang_langswitcher inside #modal-1-content, duplicate the DOM node at the beginning
      if (!document.body.querySelector('#modal-1-content .polylang_langswitcher')) {
        const langSwitcher = document.body.querySelector('.polylang_langswitcher');
        const modalContent = document.body.querySelector('#modal-1-content');
        modalContent.insertBefore(langSwitcher, modalContent.firstChild);
      }

    });

});