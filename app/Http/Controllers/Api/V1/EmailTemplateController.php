<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\EmailTemplateManager;
use App\Services\EmailTemplatePreviewDataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailTemplateController extends Controller
{
    public function __construct(
        protected EmailTemplateManager $templateManager,
        protected EmailTemplatePreviewDataService $previewDataService
    ) {
    }

    public function index(): JsonResponse
    {
        $templates = EmailTemplate::all();
        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    public function show($id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $template,
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);
        
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Template updated successfully',
            'data' => $template,
        ]);
    }

    public function preview(Request $request, $id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
        ]);

        $sampleData = $this->previewDataService->buildForTemplate($template);
        $rendered = $this->templateManager->renderRaw(
            (string) ($validated['subject'] ?? $template->subject ?? ''),
            (string) ($validated['body'] ?? $template->body ?? ''),
            $sampleData
        );

        return response()->json([
            'success' => true,
            'data' => [
                'subject' => $rendered['subject'],
                'body' => $rendered['body'],
                'sample_data' => $sampleData,
                'is_preview' => true,
            ],
        ]);
    }
}
