<?php

namespace Wishlist\Tests\Domain;

use Wishlist\Domain\WishId;
use PHPUnit\Framework\TestCase;

class IdentityTest extends TestCase
{
    public function testFromValidString()
    {
        $string = '550e8400-e29b-41d4-a716-446655440000';
        $wishId = WishId::fromString($string);

        static::assertInstanceOf(WishId::class, $wishId);
        static::assertEquals($string, $wishId->getId());
        static::assertEquals($string, (string) $wishId);
    }

    public function testEquality()
    {
        $string = '550e8400-e29b-41d4-a716-446655440000';
        $wishIdOne = WishId::fromString($string);
        $wishIdTwo = WishId::fromString($string);
        $wishIdThree = WishId::next();

        static::assertTrue($wishIdOne->equalTo($wishIdTwo));
        static::assertFalse($wishIdTwo->equalTo($wishIdThree));
    }
}
