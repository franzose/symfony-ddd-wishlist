;(function (window) {
    window.addEventListener('load', function () {
        let wishlist = document.getElementById('wishlist');

        wishlist.addEventListener('click', function (event) {
            let button = event.target;

            if (!button.classList.contains('js-publish-button')) {
                return;
            }

            let options = {
                method: 'PUT'
            };

            fetch(button.dataset.url, options)
                .then(response => response.json())
                .then(function (response) {
                    button.dataset.url = response.url;
                    button.innerHTML = response.label;

                    let method = response.published ? 'remove' : 'add';

                    button.closest('.js-wish').classList[method]('wishlist__wish--unpublished');
                })
                .catch(function (e) {
                    console.log(e);
                });
        });
    });
})(window);
