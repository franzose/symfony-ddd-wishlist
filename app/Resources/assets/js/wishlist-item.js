export default {
    template:
        `
        <tr :class="{ 'wishlist__wish js-wish': true, 'is-unpublished': !wish.isPublished }"
            :data-id="wish.id">
            <td class="wishlist__muted">{{ wish.createdAt }}</td>
            <td class="wishlist__name" @click="choose(wish)">{{ wish.name }}</td>
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
        choose(wish) {
            this.$emit('chosen', wish);
        },
        togglePublishedStatus(wish) {
            wish.isPublished = !wish.isPublished;

            this.$emit(
                wish.isPublished ? 'published' : 'unpublished',
                wish
            );
        }
    }
};