<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LayoutAsset;
use App\Services\LayoutAssetPreviewService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LayoutAssetPreviewController extends Controller
{
    public function show(Request $request, LayoutAsset $layoutAsset, LayoutAssetPreviewService $previewService): View
    {
        abort_unless($previewService->hasValidSignature($request), 403);

        return view(
            'admin.appearance.layout-asset-preview',
            $previewService->buildViewData(
                $layoutAsset,
                filter_var((string) $request->query('dark', '0'), FILTER_VALIDATE_BOOLEAN)
            )
        );
    }
}
