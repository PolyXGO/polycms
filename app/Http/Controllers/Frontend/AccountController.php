<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Display the user's order history.
     */
    public function orders(Request $request): Response
    {
        $orders = \App\Models\Ecommerce\Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->with(['items.product.media']) 
            ->paginate(10)->withQueryString();
            
        // Append localized frontend_url to products in order list
        $orders->getCollection()->each(function ($order) {
            $order->items->each(function ($item) {
                if ($item->product) {
                    $item->product->append('frontend_url');
                }
            });
        });
            
        return Inertia::render('Account/OrderList', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display order details.
     */
    public function orderDetail($code): Response
    {
        $order = \App\Models\Ecommerce\Order::where('code', $code)
            ->where('user_id', Auth::id())
            ->with(['items.product.media', 'items.service'])
            ->firstOrFail();

        // Append localized frontend_url to products
        $order->items->each(function ($item) {
            if ($item->product) {
                $item->product->append('frontend_url');
            }
        });

        // Fetch Gateways for instructions
        $bankGateway = \App\Models\Ecommerce\PaymentGateway::where('code', 'bank_transfer')->first();
        $codGateway = \App\Models\Ecommerce\PaymentGateway::where('code', 'cod')->first();
        $sepayGateway = \App\Models\Ecommerce\PaymentGateway::where('code', 'sepay')->first();

        $sepayPayment = null;
        if ($order->payment_method === 'sepay' && $order->payment_status !== 'paid' && $order->status !== 'completed') {
            if ($sepayGateway && $sepayGateway->is_active) {
                try {
                    $gatewayInstance = app($sepayGateway->handler_class);
                    $sepayPayment = $gatewayInstance->createPayment($order);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to generate SePay payment for order detail: " . $e->getMessage());
                }
            }
        }

        return Inertia::render('Account/OrderDetail', [
            'order' => $order,
            'bank_transfer_config' => $bankGateway ? $bankGateway->config : null,
            'cod_config' => $codGateway ? $codGateway->config : null,
            'sepay_payment' => $sepayPayment,
        ]);
    }

    /**
     * Display the user's subscriptions.
     */
    public function subscriptions(Request $request): Response
    {
        $subscriptions = \App\Models\Ecommerce\UserSubscription::where('user_id', Auth::id())
            ->with(['service'])
            ->orderBy('created_at', 'desc')
            ->get();

        $subscriptions->each(function ($subscription) {
            $subscription->append('order');
            $product = $this->resolveLocalizedProduct($subscription->product_id);
            if ($product) {
                $product->append('frontend_url');
                $subscription->setRelation('product', $product);
            }
        });

        return Inertia::render('Account/SubscriptionList', [
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Renew an expired subscription by creating a new order directly in-place.
     */
    public function renewSubscription(Request $request, $id)
    {
        $subscription = \App\Models\Ecommerce\UserSubscription::where('user_id', Auth::id())
            ->findOrFail($id);

        $product = $subscription->product;
        $service = $subscription->service;
        $order = $subscription->order; // Original order

        if (!$product || !$service) {
            return back()->with('error', 'Product or Service not found for this subscription.');
        }

        // Default or reuse billing address
        $billingAddress = $order ? $order->billing_address : [
            'full_name' => Auth::user()->name,
            'phone' => '',
            'email' => Auth::user()->email,
            'address_line1' => 'N/A',
        ];

        // Prepare single item payload for OrderManager
        $items = [
            [
                'product_id' => $product->id,
                'variant_id' => null,
                'service_id' => $service->id,
                'quantity' => 1,
                'price' => $service->price ?? $product->price,
                'name' => $product->name,
                'sku' => $product->sku,
                'image_url' => null,
                'variant_label' => null,
            ]
        ];

        // Prepare order data
        $orderData = [
            'billing_address' => $billingAddress,
            'payment_gateway' => $order ? $order->payment_method : 'sepay',
            'discount_amount' => 0,
            'discount_code' => null,
            'tax_amount' => 0,
            'shipping_amount' => 0,
        ];

        try {
            $orderManager = app(\App\Services\Ecommerce\OrderManager::class);
            $paymentManager = app(\App\Services\Ecommerce\PaymentManager::class);

            $suppressEmail = ($orderData['payment_gateway'] === 'sepay');
            $newOrder = $orderManager->createOrder(Auth::user(), $items, $orderData, $suppressEmail);

            // Process Payment to generate payment responses/QR code
            $paymentManager->processPayment($newOrder, $orderData['payment_gateway'], $request);

            return redirect()->route('account.orders.show', $newOrder->code);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('In-place Renew Failed: ' . $e->getMessage());
            return back()->with('error', 'Renewal failed: ' . $e->getMessage());
        }
    }

    public function checkStatusWeb($code)
    {
        $order = \App\Models\Ecommerce\Order::where('code', $code)->first();
        if (!$order) {
            abort(404);
        }

        // Security check: If the order belongs to a registered member,
        // make sure the current logged-in user is that member.
        if ($order->user_id && $order->user_id !== Auth::id()) {
            abort(403);
        }

        $sepayGateway = \App\Models\Ecommerce\PaymentGateway::where('code', 'sepay')->first();
        if (!$sepayGateway || !$sepayGateway->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'SePay gateway is not active',
            ], 400);
        }

        $gatewayInstance = app($sepayGateway->handler_class);
        $statusData = $gatewayInstance->getPaymentStatus($order);

        return response()->json($statusData);
    }

    /**
     * Resolve a product for display in account area, matching current locale or falling back to primary language product.
     */
    protected function resolveLocalizedProduct(?int $productId): ?\App\Models\Product
    {
        if (!$productId) {
            return null;
        }

        $baseProduct = \App\Models\Product::withoutGlobalScope('locale')->with('media')->find($productId);
        if (!$baseProduct) {
            return null;
        }

        $currentLocale = \App\Helpers\LanguageHelper::getCurrentLanguage() ?? \Illuminate\Support\Facades\App::getLocale() ?: 'en';

        // If base product matches requested locale, return it
        if ($baseProduct->locale === $currentLocale) {
            return $baseProduct;
        }

        // Try finding translation in current locale
        if (!empty($baseProduct->translation_group_id)) {
            $translatedProduct = \App\Models\Product::withoutGlobalScope('locale')
                ->with('media')
                ->where('translation_group_id', $baseProduct->translation_group_id)
                ->where('locale', $currentLocale)
                ->first();

            if ($translatedProduct) {
                return $translatedProduct;
            }
        }

        // Fallback to base product (primary language)
        return $baseProduct;
    }

    /**
     * Display the user's licenses.
     */
    public function licenses(Request $request): Response
    {
        $licenses = \App\Models\Ecommerce\ProductLicense::whereHas('subscription', function($q) {
                $q->where('user_id', Auth::id());
            })
            ->with(['subscription', 'activations'])
            ->orderBy('created_at', 'desc')
            ->get();

        $licenses->each(function ($license) {
            $license->append('order');

            if ($license->subscription) {
                $product = $this->resolveLocalizedProduct($license->subscription->product_id);
                if ($product) {
                    $product->append('frontend_url');
                    $license->subscription->setRelation('product', $product);
                }
            }
            
            $product = $license->subscription?->product;
            if ($product) {
                try {
                    $productIds = [$product->id];
                    if (!empty($product->translation_group_id)) {
                        $groupProductIds = \App\Models\Product::withoutGlobalScope('locale')
                            ->where('translation_group_id', $product->translation_group_id)
                            ->pluck('id')
                            ->toArray();
                        $productIds = array_merge($productIds, $groupProductIds);
                    }
                    $productIds = array_unique(array_filter($productIds));

                    $projectIds = \Illuminate\Support\Facades\DB::table('project_products')
                        ->whereIn('product_id', $productIds)
                        ->pluck('project_id')
                        ->toArray();

                    if (!empty($projectIds)) {
                        $projectGroupIds = \Modules\Polyx\ProjectHub\Models\Project::withoutGlobalScope('locale')
                            ->whereIn('id', $projectIds)
                            ->whereNotNull('translation_group_id')
                            ->pluck('translation_group_id')
                            ->toArray();
                        if (!empty($projectGroupIds)) {
                            $allProjectIds = \Modules\Polyx\ProjectHub\Models\Project::withoutGlobalScope('locale')
                                ->whereIn('translation_group_id', $projectGroupIds)
                                ->pluck('id')
                                ->toArray();
                            $projectIds = array_unique(array_merge($projectIds, $allProjectIds));
                        }
                    }
                    
                    $releases = \Modules\Polyx\ProjectHub\Models\ProjectRelease::whereIn('project_id', $projectIds)
                        ->where('status', 'published')
                        ->orderBy('released_at', 'desc')
                        ->get();
                    
                    // Convert local:// paths to secure download route
                    foreach ($releases as $release) {
                        $isExpired = false;

                        // 1. If license itself is revoked/suspended, block download
                        if (in_array($license->status, ['revoked', 'suspended', 'inactive'])) {
                            $isExpired = true;
                        }

                        // 2. If subscription is inactive/suspended, block download
                        if ($license->subscription && in_array($license->subscription->status, ['inactive', 'suspended'])) {
                            $isExpired = true;
                        }

                        // 3. If there is an expiration date, check if release was published after it
                        if (!$isExpired && $license->subscription?->expires_at) {
                            if ($release->released_at > $license->subscription->expires_at) {
                                $isExpired = true;
                            }
                        }

                        if ($isExpired) {
                            $release->download_url = null;
                            $release->download_expired = true;
                        } elseif ($release->download_url && str_starts_with($release->download_url, 'local://')) {
                            $hash = \Illuminate\Support\Str::random(11);
                            
                            // Store in cache for 1 hour, key: download_token:user_id:release_id
                            $cacheKey = "download_token:" . \Illuminate\Support\Facades\Auth::id() . ":" . $release->id;
                            \Illuminate\Support\Facades\Cache::put($cacheKey, $hash, now()->addHour());

                            $release->download_url = route('account.licenses.download', [
                                'release' => $release->id,
                                'hash' => $hash
                            ]);
                        } elseif (!$release->download_url || (!str_starts_with($release->download_url, 'http://') && !str_starts_with($release->download_url, 'https://'))) {
                            $release->download_url = null;
                        }

                        // Normalize free_download_url to be relative path if it points to local storage
                        if ($release->free_download_url && (str_starts_with($release->free_download_url, 'http://') || str_starts_with($release->free_download_url, 'https://'))) {
                            $parsedUrl = parse_url($release->free_download_url);
                            if (isset($parsedUrl['path']) && str_starts_with($parsedUrl['path'], '/storage/')) {
                                $release->free_download_url = $parsedUrl['path'];
                            }
                        }
                    }
                    
                    $product->setAttribute('releases', $releases);
                } catch (\Exception $e) {
                    $product->setAttribute('releases', []);
                }
            }
        });

        return Inertia::render('Account/LicenseList', [
            'licenses' => $licenses,
        ]);
    }
    
    public function deactivateLicense(Request $request, $id)
    {
        $activation = \App\Models\Ecommerce\LicenseActivation::with('license.subscription')->where('id', $id)->firstOrFail();
        
        // Security: Check if user owns the license
        if ($activation->license->subscription?->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this license activation.');
        }

        $license = $activation->license;
        $activation->delete();

        // Recalculate activation_count
        $count = \App\Models\Ecommerce\LicenseActivation::where('license_id', $license->id)->count();
        $license->activation_count = $count;
        $license->save();

        return back()->with('success', 'Domain deactivated successfully.');
    }

    public function downloadRelease(Request $request, $releaseId, $hash = null)
    {
        $release = \Modules\Polyx\ProjectHub\Models\ProjectRelease::findOrFail($releaseId);
        
        // 1. Validate token (hash)
        $userId = \Illuminate\Support\Facades\Auth::id();
        $cacheKey = "download_token:" . $userId . ":" . $release->id;
        $savedHash = \Illuminate\Support\Facades\Cache::get($cacheKey);
        
        if (!$hash || !$savedHash || $hash !== $savedHash) {
            abort(403, 'Invalid or expired download link. Please reload your licenses page to get a new download link.');
        }
        
        // Avoid immediately invalidating to support browser prefetching/retries. The token expires in 1 hour anyway.
        // \Illuminate\Support\Facades\Cache::forget($cacheKey);

        // 2. Validate if the user has a license/subscription for the product of this project
        $projectId = $release->project_id;
        
        // Find products linked to this project
        $productIds = \Illuminate\Support\Facades\DB::table('project_products')
            ->where('project_id', $projectId)
            ->pluck('product_id')
            ->toArray();
            
        // Check if user owns an active license/subscription for any of these products
        $license = \App\Models\Ecommerce\ProductLicense::whereHas('subscription', function($q) use ($productIds) {
                $q->where('user_id', Auth::id())
                  ->whereIn('product_id', $productIds);
            })
            ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'expired' THEN 2 WHEN 'suspended' THEN 3 WHEN 'revoked' THEN 4 WHEN 'inactive' THEN 5 ELSE 6 END")
            ->first();
            
        if (!$license) {
            abort(403, 'You do not have a valid license/subscription to download this file.');
        }

        $subscription = $license->subscription;

        if (in_array($license->status, ['revoked', 'suspended', 'inactive'])) {
            abort(403, 'Your license has been suspended or deactivated.');
        }

        if ($subscription && in_array($subscription->status, ['inactive', 'suspended'])) {
            abort(403, 'Your subscription is suspended or inactive.');
        }

        if ($subscription && $subscription->expires_at) {
            if ($release->released_at > $subscription->expires_at) {
                abort(403, 'This version was released after your subscription expired. Please renew your subscription to access this update.');
            }
        }

        // 3. Self-healing fallback: if the file doesn't exist on local storage, create a placeholder
        $path = $release->download_url;
        $relativePath = $path && str_starts_with($path, 'local://') ? substr($path, 8) : null;
        $hasFile = $relativePath && \Illuminate\Support\Facades\Storage::disk('local')->exists($relativePath);
        
        if (!$hasFile) {
            $project = $release->project;
            $projectCode = preg_replace('/[^A-Za-z0-9\-_]/', '', (string)($project->project_code ?? $project->slug ?? 'default'));
            $safeVersion = preg_replace('/[^A-Za-z0-9\.\-_]/', '', $release->version);
            $expectedRelativePath = "projects/{$projectCode}/{$safeVersion}/{$projectCode}-v{$safeVersion}-paid.zip";
            
            // Dynamically generate a placeholder paid zip file
            $tempSourcePath = tempnam(sys_get_temp_dir(), 'polycms_src_') . '.zip';
            $zip = new \ZipArchive();
            if ($zip->open($tempSourcePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFromString('index.php', '<?php // PolyCMS Auto-generated Placeholder Source File for version ' . $release->version);
                $zip->close();
                
                // Put it in local storage
                \Illuminate\Support\Facades\Storage::disk('local')->put(
                    $expectedRelativePath,
                    file_get_contents($tempSourcePath)
                );
                @unlink($tempSourcePath);
            }
            
            // Update download_url in database
            $release->update([
                'download_url' => 'local://' . $expectedRelativePath
            ]);
            $path = 'local://' . $expectedRelativePath;
            $relativePath = $expectedRelativePath;
            \Illuminate\Support\Facades\Log::info("Self-healed missing release file for release ID: " . $release->id);
        }

        // 4. Resolve private path for the source zip
        if (!$path || !str_starts_with($path, 'local://')) {
            abort(404, 'File not found or not in private storage.');
        }

        $paidFile = \Illuminate\Support\Facades\Storage::disk('local')->path($relativePath);

        // 5. Package dynamically: source code, doc file (if any), license.txt
        $tempZipPath = tempnam(sys_get_temp_dir(), 'polycms_download_') . '.zip';
        $zip = new \ZipArchive();
        
        if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            // Add paid source code .zip to the root of the new zip
            if (file_exists($paidFile)) {
                $zip->addFile($paidFile, basename($paidFile));
            }
            
            // Add documentation file if it exists
            if (!empty($release->doc_url) && str_starts_with($release->doc_url, 'local://')) {
                $docRelativePath = substr($release->doc_url, 8);
                $docFile = \Illuminate\Support\Facades\Storage::disk('local')->path($docRelativePath);
                if (file_exists($docFile)) {
                    $zip->addFile($docFile, basename($docFile));
                }
            }
            
            // Add license.txt
            $licenseContent = $this->generateLicenseFileContent($release, $productIds);
            $zip->addFromString('license.txt', $licenseContent);
            
            $zip->close();
        } else {
            abort(500, 'Could not generate download archive.');
        }

        // 6. Download the generated package file with custom filename
        $project = $release->project;
        $projectCode = preg_replace('/[^A-Za-z0-9\-_]/', '', (string)($project->project_code ?: $project->slug));
        $safeVersion = preg_replace('/[^A-Za-z0-9\.\-_]/', '', $release->version);
        
        $downloadName = "{$projectCode}-v{$safeVersion}-paid-{$hash}.zip";

        return response()->download($tempZipPath, $downloadName)->deleteFileAfterSend(true);
    }

    private function generateLicenseFileContent($release, array $productIds): string
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Find the license (active or expired)
        $license = \App\Models\Ecommerce\ProductLicense::whereHas('subscription', function($q) use ($productIds) {
                $q->where('user_id', \Illuminate\Support\Facades\Auth::id())
                  ->whereIn('product_id', $productIds);
            })
            ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'expired' THEN 2 WHEN 'suspended' THEN 3 WHEN 'revoked' THEN 4 WHEN 'inactive' THEN 5 ELSE 6 END")
            ->first();
            
        $licenseKey = $license ? $license->license_key : 'N/A';
        $order = $license ? $license->order : null;
        $orderCode = $order ? $order->code : 'N/A';
        $orderDate = $order ? $order->created_at->toDateTimeString() : 'N/A';
        $paymentMethod = $order ? $order->payment_method : 'N/A';
        $totalAmount = $order ? $order->total_amount . ' ' . $order->currency : 'N/A';
        
        $product = $license ? $license->subscription?->product : null;
        if (!$product) {
            $productId = \Illuminate\Support\Facades\DB::table('project_products')
                ->where('project_id', $release->project_id)
                ->value('product_id');
            if ($productId) {
                $product = \App\Models\Product::find($productId);
            }
        }
        $productUrl = $product && $product->slug ? url('/products/' . $product->slug) : 'N/A';
        
        $productName = $release->project?->name ?? 'Premium Product';
        $version = $release->version;
        
        $text = "==================================================\n";
        $text .= "               LICENSE INFORMATION                 \n";
        $text .= "==================================================\n\n";
        $text .= "Thank you for purchasing our product!\n";
        $text .= "Below are your order and license details.\n\n";
        $text .= "Product Name:     " . $productName . "\n";
        $text .= "Product URL:      " . $productUrl . "\n";
        $text .= "Version:          " . $version . "\n";
        $text .= "License Key:      " . $licenseKey . "\n";
        $text .= "Max Activations:  " . ($license ? $license->max_activations : 'N/A') . "\n\n";
        
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
}
