<?php

namespace Wishlist\Infrastructure\Validation;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationListTransformer implements ConstraintViolationListTransformerInterface
{
    public function toArray(ConstraintViolationListInterface $violations): array
    {
        $result = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $property = trim($violation->getPropertyPath(), '[]');

            $result[$property] = $violation->getMessage();
        }

        return $result;
    }
}
