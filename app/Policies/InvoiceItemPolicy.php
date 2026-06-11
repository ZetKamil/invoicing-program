<?php

namespace App\Policies;

use App\Models\InvoiceItem;
use App\Models\User;

class InvoiceItemPolicy
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
    public function view(User $user, InvoiceItem $invoiceItem): bool
    {
        return $user->bedrijf_id === $invoiceItem->invoice->bedrijf_id;
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
    public function update(User $user, InvoiceItem $invoiceItem): bool
    {
        if (in_array($invoiceItem->invoice->status, [\App\Enums\InvoiceStatus::SENT, \App\Enums\InvoiceStatus::PAID, \App\Enums\InvoiceStatus::OVERDUE])) {
            return false;
        }
        return $user->bedrijf_id === $invoiceItem->invoice->bedrijf_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InvoiceItem $invoiceItem): bool
    {
        if (in_array($invoiceItem->invoice->status, [\App\Enums\InvoiceStatus::SENT, \App\Enums\InvoiceStatus::PAID, \App\Enums\InvoiceStatus::OVERDUE])) {
            return false;
        }
        return $user->bedrijf_id === $invoiceItem->invoice->bedrijf_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InvoiceItem $invoiceItem): bool
    {
        return $user->bedrijf_id === $invoiceItem->invoice->bedrijf_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InvoiceItem $invoiceItem): bool
    {
        return $user->bedrijf_id === $invoiceItem->invoice->bedrijf_id;
    }
}
