document.addEventListener("DOMContentLoaded", function() {
  const touchEvent = "click";

  const shareUrls = {
      twitter: `https://twitter.com/intent/tweet?url=${generateperf_social_share.url}&text=${generateperf_social_share.title}`,
      x: `https://twitter.com/intent/tweet?url=${generateperf_social_share.url}&text=${generateperf_social_share.title}`,
      facebook: `https://www.facebook.com/sharer.php?u=${generateperf_social_share.url}&quote=${generateperf_social_share.title}`,
      flipboard: `https://share.flipboard.com/bookmarklet/popout?v=2&t=${new Date().getTime()}&url=${generateperf_social_share.url}&title=${generateperf_social_share.title}&utm_campaign=widgets&utm_medium=web&utm_source=profile_badge`,
      linkedin: `https://www.linkedin.com/shareArticle?url=${generateperf_social_share.url}&title=${generateperf_social_share.title}`,
      pinterest: `https://www.pinterest.fr/pin/create/button/?url=${generateperf_social_share.url}&description=${generateperf_social_share.title}&media=${generateperf_social_share.image}`,
      whatsapp: `https://wa.me/?text=${generateperf_social_share.title}%20:%20${generateperf_social_share.url}`,
      telegram: `https://t.me/share/url?url=${generateperf_social_share.url}&text=${generateperf_social_share.title}`,
      snapchat: `https://snapchat.com/scan?attachmentUrl=${generateperf_social_share.url}`,
      reddit: `http://www.reddit.com/submit?url=${generateperf_social_share.url}&title=${generateperf_social_share.title}`
  };

  const handleShareClick = function(e) {
      const service = this.dataset.shareUrl;
      let url = shareUrls[service];
      if (service === "native") {
        if (navigator.share) {
            navigator.share({
                title: generateperf_social_share.title,
                url: generateperf_social_share.url,
            }).then(() => {});
        } else {
            this.closest('.share-buttons-toggler').remove();
        }
      } else if (service === "copy-link") {
          if (!this.dataset.orig) this.dataset.orig = encodeURI(this.innerHTML);
          this.innerHTML = generateperf_social_share.copied;
          navigator.clipboard.writeText(decodeURIComponent(generateperf_social_share.url));
          setTimeout(() => { this.innerHTML = decodeURI(this.dataset.orig); }, 1500);
      } else if (url) {
          window.open(url, "_blank").focus();
      }
  };

  document.querySelectorAll("[data-share-url]").forEach(el => {
      el.addEventListener(touchEvent, handleShareClick, { passive: true });
  });
});
