<?php

namespace Wishlist\Domain;

use Ramsey\Uuid\Uuid;

abstract class AbstractId
{
    protected $id;

    private function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public static function next()
    {
        return new static;
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function __toString(): string
    {
        return $this->getId();
    }
}
