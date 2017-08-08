<?php

namespace Wishlist\Infrastructure\Validation;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ConstraintViolationListTransformerInterface
{
    public function toArray(ConstraintViolationListInterface $violations): array;
}
