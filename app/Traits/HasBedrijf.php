<?php

namespace App\Traits;

use App\Models\Scopes\BedrijfScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasBedrijf
{
    /**
     * Boot the trait.
     */
    public static function bootHasBedrijf(): void
    {
        static::addGlobalScope(new BedrijfScope());
        /**
         * opstaart van event listener met anonyme functie
         */
        static::creating(function (Model $model) {
            if (\Illuminate\Support\Facades\Auth::hasUser() && !$model->bedrijf_id) {
                $model->bedrijf_id = \Illuminate\Support\Facades\Auth::user()->bedrijf_id;
            }
        });
    }

    /**
     * Get the bedrijf that owns the model.
     */
    public function bedrijf()
    {
        return $this->belongsTo(\App\Models\Bedrijf::class);
    }
}
