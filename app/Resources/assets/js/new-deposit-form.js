import Vue from 'vue';

export default {
    template:
    `
    <tr>
        <td>
            <div class="new-deposit-form table__padded">
                <input
                    class="new-deposit-form__value"
                    type="number"
                    :placeholder="lang.enterNewDeposit"
                    required
                    @input="changeValue">
                <button
                    class="new-deposit-form__submit button button--primary"
                    type="button"
                    :disabled="shouldButtonBeDisabled"
                    @click="makeDeposit">{{ lang.deposit }}</button>
            </div>
        </td>
    </tr>
    `,
    props: [
        'wishId'
    ],
    data() {
        return {
            lang: window.translations,
            amount: null,
            shouldButtonBeDisabled: false
        }
    },
    methods: {
        changeValue(event) {
            const amount = parseInt(event.target.value);

            this.amount = isNaN(amount) ? null : amount;
            this.shouldButtonBeDisabled = '' === event.target.value || 0 >= this.amount;
        },
        makeDeposit() {
            const url = Routing.generate('wishlist.wish.deposit', {
                wishId: this.wishId
            });

            const payload = new FormData();
            payload.append('amount', this.amount);

            Vue.http.post(url, payload)
                .then(response => response.body)
                .then(response => {
                    this.$emit('deposit', response.deposit);
                })
                .catch(response => {
                    this.$notify({
                        type: 'danger',
                        message: ('violations' in response.body
                            ? response.body.violations.amount
                            : 'Internal Server Error'
                        )
                    });
                });
        }
    }
};
