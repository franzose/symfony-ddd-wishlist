export default {
    template:
        `
        <div class="wishlist__pagination pagination">
            <div class="pagination__link pagination__link--prev"
                 @click="prev">
                <svg class="pagination__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path transform="scale(-1,1) translate(-24,0)" d="M13.025 1l-2.847 2.828 6.176 6.176h-16.354v3.992h16.354l-6.176 6.176 2.847 2.828 10.975-11z"></path></svg>
            </div>
            {{ pagination.startIndex }}-{{ pagination.endIndex }}
            {{ lang.of }}
            {{ pagination.total }}
            {{ lang.total }}
            <div class="pagination__link pagination__link--next"
                 @click="next">
                <svg class="pagination__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M13.025 1l-2.847 2.828 6.176 6.176h-16.354v3.992h16.354l-6.176 6.176 2.847 2.828 10.975-11z"></path></svg>
            </div>
        </div>
        `,
    props: [
        'pagination',
        'lang'
    ],
    methods: {
        prev() {
            this.$emit('paginated', 'prev');
        },
        next() {
            this.$emit('paginated', 'next');
        }
    }
};