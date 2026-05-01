<?php

namespace App\Http\Controllers\Api\V1;

use App\Facades\Hook;
use App\Http\Controllers\Controller;
use App\Models\Ecommerce\PaymentGateway;
use App\Services\ModuleManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentGatewayController extends Controller
{
    public function __construct(
        protected ModuleManager $moduleManager
    ) {}

    /**
     * Get list of payment gateways, filtered by active modules
     */
    public function index(): JsonResponse
    {
        $this->ensureCoreGateways();

        $gateways = PaymentGateway::all();
        $enabledModules = collect($this->moduleManager->getEnabledModules())
            ->map(fn($k) => strtolower($k))
            ->toArray();

        // Filter gateways to only include those from active modules
        // Gateway handler_class contains module namespace
        $filteredGateways = $gateways->filter(function ($gateway) use ($enabledModules) {
            // If no handler class or starts with 'core:', it's a core gateway - always show
            if (empty($gateway->handler_class) || str_starts_with($gateway->handler_class, 'core:')) {
                return true;
            }
            
            // Check if module is enabled based on handler_class namespace
            // e.g. Modules\Polyx\PaypalGateway\PaypalGateway matches Polyx.PaypalGateway
            foreach ($enabledModules as $moduleKey) {
                // Normalize Polyx.SepayGateway to Polyx\SepayGateway
                $normalizedNamespace = str_replace(['/', '.'], '\\', strtolower($moduleKey));
                
                if (stripos($gateway->handler_class, $normalizedNamespace) !== false) {
                    return true;
                }
            }
            
            return false;
        });

        return response()->json([
            'success' => true,
            'data' => $filteredGateways->values(),
        ]);
    }

    /**
     * Ensure core payment gateways (COD, Bank Transfer) exist.
     * Auto-seeds on first access — idempotent via firstOrCreate.
     */
    protected function ensureCoreGateways(): void
    {
        try {
            PaymentGateway::firstOrCreate(
                ['code' => 'cod'],
                [
                    'name' => 'Cash on Delivery (COD)',
                    'description' => 'Pay with cash when your order is delivered',
                    'is_active' => false,
                    'handler_class' => 'core:cod',
                    'sort_order' => 10,
                    'config' => [
                        'instructions' => 'Please have exact amount ready for the delivery person.',
                        'min_order_amount' => 0,
                        'max_order_amount' => 0,
                        'additional_fee' => 0,
                        'fee_type' => 'fixed',
                        'available_areas' => '',
                    ],
                ]
            );

            PaymentGateway::firstOrCreate(
                ['code' => 'bank_transfer'],
                [
                    'name' => 'Bank Transfer',
                    'description' => 'Pay by transferring money directly to our bank account',
                    'is_active' => false,
                    'handler_class' => 'core:bank_transfer',
                    'sort_order' => 11,
                    'config' => [
                        'instructions' => 'Please transfer the total amount to one of our bank accounts listed below. Include your order number in the transfer description.',
                        'banks' => [],
                    ],
                ]
            );
        } catch (\Exception $e) {
            // Table may not be ready
        }
    }

    public function show($id): JsonResponse
    {
        $gateway = PaymentGateway::findOrFail($id);

        $schema = $this->inferConfigSchema(is_array($gateway->config) ? $gateway->config : []);
        $schema = Hook::applyFilters('payment.gateway.config_schema', $schema, $gateway);

        $payload = $gateway->toArray();
        $payload['config_schema'] = $schema;

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $gateway = PaymentGateway::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
            'logo' => 'sometimes|nullable|string|max:500',
            'config' => 'sometimes|array',
        ]);

        // Update basic fields
        if (isset($validated['name'])) {
            $gateway->name = $validated['name'];
        }
        
        if (array_key_exists('description', $validated)) {
            $gateway->description = $validated['description'];
        }
        
        if (isset($validated['is_active'])) {
            $gateway->is_active = $validated['is_active'];
        }
        
        if (isset($validated['sort_order'])) {
            $gateway->sort_order = $validated['sort_order'];
        }
        
        if (array_key_exists('logo', $validated)) {
            $gateway->logo = $validated['logo'];
        }

        // Merge config - handle both string (legacy) and array formats
        if (isset($validated['config'])) {
            // Get current config, parsing if it's a string
            $currentConfig = $gateway->config;
            if (is_string($currentConfig)) {
                $currentConfig = json_decode($currentConfig, true) ?? [];
            }
            if (!is_array($currentConfig)) {
                $currentConfig = [];
            }
            
            // Merge with new config (new config replaces old values)
            $gateway->config = array_merge($currentConfig, $validated['config']);
        }

        $gateway->save();

        return response()->json([
            'success' => true,
            'message' => 'Gateway updated successfully',
            'data' => $gateway->fresh(),
        ]);
    }

    /**
     * Reorder payment gateways
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'string',
        ]);

        foreach ($validated['order'] as $index => $code) {
            PaymentGateway::where('code', $code)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
        ]);
    }

    /**
     * Set default payment gateway
     */
    public function setDefault($code): JsonResponse
    {
        // Clear current default
        PaymentGateway::where('is_default', true)->update(['is_default' => false]);
        
        // Set new default
        $gateway = PaymentGateway::findOrFail($code);
        $gateway->is_default = true;
        $gateway->save();

        return response()->json([
            'success' => true,
            'message' => 'Default gateway set successfully',
            'data' => $gateway,
        ]);
    }

    /**
     * Build a default config schema from existing config values.
     *
     * @param array<string, mixed> $config
     * @return array<int, array<string, mixed>>
     */
    protected function inferConfigSchema(array $config): array
    {
        $schema = [];
        $order = 10;

        foreach ($config as $key => $value) {
            $lower = strtolower((string) $key);
            $isSensitive = str_contains($lower, 'secret')
                || str_contains($lower, 'password')
                || str_contains($lower, 'token')
                || str_contains($lower, 'api_key')
                || str_contains($lower, 'client_key')
                || str_ends_with($lower, '_key');

            $type = 'text';
            if (is_bool($value)) {
                $type = 'boolean';
            } elseif (is_int($value) || is_float($value)) {
                $type = 'number';
            } elseif (is_array($value) || is_object($value)) {
                $type = 'json';
            }

            $schema[] = [
                'key' => $key,
                'label' => str_replace('_', ' ', ucfirst((string) $key)),
                'type' => $type,
                'sensitive' => $isSensitive,
                'order' => $order,
            ];
            $order += 10;
        }

        return $schema;
    }
}
