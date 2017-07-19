<?php

namespace Wishlist\Infrastructure\Persistence\Memory;

use Webmozart\Assert\Assert;
use Wishlist\Domain\Exception\WishNotFoundException;
use Wishlist\Domain\Wish;
use Wishlist\Domain\WishId;
use Wishlist\Domain\WishRepositoryInterface;

class WishRepository implements WishRepositoryInterface
{
    private $wishes = [];

    public function __construct(array $wishes = [])
    {
        Assert::allIsInstanceOf($wishes, Wish::class);

        $this->wishes = $wishes;
    }

    public function get(WishId $wishId): Wish
    {
        if (!$this->containsId($wishId)) {
            throw new WishNotFoundException($wishId);
        }

        return $this->wishes[$wishId->getId()];
    }

    public function put(Wish $wish)
    {
        $this->wishes[$wish->getId()->getId()] = $wish;
    }

    public function slice(int $offset, int $limit): array
    {
        return array_slice($this->wishes, $offset, $limit, true);
    }

    public function contains(Wish $wish): bool
    {
        return in_array($wish, $this->wishes, true);
    }

    public function containsId(WishId $wishId): bool
    {
        return array_key_exists($wishId->getId(), $this->wishes);
    }

    public function count(): int
    {
        return count($this->wishes);
    }

    public function getNextWishId(): WishId
    {
        return WishId::next();
    }
}
