<?php

namespace Wishlist\Domain;

interface WishRepositoryInterface
{
    public function get(WishId $wishId): Wish;
    public function put(Wish $wish);
    public function slice(int $offset, int $limit): array;
    public function has(Wish $wish): bool;
    public function hasWishWithId(WishId $wishId): bool;
    public function count(): int;
    public function getNextWishId(): WishId;
}
