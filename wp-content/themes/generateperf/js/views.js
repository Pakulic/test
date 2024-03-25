const initializeViews = () => {
  const viewed_ls = localStorage.getItem("viewed");
  const viewed = viewed_ls ? JSON.parse(viewed_ls) : [];
  const post_id = document.querySelector("#main article").id.replace("post-", "");
  
  if (!viewed.includes(post_id)) {
      viewed.push(post_id);
      localStorage.setItem("viewed", JSON.stringify(viewed));
      
      fetch('/wp-json/generateperf/update_views/', {
          method: "POST",
          headers: {
              'Content-Type': 'application/json'
          },
          body: JSON.stringify({post_id})
      })
      .then(response => response.json())
      .then(data => {})
      .catch(error => {});
  }
};

if (document.readyState !== "loading") {
  setTimeout(initializeViews, 3000);
} else {
  document.addEventListener("DOMContentLoaded", () => setTimeout(initializeViews, 3000));
}
