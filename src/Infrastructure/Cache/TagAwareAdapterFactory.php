<?php

namespace Wishlist\Infrastructure\Cache;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

class TagAwareAdapterFactory
{
    public static function createAdapter(AdapterInterface $adapter): TagAwareAdapter
    {
        return new TagAwareAdapter($adapter, $adapter);
    }
}
