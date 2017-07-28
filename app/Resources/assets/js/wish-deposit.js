export default {
    template:
        `
            <tr :data-id="deposit.id">
            <td>{{ deposit.amount }}</td>
            <td class="wishlist__muted">{{ deposit.currency }}</td>
            <td class="wishlist__muted">{{ deposit.createdAt }}</td>
        </tr>
        `,
    props: [
        'deposit'
    ]
};