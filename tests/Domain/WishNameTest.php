<?php

namespace Wishlist\Tests\Domain;

use Wishlist\Domain\WishName;
use PHPUnit\Framework\TestCase;

class WishNameTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldNotCreateWithEmptyString()
    {
        new WishName('');
    }
}
