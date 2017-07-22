<?php

namespace Wishlist\Infrastructure\Persistence\Doctrine;

use Closure;
use Psr\Cache\CacheItemPoolInterface;
use Wishlist\Domain\Wish;
use Wishlist\Domain\WishId;
use Wishlist\Domain\WishRepositoryInterface;

class CachingWishRepository implements WishRepositoryInterface
{
    private $repository;
    private $cache;

    public function __construct(WishRepositoryInterface $repository, CacheItemPoolInterface $cache)
    {
        $this->repository = $repository;
        $this->cache = $cache;
    }

    public function get(WishId $wishId): Wish
    {
        return $this->fetch(function () use ($wishId) {
            return $this->repository->get($wishId);
        }, 'wishlist.wish.' . $wishId->getId(), 5 * 60);
    }

    public function put(Wish $wish)
    {
        return $this->repository->put($wish);
    }

    public function slice(int $offset, int $limit): array
    {
        return $this->fetch(function () use ($offset, $limit) {
            return $this->repository->slice($offset, $limit);
        }, sprintf('wishlist.slice.%d.%d', $offset, $limit), 5 * 60);
    }

    public function contains(Wish $wish): bool
    {
        return $this->fetch(function () use ($wish) {
            return $this->repository->contains($wish);
        }, sprintf('wishlist.contains.%s', $wish->getId()->getId()), 5 * 60);
    }

    public function containsId(WishId $wishId): bool
    {
        return $this->fetch(function () use ($wishId) {
            return $this->repository->containsId($wishId);
        }, sprintf('wishlist.contains.%s', $wishId->getId()), 5 * 60);
    }

    public function count(): int
    {
        return $this->fetch(function () {
            return $this->repository->count();
        }, 'wishlist.count', 5 * 60);
    }

    public function getNextWishId(): WishId
    {
        return $this->repository->getNextWishId();
    }

    private function fetch(Closure $fetch, string $cacheKey, int $lifetime = 0)
    {
        $cacheItem = $this->cache->getItem($cacheKey);
        $cacheItem->expiresAfter($lifetime);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $results = $fetch();
        $cacheItem->set($results);
        $this->cache->save($cacheItem);

        return $results;
    }
}
