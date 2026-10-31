<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(User::ROLES);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasAnyRole(User::ROLES);
    }
}
