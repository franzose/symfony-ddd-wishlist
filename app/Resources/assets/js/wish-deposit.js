import Vue from 'vue';

export default {
    template:
        `
        <tr>
            <td>{{ deposit.amount }}</td>
            <td class="wishlist__muted">{{ deposit.currency }}</td>
            <td class="wishlist__muted">{{ deposit.createdAt }}</td>
            <td class="wish-deposits__withdraw">
                <button
                    class="wish-deposits__withdraw-button button button--danger"
                    type="button"
                    @click="withdraw">
                    {{ lang.withdraw }}
                </button>
            </td>
        </tr>
        `,
    props: [
        'wish',
        'deposit'
    ],
    data() {
        return {
            lang: window.translations,
        }
    },
    methods: {
        withdraw() {
            const url = Routing.generate('wishlist.wish.withdraw', {
                wishId: this.wish.id,
                depositId: this.deposit.id
            });

            Vue.http.delete(url)
                .then(response => {
                    this.wish.deposits = this.wish.deposits.filter(deposit => {
                        return deposit.id !== this.deposit.id;
                    });

                    this.wish.fund = parseInt(this.wish.fund) - parseInt(this.deposit.amount);
                })
                .catch(response => {
                    this.$notify({
                        type: 'danger',
                        message: ('violations' in response.body
                                ? response.body.violations.depositId
                                : 'Internal Server Error'
                        )
                    });
                });
        }
    }
};