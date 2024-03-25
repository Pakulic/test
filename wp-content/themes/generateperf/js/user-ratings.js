(function() {
    document.querySelectorAll('.stars-rating').forEach(rating => {
        let postId = rating.dataset.postid;
        
        rating.addEventListener('click', function(e) {
            if(e.target.classList.contains('star') && !localStorage.getItem('voted_' + postId)) {
                let score = e.target.dataset.rating;
                
                fetch('/wp-json/generateperf/vote', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({postId, score})
                })
                .then(response => {
                    if(response.ok) {
                        return response.json();
                    }
                })
                .then(data => {
                    if(data.success) {
                        localStorage.setItem('voted_' + postId, true);
                        updateStars(rating, score);
                        updateVoteInfo(postId, generateperf_user_ratings.saved);
                    } else {
                        updateVoteInfo(postId, generateperf_user_ratings.error);
                    }
                })
                .catch(error => {
                    updateVoteInfo(postId, generateperf_user_ratings.error);
                });
            }
        });
    });

    function updateStars(rating, score) {
        rating.querySelectorAll('.star').forEach((star, index) => {
            star.classList.toggle('active', index < score);
        });
    }

    function updateVoteInfo(postId, message) {
        let voteInfoElem = document.querySelector('.vote-info');
        if (voteInfoElem) {
            voteInfoElem.textContent = message;
        }
    }
})();
