<?php

namespace Wishlist\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use InvalidArgumentException;
use Ramsey\Uuid\Doctrine\UuidType;
use Wishlist\Domain\DepositId;

class DepositIdType extends UuidType
{
    const NAME = 'deposit_id';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof DepositId) {
            return $value;
        }

        try {
            $uuid = DepositId::fromString($value);
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

        if ($value instanceof DepositId) {
            return (string) $value;
        }

        throw ConversionException::conversionFailed($value, static::NAME);
    }
}
