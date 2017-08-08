import Vue from 'vue';
import VueResource from 'vue-resource';
import VueRouter from 'vue-router';
import Notify from 'vue-notifyjs';
import Wishlist from './wishlist';

Vue.use(VueResource);
Vue.use(VueRouter);
Vue.use(Notify);

window.addEventListener('load', function () {
    const routes = [
        {
            path: Routing.generate('wishlist.index'),
            component: Wishlist
        }
    ];

    const router = new VueRouter({
        routes,
        mode: 'history',
        linkActiveClass: 'is-active',
        linkExactActiveClass: 'is-current'
    });

    new Vue({
        router,
        el: '#wishlist'
    });
});
