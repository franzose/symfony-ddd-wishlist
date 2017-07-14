<?php

namespace Wishlist\Domain;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class AbstractId
{
    protected $id;

    private function __construct(UuidInterface $id)
    {
        $this->id = $id;
    }

    public static function fromString(string $id)
    {
        return new static(Uuid::fromString($id));
    }

    public static function next()
    {
        return new static(Uuid::uuid4());
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
