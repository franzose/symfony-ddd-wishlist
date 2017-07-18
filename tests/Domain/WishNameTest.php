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

    public function testGetValueShouldReturnTheName()
    {
        $expected = 'A bucket of candies';
        $name = new WishName($expected);

        static::assertEquals($expected, $name->getValue());
        static::assertEquals($expected, (string) $name);
    }
}
