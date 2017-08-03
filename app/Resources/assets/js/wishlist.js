import Vue from 'vue';
import Pagination from './pagination';
import WishlistItem from './wishlist-item';
import Deposits from './wish-deposits';

export default {
    template:
        `
        <div>
            <table class="table">
                <caption class="table__caption">
                    <div class="table__caption-wrapper">
                        <div class="table__caption-text">{{ lang.title }}</div>
                        <pagination
                            route="wishlist.index"
                            :lang="lang"
                            :pagination="pagination" />
                    </div>
                </caption>
                <tbody>
                <tr is="wishlist-item"
                    v-for="wish in wishlist"
                    :lang="lang"
                    :wish="wish"
                    :key="wish.id"
                    @published="publish"
                    @unpublished="unpublish"
                    @chosen="showDeposits"></tr>
                </tbody>
            </table>
            <deposits
                :isActive="shouldShowDeposits"
                :wishName="chosenWishName"
                :deposits="deposits"
                @closed="hideDeposits" />
        </div>
        `,
    components: {
        pagination: Pagination,
        wishlistItem: WishlistItem,
        deposits: Deposits
    },
    data() {
        return {
            wishlist: [],
            pagination: {},
            lang: window.translations,
            shouldShowDeposits: false,
            chosenWishName: '',
            deposits: []
        }
    },
    beforeRouteEnter(to, from, next) {
        Vue.http.get(Routing.generate('wishlist.index', {
            page: to.query.page || 1
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
            page: to.query.page || 1
        }))
            .then(response => response.body)
            .then(response => {
                this.wishlist = response.wishes;
                this.pagination = response.pagination;

                next();
            });
    },
    methods: {
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
        showDeposits(wish) {
            this.shouldShowDeposits = true;
            this.chosenWishName = wish.name;
            this.deposits = wish.deposits;
        },
        hideDeposits() {
            this.shouldShowDeposits = false;

            setTimeout(() => {
                this.chosenWishName = '';
                this.deposits = [];
            }, 350);
        }
    }
};