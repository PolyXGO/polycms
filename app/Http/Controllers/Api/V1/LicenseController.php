<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\ProductLicense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LicenseController extends Controller
{
    /**
     * Display a listing of licenses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductLicense::with(['subscription.user', 'subscription.product']);

        if ($request->has('user_id')) {
            if ($request->user_id === 'me') {
                $userId = $request->user()->id;
                $query->whereHas('subscription', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            } else {
                // If filtering by specific user ID (admin use case usually)
                $targetUserId = $request->user_id;
                $query->whereHas('subscription', function($q) use ($targetUserId) {
                    $q->where('user_id', $targetUserId);
                });
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('license_key', 'like', "%{$search}%")
                  ->orWhereHas('subscription.product', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('subscription.user', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $licenses = $query->latest()->paginate($request->get('per_page', 15));

        $licenses->getCollection()->each(function ($license) {
            $license->append('order');
            
            $product = $license->subscription?->product;
            if ($product) {
                try {
                    $projectIds = \Illuminate\Support\Facades\DB::table('project_products')
                        ->where('product_id', $product->id)
                        ->pluck('project_id')
                        ->toArray();
                    
                    $releases = \Modules\Polyx\ProjectHub\Models\ProjectRelease::whereIn('project_id', $projectIds)
                        ->where('status', 'published')
                        ->orderBy('released_at', 'desc')
                        ->get();
                    
                    foreach ($releases as $release) {
                        $release->download_url = route('admin.licenses.download-as-admin', [
                            'license' => $license->id,
                            'release' => $release->id
                        ]);
                    }
                    
                    $product->setAttribute('releases', $releases);
                } catch (\Exception $e) {
                    $product->setAttribute('releases', []);
                }
            }
        });

        return response()->json($licenses);
    }

    /**
     * Display the specified license.
     */
    public function show($id): JsonResponse
    {
        $license = ProductLicense::with(['subscription.user', 'subscription.product', 'activations'])->findOrFail($id);
        
        if (request()->has('user_id') && request()->user_id === 'me') {
            if ($license->subscription->user_id !== request()->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return response()->json($license);
    }

    /**
     * Update specified license (Max activations & Status).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $license = ProductLicense::with(['subscription.user', 'subscription.product', 'activations'])->findOrFail($id);

        $validated = $request->validate([
            'max_activations' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|required|string|in:active,revoked,suspended',
        ]);

        if (isset($validated['max_activations'])) {
            $license->max_activations = (int) $validated['max_activations'];
        }

        if (isset($validated['status'])) {
            $license->status = $validated['status'];
        }

        $license->save();

        return response()->json([
            'success' => true,
            'message' => 'License updated successfully',
            'data' => $license->fresh(['subscription.user', 'subscription.product', 'activations'])
        ]);
    }

    /**
     * Delete an activation record for a license.
     */
    public function deleteActivation(Request $request, $licenseId, $activationId): JsonResponse
    {
        $license = ProductLicense::findOrFail($licenseId);
        $activation = \App\Models\Ecommerce\LicenseActivation::where('license_id', $license->id)
            ->where('id', $activationId)
            ->firstOrFail();

        $activation->delete();

        // Recalculate activation_count
        $count = \App\Models\Ecommerce\LicenseActivation::where('license_id', $license->id)->count();
        $license->activation_count = $count;
        $license->save();

        return response()->json([
            'success' => true,
            'message' => 'Activation removed successfully',
            'data' => $license->fresh(['subscription.user', 'subscription.product', 'activations'])
        ]);
    }

    public function downloadAsAdmin(Request $request, $licenseId, $releaseId)
    {
        if (!Auth::check() && !$request->user()) {
            abort(401);
        }

        $license = ProductLicense::with(['subscription.user', 'subscription.product'])->findOrFail($licenseId);
        $release = \Modules\Polyx\ProjectHub\Models\ProjectRelease::findOrFail($releaseId);

        $projectId = $release->project_id;
        $productIds = \Illuminate\Support\Facades\DB::table('project_products')
            ->where('project_id', $projectId)
            ->pluck('product_id')
            ->toArray();

        if (!in_array($license->subscription->product_id, $productIds)) {
            abort(403, 'Release does not match license product.');
        }

        $path = $release->download_url;
        $relativePath = $path && str_starts_with($path, 'local://') ? substr($path, 8) : null;
        $hasFile = $relativePath && \Illuminate\Support\Facades\Storage::disk('local')->exists($relativePath);

        if (!$hasFile) {
            $project = $release->project;
            $projectCode = preg_replace('/[^A-Za-z0-9\-_]/', '', (string)($project->project_code ?? $project->slug ?? 'default'));
            $safeVersion = preg_replace('/[^A-Za-z0-9\.\-_]/', '', $release->version);
            $expectedRelativePath = "projects/{$projectCode}/{$safeVersion}/{$projectCode}-v{$safeVersion}-paid.zip";
            
            $tempSourcePath = tempnam(sys_get_temp_dir(), 'polycms_src_') . '.zip';
            $zip = new \ZipArchive();
            if ($zip->open($tempSourcePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFromString('index.php', '<?php // PolyCMS Auto-generated Placeholder Source File for version ' . $release->version);
                $zip->close();
                
                \Illuminate\Support\Facades\Storage::disk('local')->put(
                    $expectedRelativePath,
                    file_get_contents($tempSourcePath)
                );
                @unlink($tempSourcePath);
            }
            
            $release->update([
                'download_url' => 'local://' . $expectedRelativePath
            ]);
            $path = 'local://' . $expectedRelativePath;
            $relativePath = $expectedRelativePath;
        }

        if (!$path || !str_starts_with($path, 'local://')) {
            abort(404, 'File not found.');
        }

        $paidFile = \Illuminate\Support\Facades\Storage::disk('local')->path($relativePath);

        $tempZipPath = tempnam(sys_get_temp_dir(), 'polycms_admin_download_') . '.zip';
        $zip = new \ZipArchive();
        
        if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            if (file_exists($paidFile)) {
                $zip->addFile($paidFile, basename($paidFile));
            }
            
            if (!empty($release->doc_url) && str_starts_with($release->doc_url, 'local://')) {
                $docRelativePath = substr($release->doc_url, 8);
                $docFile = \Illuminate\Support\Facades\Storage::disk('local')->path($docRelativePath);
                if (file_exists($docFile)) {
                    $zip->addFile($docFile, basename($docFile));
                }
            }
            
            $licenseContent = $this->generateLicenseFileContentForAdmin($release, $license);
            $zip->addFromString('license.txt', $licenseContent);
            
            $zip->close();
        } else {
            abort(500, 'Could not generate download archive.');
        }

        $project = $release->project;
        $projectCode = preg_replace('/[^A-Za-z0-9\-_]/', '', (string)($project->project_code ?: $project->slug));
        $safeVersion = preg_replace('/[^A-Za-z0-9\.\-_]/', '', $release->version);
        
        $hash = \Illuminate\Support\Str::random(11);
        $downloadName = "{$projectCode}-v{$safeVersion}-paid-admin-{$hash}.zip";

        return response()->download($tempZipPath, $downloadName)->deleteFileAfterSend(true);
    }

    private function generateLicenseFileContentForAdmin($release, $license): string
    {
        $user = $license->subscription?->user;
        $licenseKey = $license->license_key;
        $order = $license->order;
        $orderCode = $order ? $order->code : 'N/A';
        $orderDate = $order ? $order->created_at->toDateTimeString() : 'N/A';
        $paymentMethod = $order ? $order->payment_method : 'N/A';
        $totalAmount = $order ? $order->total_amount . ' ' . $order->currency : 'N/A';
        
        $productName = $release->project?->name ?? 'Premium Product';
        $version = $release->version;
        
        $text = "==================================================\n";
        $text .= "               LICENSE INFORMATION                 \n";
        $text .= "==================================================\n\n";
        $text .= "Thank you for purchasing our product!\n";
        $text .= "Below are your order and license details.\n\n";
        $text .= "Product Name:     " . $productName . "\n";
        $text .= "Version:          " . $version . "\n";
        $text .= "License Key:      " . $licenseKey . "\n";
        $text .= "Max Activations:  " . $license->max_activations . "\n\n";
        
        $text .= "--------------------------------------------------\n";
        $text .= "ORDER INFORMATION\n";
        $text .= "--------------------------------------------------\n";
        $text .= "Order Code:       " . $orderCode . "\n";
        $text .= "Order Date:       " . $orderDate . "\n";
        $text .= "Payment Method:   " . $paymentMethod . "\n";
        $text .= "Total Paid:       " . $totalAmount . "\n";
        $text .= "Customer Name:    " . ($user ? $user->name : 'Customer') . "\n";
        $text .= "Customer Email:   " . ($user ? $user->email : 'N/A') . "\n\n";
        
        $text .= "--------------------------------------------------\n";
        $text .= "SUPPORT & TERMS\n";
        $text .= "--------------------------------------------------\n";
        $text .= "- Please keep this license key private.\n";
        $text .= "- For support and documentation, please visit our website.\n";
        $text .= "==================================================\n";
        
        return $text;
    }

    /**
     * Public API endpoint to activate a license for a site domain.
     */
    public function activatePublic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => 'required|string',
            'domain' => 'nullable|string',
            'hardware_id' => 'nullable|string',
        ]);

        $key = trim($validated['license_key']);
        $domain = !empty($validated['domain']) 
            ? trim(preg_replace('#^https?://#', '', strtolower($validated['domain'])), '/')
            : strtolower($request->getHost());
        $hwid = $validated['hardware_id'] ?? null;

        $licenseManager = app(\App\Services\Ecommerce\LicenseManager::class);

        try {
            $license = ProductLicense::where('license_key', $key)->first();
            if (!$license) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid license key.',
                ], 404);
            }

            $existingActivation = $license->activations()
                ->where('domain', $domain)
                ->first();

            if ($existingActivation) {
                return response()->json([
                    'success' => true,
                    'message' => 'License is already active for this domain.',
                    'data' => [
                        'license_key' => $license->license_key,
                        'status' => $license->status,
                        'domain' => $domain,
                        'max_activations' => $license->max_activations,
                        'activation_count' => $license->activation_count,
                        'activated_at' => $existingActivation->activated_at ?? now()->toDateTimeString(),
                    ]
                ]);
            }

            $licenseManager->activateLicense($key, $domain, $hwid);
            $license->refresh();

            return response()->json([
                'success' => true,
                'message' => "License activated successfully for domain {$domain}.",
                'data' => [
                    'license_key' => $license->license_key,
                    'status' => $license->status,
                    'domain' => $domain,
                    'max_activations' => $license->max_activations,
                    'activation_count' => $license->activation_count,
                    'activated_at' => now()->toDateTimeString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Public API endpoint to verify a license for a site domain.
     */
    public function verifyPublic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => 'required|string',
            'domain' => 'nullable|string',
        ]);

        $key = trim($validated['license_key']);
        $domain = !empty($validated['domain'])
            ? trim(preg_replace('#^https?://#', '', strtolower($validated['domain'])), '/')
            : strtolower($request->getHost());

        $license = ProductLicense::where('license_key', $key)->first();

        if (!$license || $license->status !== 'active') {
            return response()->json([
                'success' => false,
                'active' => false,
                'message' => 'License key is invalid or inactive.',
            ], 404);
        }

        if ($license->subscription) {
            if ($license->subscription->status !== 'active' || ($license->subscription->expires_at && $license->subscription->expires_at->isPast())) {
                return response()->json([
                    'success' => false,
                    'active' => false,
                    'message' => 'Subscription associated with this license is inactive or expired.',
                ], 422);
            }
        }

        $activation = $license->activations()->where('domain', $domain)->first();

        if (!$activation) {
            return response()->json([
                'success' => true,
                'active' => false,
                'message' => "License is valid, but domain {$domain} is not activated.",
                'data' => [
                    'license_key' => $license->license_key,
                    'status' => $license->status,
                    'max_activations' => $license->max_activations,
                    'activation_count' => $license->activation_count,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'active' => true,
            'message' => "License is active and valid for domain {$domain}.",
            'data' => [
                'license_key' => $license->license_key,
                'status' => $license->status,
                'domain' => $domain,
                'max_activations' => $license->max_activations,
                'activation_count' => $license->activation_count,
                'activated_at' => $activation->activated_at ?? null,
            ]
        ]);
    }

    /**
     * Public API endpoint to deactivate a license for a site domain.
     */
    public function deactivatePublic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => 'required|string',
            'domain' => 'nullable|string',
        ]);

        $key = trim($validated['license_key']);
        $domain = !empty($validated['domain'])
            ? trim(preg_replace('#^https?://#', '', strtolower($validated['domain'])), '/')
            : strtolower($request->getHost());

        $license = ProductLicense::where('license_key', $key)->first();
        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid license key.',
            ], 404);
        }

        $activation = $license->activations()->where('domain', $domain)->first();
        if (!$activation) {
            return response()->json([
                'success' => false,
                'message' => "Domain {$domain} is not currently activated for this license key.",
            ], 404);
        }

        $licenseManager = app(\App\Services\Ecommerce\LicenseManager::class);
        $licenseManager->deactivateLicense($activation->id);

        return response()->json([
            'success' => true,
            'message' => "License deactivated successfully for domain {$domain}.",
        ]);
    }

    /**
     * Public API endpoint to check for module version updates from ProjectHub releases.
     */
    public function checkUpdatePublic(Request $request): JsonResponse
    {
        $moduleKey = trim((string) $request->input('module', ''));
        $clientVersion = trim((string) $request->input('version', '1.0.0'));
        $licenseKey = trim((string) $request->input('license_key', ''));
        $domain = strtolower((string) $request->input('domain', $request->getHost()));
        $platform = strtolower((string) $request->input('platform', 'polycms'));

        $latestVersion = $clientVersion;
        $changelog = '';
        $downloadUrl = '';
        $isFree = false;

        if (!empty($moduleKey)) {
            $cleanModule = str_replace(['Polyx.', 'polyx.'], '', $moduleKey);
            $cleanLower = strtolower($cleanModule);

            $candidates = array_unique(array_filter([
                $moduleKey,
                $cleanModule,
                $cleanLower,
                "{$cleanLower}_polycms",
                "{$cleanLower}-polycms",
                "{$cleanLower}-for-polycms",
                "polyx_{$cleanLower}",
                "polyx-{$cleanLower}",
            ]));

            // Match by project_code & target platform in ProjectHub
            $project = null;
            if (class_exists(\Modules\Polyx\ProjectHub\Models\Project::class)) {
                $query = \Modules\Polyx\ProjectHub\Models\Project::where(function ($q) use ($candidates): void {
                    $q->whereIn('project_code', $candidates)
                        ->orWhereIn(\Illuminate\Support\Facades\DB::raw('LOWER(project_code)'), array_map('strtolower', $candidates));
                });

                if (\Illuminate\Support\Facades\Schema::hasColumn('projects', 'platform')) {
                    $query->where(function ($q) use ($platform): void {
                        $q->where('platform', $platform)
                            ->orWhereNull('platform')
                            ->orWhere('platform', '');
                    });
                }

                $project = $query->first();
            }

            if ($project) {
                // Get all published releases and find the highest version using version_compare
                $releases = \Modules\Polyx\ProjectHub\Models\ProjectRelease::where('project_id', $project->id)
                    ->where('status', 'published')
                    ->get();

                $latestRelease = null;
                foreach ($releases as $rel) {
                    if (!$latestRelease || version_compare($rel->version, $latestRelease->version, '>')) {
                        $latestRelease = $rel;
                    }
                }

                if ($latestRelease) {
                    $latestVersion = $latestRelease->version ?: $clientVersion;
                    $changelog = (string) ($latestRelease->summary ?: $latestRelease->title ?: '');

                    // Check if linked product is free or paid
                    $linkedProduct = $project->products()->first();
                    if (!$linkedProduct || (float) $linkedProduct->price <= 0) {
                        $isFree = true;
                    }

                    if ($isFree) {
                        $downloadUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                            'api.v1.licenses.download-release-public',
                            now()->addMinutes(15),
                            [
                                'release_id' => $latestRelease->id,
                            ]
                        );
                    } else {
                        // Check if valid license provided for paid module
                        $licenseValid = false;
                        if (!empty($licenseKey)) {
                            $licenseManager = app(\App\Services\Ecommerce\LicenseManager::class);
                            if (method_exists($licenseManager, 'verifyLicense')) {
                                $verified = $licenseManager->verifyLicense($licenseKey, $domain);
                                if (!empty($verified['valid'])) {
                                    $licenseValid = true;
                                }
                            }
                        }

                        if ($licenseValid) {
                            $downloadUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                                'api.v1.licenses.download-release-public',
                                now()->addMinutes(15),
                                [
                                    'release_id'  => $latestRelease->id,
                                    'license_key' => $licenseKey,
                                    'domain'      => $domain,
                                ]
                            );
                        } else {
                            $downloadUrl = ''; // Paid module requires valid active license key
                        }
                    }
                }
            }
        }

        $hasUpdate = version_compare($latestVersion, $clientVersion, '>');

        return response()->json([
            'success'         => true,
            'module'          => $moduleKey,
            'current_version' => $clientVersion,
            'latest_version'  => $latestVersion,
            'has_update'      => $hasUpdate,
            'changelog'       => $changelog,
            'download_url'    => $downloadUrl,
            'is_free'         => $isFree,
        ]);
    }

    /**
     * Download release ZIP file securely (for paid modules after license check, or free modules).
     * Enforces short-lived temporary signed tokens (15 minutes) & HMAC signature verification.
     */
    public function downloadReleasePublic(Request $request): mixed
    {
        // Require valid temporary signed token signature to prevent link sharing & data leaks
        if (!$request->hasValidSignature() && !$request->hasValidRelativeSignature()) {
            return response()->json([
                'success' => false,
                'message' => 'Link tải đã hết hạn (chỉ có hiệu lực trong 15 phút) hoặc Chữ ký bảo mật (Signature) không hợp lệ. Vui lòng thực hiện Check Update lại để lấy link mới.',
            ], 403);
        }

        $licenseKey = trim((string) $request->input('license_key', ''));
        $domain = strtolower((string) $request->input('domain', $request->getHost()));
        $releaseId = (int) $request->input('release_id', 0);

        if ($releaseId <= 0) {
            return response()->json(['success' => false, 'message' => 'Invalid release ID.'], 400);
        }

        $release = \Modules\Polyx\ProjectHub\Models\ProjectRelease::findOrFail($releaseId);
        $project = $release->project;

        $linkedProduct = $project?->products()?->first();
        $isFree = !$linkedProduct || (float) $linkedProduct->price <= 0;

        if (!$isFree) {
            if (empty($licenseKey)) {
                return response()->json(['success' => false, 'message' => 'Active license key required to download paid release.'], 403);
            }

            $licenseManager = app(\App\Services\Ecommerce\LicenseManager::class);
            if (method_exists($licenseManager, 'verifyLicense')) {
                $verified = $licenseManager->verifyLicense($licenseKey, $domain);
                if (empty($verified['valid'])) {
                    return response()->json(['success' => false, 'message' => $verified['message'] ?? 'Invalid or expired license key.'], 403);
                }
            }
        }

        $rawUrl = $release->download_url ?: $release->free_download_url ?: '';
        if (empty($rawUrl)) {
            return response()->json(['success' => false, 'message' => 'Release download file not configured on server.'], 404);
        }

        // Handle local:// storage paths
        if (str_starts_with($rawUrl, 'local://')) {
            $relativePath = ltrim(substr($rawUrl, 8), '/');
            $fullPath = storage_path('app/' . $relativePath);
            if (file_exists($fullPath)) {
                return response()->download($fullPath, basename($fullPath));
            }
            if (\Illuminate\Support\Facades\Storage::disk('local')->exists($relativePath)) {
                return \Illuminate\Support\Facades\Storage::disk('local')->download($relativePath);
            }
        }

        if (file_exists($rawUrl)) {
            return response()->download($rawUrl, basename($rawUrl));
        }

        $storagePath = storage_path('app/' . ltrim($rawUrl, '/'));
        if (file_exists($storagePath)) {
            return response()->download($storagePath, basename($storagePath));
        }

        return redirect($rawUrl);
    }
}
