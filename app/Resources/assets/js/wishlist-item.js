export default {
    template:
        `
        <tr :class="{ 'wishlist__wish js-wish': true, 'wishlist__wish--unpublished': !wish.isPublished }"
            :data-id="wish.id">
            <td class="wishlist__muted">{{ wish.createdAt }}</td>
            <td class="wishlist__name">{{ wish.name }}</td>
            <td>{{ wish.fund }} {{ lang.of }} {{ wish.price }}</td>
            <td class="wishlist__muted">{{ wish.currency }}</td>
            <td>
                <button
                    type="button"
                    class="wishlist__publish-button"
                    data-url="#"
                    @click="togglePublishedStatus(wish, $event)">
                    {{ wish.isPublished ? lang.unpublish : lang.publish }}
                </button>
            </td>
        </tr>
        `,
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
};