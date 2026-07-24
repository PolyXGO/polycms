<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactForm;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PublicContactController extends Controller
{
    /**
     * Submit a contact form from the frontend.
     */
    public function submit(Request $request): JsonResponse
    {
        $request->validate([
            'form_id' => 'required_without:form_slug|integer',
            'form_slug' => 'required_without:form_id|string',
        ]);

        $form = null;
        if ($request->has('form_id')) {
            $form = ContactForm::find($request->form_id);
        } else {
            $form = ContactForm::where('slug', $request->form_slug)->first();
        }

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Contact form not found.',
            ], 404);
        }

        if (!$form->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This form is currently inactive.',
            ], 400);
        }

        // Parse fields schema and validate request data
        $fields = $form->fields ?? [];
        $rules = [];
        $customAttributes = [];

        foreach ($fields as $field) {
            $fieldName = $field['name'] ?? null;
            if (!$fieldName) {
                continue;
            }

            $fieldRules = [];
            if (!empty($field['required'])) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $type = $field['type'] ?? 'text';
            if ($type === 'email') {
                $fieldRules[] = 'email';
            } elseif ($type === 'number') {
                $fieldRules[] = 'numeric';
            } else {
                $fieldRules[] = 'string';
            }

            $rules[$fieldName] = $fieldRules;
            $customAttributes[$fieldName] = $field['label'] ?? $fieldName;
        }

        $validator = Validator::make($request->all(), $rules, [], $customAttributes);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validate Google reCAPTCHA if enabled and keys are configured
        if (get_option('contacts_recaptcha_enabled') && !empty(get_option('contacts_recaptcha_site_key')) && !empty(get_option('contacts_recaptcha_secret_key'))) {
            $recaptchaResponse = $request->input('g-recaptcha-response');
            if (empty($recaptchaResponse)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'g-recaptcha-response' => ['Please complete the reCAPTCHA challenge.']
                    ],
                ], 422);
            }

            $secretKey = get_option('contacts_recaptcha_secret_key');
            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                    'form_params' => [
                        'secret' => $secretKey,
                        'response' => $recaptchaResponse,
                        'remoteip' => $request->ip()
                    ]
                ]);
                $body = json_decode((string)$res->getBody(), true);
                if (empty($body['success'])) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'g-recaptcha-response' => ['reCAPTCHA validation failed. Please try again.']
                        ],
                    ], 422);
                }
            } catch (\Exception $e) {
                \Log::error('reCAPTCHA validation error: ' . $e->getMessage());
            }
        }

        $validatedData = $validator->validated();

        // Extract primary fields if they exist
        $email = $validatedData['email'] ?? $request->email ?? null;
        $name = $validatedData['name'] ?? $request->name ?? null;

        // Save submission
        $submission = ContactSubmission::create([
            'form_id' => $form->id,
            'type' => $form->type,
            'name' => $name ? substr((string)$name, 0, 255) : null,
            'email' => $email ? substr((string)$email, 0, 255) : null,
            'data' => $validatedData,
            'status' => 'unread',
        ]);

        // Invalidate admin menu cache to update badge count
        \Illuminate\Support\Facades\Cache::forget('polycms.admin_menu.version');
        \App\Support\ResilientCache::put('polycms.admin_menu.version', time());

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your submission has been received.',
            'data' => $submission,
        ]);
    }
}
