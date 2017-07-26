import Vue from 'vue';
import WishlistPagination from './wishlist-pagination';
import WishlistItem from './wishlist-item';

export default {
    template:
        `
        <table>
            <caption class="wishlist__title">
                <div class="wishlist__title-wrapper">
                    <div class="wishlist__title-text">{{ lang.title }}</div>
                    <wishlist-pagination
                        :lang="lang"
                        :pagination="pagination"></wishlist-pagination>
                </div>
            </caption>
            <tbody>
            <tr is="wishlist-item"
                v-for="wish in wishlist"
                :lang="lang"
                :wish="wish"
                :key="wish.id"></tr>
            </tbody>
        </table>
        `,
    components: {
        wishlistPagination: WishlistPagination,
        wishlistItem: WishlistItem
    },
    data() {
        return {
            wishlist: [],
            pagination: {},
            lang: window.translations
        }
    },
    beforeRouteEnter(to, from, next) {
        Vue.http.get(Routing.generate('wishlist.index', {
            page: to.query.page
        }))
            .then(response => response.body)
            .then(response => {
                next(vm => {
                    vm.wishlist = response.wishes;
                    vm.pagination = response.pagination;
                    vm.$emit('activated');
                });
            });
    },
    beforeRouteUpdate (to, from, next) {
        Vue.http.get(Routing.generate('wishlist.index', {
            page: to.query.page
        }))
            .then(response => response.body)
            .then(response => {
                this.wishlist = response.wishes;
                this.pagination = response.pagination;

                next();
            });
    },
};