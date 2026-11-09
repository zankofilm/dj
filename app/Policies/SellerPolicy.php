<?php

namespace App\Policies;

use App\Models\Seller;
use App\Models\User;

class SellerPolicy
{
    public function view(User $user, Seller $seller): bool
    {
        return $seller->owner_user_id === $user->id || $user->hasPermission('sellers.review');
    }

    public function update(User $user, Seller $seller): bool
    {
        return $seller->owner_user_id === $user->id
            && in_array($seller->status, ['draft', 'changes_requested'], true);
    }

    public function review(User $user): bool
    {
        return $user->hasPermission('sellers.review');
    }
}
