<?php

namespace Wishlist\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use InvalidArgumentException;
use Ramsey\Uuid\Doctrine\UuidType;
use Wishlist\Domain\WishId;

class WishIdType extends UuidType
{
    const NAME = 'wish_id';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof WishId) {
            return $value;
        }

        try {
            $uuid = WishId::fromString($value);
        } catch (InvalidArgumentException $exception) {
            throw ConversionException::conversionFailed($value, static::NAME);
        }

        return $uuid;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof WishId) {
            return (string) $value;
        }

        throw ConversionException::conversionFailed($value, static::NAME);
    }
}
