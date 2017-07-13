<?php

namespace Wishlist\Domain;

use Webmozart\Assert\Assert;

final class WishName
{
    private $name;

    public function __construct(string $name)
    {
        Assert::notEmpty($name, 'Name must not be empty.');

        $this->name = $name;
    }

    public function getValue(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
