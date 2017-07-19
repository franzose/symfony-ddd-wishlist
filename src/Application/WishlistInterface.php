<?php

namespace Wishlist\Application;

use Money\Money;
use Wishlist\Application\Dto\NewWishDto;

interface WishlistInterface
{
    public function addNewWish(NewWishDto $dto): string;
    public function deposit(string $wishId, int $amount): string;
    public function withdraw(string $wishId, string $depositId): Money;
    public function publish(string $wishId);
    public function unpublish(string $wishId);
    public function getWishesByPage(int $page, int $limit): array;
}
