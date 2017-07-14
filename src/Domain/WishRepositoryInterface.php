<?php

namespace Wishlist\Domain;

interface WishRepositoryInterface
{
    public function getNextWishId(): WishId;
}
