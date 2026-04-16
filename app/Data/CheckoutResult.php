<?php

namespace App\Data;

use App\Models\Order;
use App\Models\User;

class CheckoutResult
{
    public function __construct(
        public readonly Order $order,
        public readonly ?User $authenticatedUser = null,
        public readonly bool $accountCreated = false,
        public readonly bool $resetLinkSent = false,
    ) {}

    public function withResetLinkSent(bool $resetLinkSent): self
    {
        return new self(
            order: $this->order,
            authenticatedUser: $this->authenticatedUser,
            accountCreated: $this->accountCreated,
            resetLinkSent: $resetLinkSent,
        );
    }
}
