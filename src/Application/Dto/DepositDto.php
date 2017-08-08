<?php

namespace Wishlist\Application\Dto;

class DepositDto implements \JsonSerializable
{
    public $depositId;
    public $amount;
    public $currency;
    public $createdAt;

    public function jsonSerialize()
    {
        return [
            'depositId' => $this->depositId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'createdAt' => $this->createdAt
        ];
    }
}
