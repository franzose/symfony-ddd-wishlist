<?php

namespace Wishlist\Infrastructure\Persistence\Common;

use Closure;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Wishlist\Domain\Wish;
use Wishlist\Domain\WishId;
use Wishlist\Domain\WishRepositoryInterface;
use Wishlist\Infrastructure\Cache\CacheOptions;

class CachingWishRepository implements WishRepositoryInterface
{
    private $repository;
    private $cache;

    public function __construct(WishRepositoryInterface $repository, TagAwareAdapterInterface $cache)
    {
        $this->repository = $repository;
        $this->cache = $cache;
    }

    public function get(WishId $wishId): Wish
    {
        $cacheOptions = new CacheOptions('wishlist.wish.' . $wishId->getId(), 5 * 60);

        return $this->fetch(function () use ($wishId) {
            return $this->repository->get($wishId);
        }, $cacheOptions);
    }

    public function put(Wish $wish)
    {
        $this->repository->put($wish);

        $this->cache->deleteItems($this->getKeysToExpire($wish));
        $this->cache->invalidateTags($this->getTagsToExpire());
    }

    private function getKeysToExpire(Wish $wish): array
    {
        return [
            'wishlist.wish.' . $wish->getId(),
            'wishlist.contains.' . $wish->getId(),
            'wishlist.count',
        ];
    }

    private function getTagsToExpire(): array
    {
        return [
            'wishlist.slice'
        ];
    }

    public function slice(int $offset, int $limit): array
    {
        $cacheOptions = new CacheOptions(
            sprintf('wishlist.slice.%d.%d', $offset, $limit),
            5 * 60,
            ['wishlist.slice']
        );

        return $this->fetch(function () use ($offset, $limit) {
            return $this->repository->slice($offset, $limit);
        }, $cacheOptions);
    }

    public function contains(Wish $wish): bool
    {
        $cacheOptions = new CacheOptions(
            sprintf('wishlist.contains.%s', $wish->getId()->getId()),
            5 * 60
        );

        return $this->fetch(function () use ($wish) {
            return $this->repository->contains($wish);
        }, $cacheOptions);
    }

    public function containsId(WishId $wishId): bool
    {
        $cacheOptions = new CacheOptions(
            sprintf('wishlist.contains.%s', $wishId->getId()),
            5 * 60
        );

        return $this->fetch(function () use ($wishId) {
            return $this->repository->containsId($wishId);
        }, $cacheOptions);
    }

    public function count(): int
    {
        $cacheOptions = new CacheOptions(
            'wishlist.count',
            5 * 60
        );

        return $this->fetch(function () {
            return $this->repository->count();
        }, $cacheOptions);
    }

    public function getNextWishId(): WishId
    {
        return $this->repository->getNextWishId();
    }

    private function fetch(Closure $fetch, CacheOptions $options)
    {
        $cacheItem = $this->cache->getItem($options->getKey());
        $cacheItem->expiresAfter($options->getLifetime());

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $results = $fetch();
        $cacheItem->tag($options->getTags());
        $cacheItem->set($results);
        $this->cache->save($cacheItem);

        return $results;
    }
}
