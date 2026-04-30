<?php

namespace App\Observers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaObserver
{
    /**
     * Handle the Media "force deleted" event.
     */
    public function forceDeleted(Media $media): void
    {
        if (blank($media->file_path)) {
            return;
        }

        $disk = $media->disk ?: 'public';

        if (Storage::disk($disk)->exists($media->file_path)) {
            Storage::disk($disk)->delete($media->file_path);
        }
    }
}
