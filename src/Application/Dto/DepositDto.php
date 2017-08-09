<?php

namespace Wishlist\Application\Dto;

class DepositDto implements \JsonSerializable
{
    public $id;
    public $amount;
    public $currency;
    public $createdAt;

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'createdAt' => $this->createdAt
        ];
    }
}
