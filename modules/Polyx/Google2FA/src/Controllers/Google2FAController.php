<?php

declare(strict_types=1);

namespace Modules\Polyx\Google2FA\Controllers;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Polyx\Google2FA\Models\Google2FASetting;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class Google2FAController extends Controller
{
    /**
     * Verify 2FA code and issue Sanctum token
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => ['required', 'exists:users,id'],
                'one_time_password' => ['required', 'string'],
            ]);

            $user = User::findOrFail($request->user_id);
            $settings = Google2FASetting::where('user_id', $user->id)->first();

            if (!$settings || !$settings->google2fa_enabled) {
                return $this->errorResponse(_l('2FA is not enabled for this user.'), 'TWO_FACTOR_NOT_ENABLED');
            }

            $isValid = Google2FA::verifyKey($settings->google2fa_secret, $request->one_time_password);

            // If not a valid TOTP, check if it's a recovery code
            if (!$isValid && !empty($settings->recovery_codes)) {
                $recoveryCodes = is_array($settings->recovery_codes) ? $settings->recovery_codes : [];
                $index = array_search(strtoupper($request->one_time_password), $recoveryCodes);
                
                if ($index !== false) {
                    $isValid = true;
                    // Remove the used recovery code
                    unset($recoveryCodes[$index]);
                    $settings->recovery_codes = array_values($recoveryCodes);
                    $settings->save();
                }
            }

            if (!$isValid) {
                return $this->unauthorizedResponse(_l('Invalid 2FA code or recovery code.'));
            }

            $deviceName = $request->device_name ?? $request->userAgent() ?? 'API Client';
            $token = $user->createToken($deviceName)->plainTextToken;

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], _l('2FA verification successful'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'VERIFY_2FA_FAILED', [], 500);
        }
    }

    /**
     * Get QR code for setup
     */
    public function getQrCode(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $settings = Google2FASetting::firstOrCreate(['user_id' => $user->id]);

            if (!$settings->google2fa_secret) {
                $settings->google2fa_secret = Google2FA::generateSecretKey();
                $settings->save();
            }

            $brandName = app(\App\Services\SettingsService::class)->get('brand_name', config('app.name'));

            $qrCodeUrl = Google2FA::getQRCodeUrl(
                $brandName,
                $user->email,
                $settings->google2fa_secret
            );

            // Use a public QR code API to avoid local service dependencies (module standalone)
            $qrCodeImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrCodeUrl);
            
            // Format as <img> tag for v-html compatibility in Profile.vue
            $qrCodeTag = '<img src="' . $qrCodeImageUrl . '" alt="QR Code" class="mx-auto" />';

            return $this->successResponse([
                'qr_code' => $qrCodeTag,
                'qr_code_url' => $qrCodeUrl,
                'secret' => $settings->google2fa_secret,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                _l('Failed to generate 2FA QR code: ') . $e->getMessage(),
                'QR_CODE_GENERATION_FAILED',
                [],
                500
            );
        }
    }

    /**
     * Enable 2FA after verification
     */
    public function enable(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'one_time_password' => ['required', 'string'],
            ]);

            $user = $request->user();
            $settings = Google2FASetting::where('user_id', $user->id)->first();

            if (!$settings || !$settings->google2fa_secret) {
                return $this->errorResponse(_l('Secret not generated. Please get QR code first.'), 'SECRET_NOT_FOUND');
            }

            $isValid = Google2FA::verifyKey($settings->google2fa_secret, $request->one_time_password);

            if (!$isValid) {
                return $this->unauthorizedResponse(_l('Invalid 2FA code.'));
            }

            $settings->google2fa_enabled = true;
            
            // Generate recovery codes if not already present
            if (empty($settings->recovery_codes)) {
                $codes = [];
                for ($i = 0; $i < 8; $i++) {
                    $codes[] = strtoupper(bin2hex(random_bytes(5))); // 10 chars each
                }
                $settings->recovery_codes = $codes;
            }
            
            $settings->save();

            return $this->successResponse([
                'recovery_codes' => $settings->recovery_codes
            ], _l('Two-factor authentication enabled successfully'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ENABLE_2FA_FAILED', [], 500);
        }
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'password' => ['required', 'string'],
            ]);

            $user = $request->user();
            
            if (!Hash::check($request->password, $user->password)) {
                return $this->unauthorizedResponse(_l('Invalid password.'));
            }

            $settings = Google2FASetting::where('user_id', $user->id)->first();

            if ($settings) {
                $settings->google2fa_enabled = false;
                $settings->save();
            }

            return $this->successResponse(null, _l('Two-factor authentication disabled successfully'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'DISABLE_2FA_FAILED', [], 500);
        }
    }
    /**
     * Get recovery codes
     */
    public function getRecoveryCodes(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $settings = Google2FASetting::where('user_id', $user->id)->first();

            if (!$settings || !$settings->google2fa_enabled) {
                return $this->errorResponse(_l('2FA is not enabled.'), 'TWO_FACTOR_NOT_ENABLED');
            }

            return $this->successResponse([
                'recovery_codes' => $settings->recovery_codes
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'GET_RECOVERY_CODES_FAILED', [], 500);
        }
    }
}
