import Deposit from './wish-deposit';

export default {
    template:
        `
        <div class="wish-deposits" :class="{ 'is-active': isActive }">
            <table class="wish-deposits__table table">
                <caption class="table__caption">
                    <div class="table__caption-wrapper">
                        <div class="table__caption-text">Deposits of ‘{{ wishName }}’</div>
                        <svg @click="close" class="table__close" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"/></svg>
                    </div>
                </caption>
                <tbody class="wish-deposits__rows">
                <tr is="deposit"
                    v-for="deposit in deposits"
                    :lang="lang"
                    :deposit="deposit"
                    :key="deposit.id"></tr>
                </tbody>
            </table>
        </div>
        `,
    components: {
        deposit: Deposit
    },
    props: [
        'wishName',
        'deposits',
        'isActive'
    ],
    data() {
        return {
            lang: window.translations
        }
    },
    methods: {
        close() {
            this.$emit('closed');
        }
    }
};