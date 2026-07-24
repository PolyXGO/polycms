<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactForm;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ContactFormController extends Controller
{
    /**
     * Display a listing of contact forms.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContactForm::query();

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('slug', 'like', "%{$request->search}%");
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $forms = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($forms);
    }

    /**
     * Store a newly created contact form.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:contact_forms,slug',
            'fields' => 'required|array',
            'type' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure unique slug
            $originalSlug = $validated['slug'];
            $count = 1;
            while (ContactForm::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count++;
            }
        }

        $form = ContactForm::create($validated);

        return response()->json($form, 201);
    }

    /**
     * Display the specified contact form.
     */
    public function show($id): JsonResponse
    {
        return response()->json(ContactForm::findOrFail($id));
    }

    /**
     * Update the specified contact form.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $form = ContactForm::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:contact_forms,slug,' . $id,
            'fields' => 'required|array',
            'type' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $form->update($validated);

        return response()->json($form);
    }

    /**
     * Remove the specified contact form.
     */
    public function destroy($id): JsonResponse
    {
        $form = ContactForm::findOrFail($id);
        $form->delete();

        return response()->json(['success' => true]);
    }
}
