<?php

namespace Wishlist\Domain;

interface WishRepositoryInterface
{
    public function get(WishId $wishId): Wish;
    public function put(Wish $wish);
    public function getNextWishId(): WishId;
}
