<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LayoutAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class LayoutAssetPreviewService
{
    public function __construct(
        protected LayoutAssetManager $layoutAssetManager
    ) {}

    public function buildPreviewUrl(LayoutAsset $layoutAsset, int $ttlMinutes = 720): string
    {
        return URL::temporarySignedRoute(
            'admin.appearance.layout-assets.preview',
            now()->addMinutes($ttlMinutes),
            ['layoutAsset' => $layoutAsset->id]
        );
    }

    public function hasValidSignature(Request $request): bool
    {
        return $request->hasValidSignatureWhileIgnoring(['dark']);
    }

    public function renderAssetHtml(LayoutAsset $layoutAsset): string
    {
        if (is_array($layoutAsset->content_raw) && !empty($layoutAsset->content_raw)) {
            return (string) ($this->layoutAssetManager->renderContent($layoutAsset->content_raw) ?? '');
        }

        return (string) ($layoutAsset->content_html ?? '');
    }

    public function buildViewData(LayoutAsset $layoutAsset, bool $isDark = false): array
    {
        return [
            'asset' => $layoutAsset,
            'contentHtml' => $this->renderAssetHtml($layoutAsset),
            'isDark' => $isDark,
        ];
    }
}
