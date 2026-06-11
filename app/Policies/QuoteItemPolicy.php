<?php

namespace App\Policies;

use App\Models\QuoteItem;
use App\Models\User;

class QuoteItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, QuoteItem $quoteItem): bool
    {
        return $user->bedrijf_id === $quoteItem->quote->bedrijf_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, QuoteItem $quoteItem): bool
    {
        if (in_array($quoteItem->quote->status, [\App\Enums\QuoteStatus::ACCEPTED, \App\Enums\QuoteStatus::DECLINED])) {
            return false;
        }
        return $user->bedrijf_id === $quoteItem->quote->bedrijf_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, QuoteItem $quoteItem): bool
    {
        if (in_array($quoteItem->quote->status, [\App\Enums\QuoteStatus::ACCEPTED, \App\Enums\QuoteStatus::DECLINED])) {
            return false;
        }
        return $user->bedrijf_id === $quoteItem->quote->bedrijf_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, QuoteItem $quoteItem): bool
    {
        return $user->bedrijf_id === $quoteItem->quote->bedrijf_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, QuoteItem $quoteItem): bool
    {
        return $user->bedrijf_id === $quoteItem->quote->bedrijf_id;
    }
}
