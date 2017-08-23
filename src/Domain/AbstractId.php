<?php

namespace Wishlist\Domain;

use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Wishlist\Domain\Exception\InvalidIdentityException;

abstract class AbstractId
{
    protected $id;

    private function __construct(UuidInterface $id)
    {
        $this->id = $id;
    }

    public static function fromString(string $id)
    {
        try {
            return new static(Uuid::fromString($id));
        } catch (InvalidUuidStringException $exception) {
            throw new InvalidIdentityException($id);
        }
    }

    public static function next()
    {
        return new static(Uuid::uuid4());
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function equalTo(AbstractId $id): bool
    {
        return $this->getId() === $id->getId() &&
               get_class($this) === get_class($id);
    }

    public function __toString(): string
    {
        return $this->getId();
    }
}
