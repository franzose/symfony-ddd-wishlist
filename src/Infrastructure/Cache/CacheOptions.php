<?php

namespace Wishlist\Infrastructure\Cache;

final class CacheOptions
{
    private $key;
    private $lifetime;
    private $tags;

    public function __construct(string $key, int $lifetime, array $tags = [])
    {

        $this->key = $key;
        $this->lifetime = $lifetime;
        $this->tags = $tags;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}
