import Vue from 'vue';
import VueResource from 'vue-resource';
import URLSearchParams from 'url-search-params';

Vue.use(VueResource);

window.addEventListener('load', function () {
    Vue.component('wishlist-pagination', {
        template: document.getElementById('wishlist-pagination').innerHTML,
        props: [
            'pagination',
            'lang'
        ],
        methods: {
            prev: function () {
                this.$emit('paginated', 'prev');
            },
            next: function () {
                this.$emit('paginated', 'next');
            }
        }
    });

    Vue.component('wishlist-item', {
        template: document.getElementById('wishlist-item').innerHTML,
        props: [
            'wish',
            'lang',
        ],
        methods: {
            togglePublishedStatus: function (wish) {
                wish.isPublished = !wish.isPublished;

                this.$emit(
                    wish.isPublished ? 'published' : 'unpublished',
                    wish
                );
            }
        }
    });

    const app = new Vue({
        el: '#app',
        data: {
            lang: {
                publish: 'Publish',
                unpublish: 'Unpublish',
                of: 'of'
            },
            pagination: {},
            wishlist: [],
            isActive: false
        },
        mounted: function () {
            this.$http.get(Routing.generate('wishlist.index', {
                page: new URLSearchParams(location.search).get('page')
            }))
                .then(response => response.body)
                .then(response => {
                    this.wishlist = response.wishes;
                    this.pagination = response.pagination;
                    this.isActive = true;
                })
        },
        methods: {
            goToPage: function (direction) {
                if (this.pagination.page <= 1 && direction === 'prev' ||
                    this.pagination.page >= this.pagination.totalPages && direction === 'next') {
                    return;
                }

                const page = direction === 'next'
                    ? this.pagination.page + 1
                    : this.pagination.page - 1;

                this.$http.get(Routing.generate('wishlist.index', { page }))
                    .then(response => response.body)
                    .then(response => {
                        this.wishlist = response.wishes;
                        this.pagination = response.pagination;
                    })
                    .catch(e => {})
            },
            publish: function (wish) {
                this.$http.put(Routing.generate('wishlist.wish.publish', {
                    wishId: wish.id
                }))
                    .catch(e => {
                        wish.isPublished = false;
                    });
            },
            unpublish: function (wish) {
                this.$http.put(Routing.generate('wishlist.wish.unpublish', {
                    wishId: wish.id
                }))
                    .catch(e => {
                        wish.isPublished = true;
                    });
            },
        }
    });
});
