import Vue from 'vue';
import VueResource from 'vue-resource';
import URLSearchParams from 'url-search-params';
import WishlistPagination from './wishlist-pagination';
import WishlistItem from './wishlist-item';

Vue.use(VueResource);

window.addEventListener('load', function () {
    new Vue({
        el: '#wishlist',
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
        components: {
            wishlistPagination: WishlistPagination,
            wishlistItem: WishlistItem
        },
        mounted() {
            this.lang = translations;

            this.$http.get(Routing.generate('wishlist.index', {
                page: new URLSearchParams(location.search).get('page')
            }))
                .then(response => response.body)
                .then(response => {
                    this.wishlist = response.wishes;
                    this.pagination = response.pagination;
                    this.isActive = true;
                });
        },
        methods: {
            goToPage(direction) {
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
            publish(wish) {
                this.$http.put(Routing.generate('wishlist.wish.publish', {
                    wishId: wish.id
                }))
                    .catch(e => {
                        wish.isPublished = false;
                    });
            },
            unpublish(wish) {
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
