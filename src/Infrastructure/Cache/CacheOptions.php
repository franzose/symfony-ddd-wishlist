<?php

namespace Wishlist\Infrastructure\Cache;

use Webmozart\Assert\Assert;

final class CacheOptions
{
    private $key;
    private $lifetime;
    private $tags;

    public function __construct(string $key, int $lifetime, array $tags = [])
    {
        Assert::notEmpty($key);
        Assert::greaterThanEq(0, $lifetime);

        if (!empty($tags)) {
            Assert::allStringNotEmpty($tags);
        }

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
