<?php

namespace App\Actions\Pages;

use App\Models\AuditLog;
use App\Models\Page;
use App\Support\TemplateBlocks;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class BuildPageBlocksAction
{
    /**
     * Validate, sanitize, and save blocks for a CMS Page.
     *
     * @param  Page                  $page
     * @param  array<int, mixed>     $blocks
     * @param  string|null           $userId
     * @return Page
     *
     * @throws ValidationException
     */
    public function execute(Page $page, array $blocks, ?string $userId = null): Page
    {
        if (!TemplateBlocks::validateBlocks($blocks)) {
            throw ValidationException::withMessages([
                'blocks' => 'Het blokkenschema is ongeldig. Elk blok moet een id, type en data array bevatten.',
            ]);
        }

        // Deep sanitize block strings against XSS while preserving structured data
        $sanitizedBlocks = $this->sanitizeBlocks($blocks);

        $oldBlocks = $page->blocks;

        $page->update([
            'blocks' => $sanitizedBlocks,
        ]);

        // Clear tenant-scoped page cache
        Cache::forget("tenant_{$page->tenant_id}_page_{$page->slug}");
        if ($page->is_homepage) {
            Cache::forget("tenant_{$page->tenant_id}_homepage");
        }

        // Record audit trail
        AuditLog::create([
            'tenant_id' => $page->tenant_id,
            'user_id' => $userId,
            'action' => 'page.blocks_updated',
            'auditable_type' => Page::class,
            'auditable_id' => $page->id,
            'old_values' => ['block_count' => is_array($oldBlocks) ? count($oldBlocks) : 0],
            'new_values' => ['block_count' => count($sanitizedBlocks)],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'KMO-Flow Block Engine',
            'created_at' => now(),
        ]);

        return $page;
    }

    /**
     * Recursively sanitize strings in block data to prevent XSS.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function sanitizeBlocks(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeBlocks($value);
            } elseif (is_string($value)) {
                // Strip tags except harmless formatting or sanitize special chars
                $sanitized[$key] = strip_tags($value, '<b><i><strong><em><br>');
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
