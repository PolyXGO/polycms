<?php

declare(strict_types=1);

namespace Modules\Polyx\Google2FA;

use App\Facades\Hook;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\JsonResponse;

class Google2FAServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register any service bindings here
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register admin menu items (will be refined later if needed)
        
        // Register routes
        $this->loadRoutes();

        // Register migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Intercept login flow
        Hook::addFilter('auth.login.pre_token', function ($response, $user, $request) {
            $settings = \Modules\Polyx\Google2FA\Models\Google2FASetting::where('user_id', $user->id)->first();
            
            if ($settings && $settings->google2fa_enabled) {
                return response()->json([
                    'message' => 'Two-factor authentication required.',
                    'two_factor_required' => true,
                    'user_id' => $user->id,
                    'email' => $user->email,
                ], 403);
            }
            
            return $response;
        }, 10, 3);

        // Add 2FA status to UserResource
        Hook::addFilter('user.resource.to_array', function ($data, $user, $request) {
            $settings = \Modules\Polyx\Google2FA\Models\Google2FASetting::where('user_id', $user->id)->first();
            $data['google2fa_enabled'] = $settings ? $settings->google2fa_enabled : false;
            return $data;
        }, 10, 3);

        // Register filter to add documentation to module listing
        Hook::addFilter('module.resource.meta', function (array $meta, array $module) {
            if ($module['key'] === 'Polyx.Google2FA') {
                $meta['actions'] = [
                    [
                        'type' => 'modal',
                        'label' => [
                            'en' => '2FA Setup Guide',
                            'vi' => 'Hướng dẫn thiết lập 2FA'
                        ],
                        'modal' => [
                            'title' => [
                                'en' => '2FA Setup Guide',
                                'vi' => 'Hướng dẫn thiết lập 2FA'
                            ],
                            'size' => 'md',
                            'content' => [
                                'en' => '<div class="space-y-4 text-gray-700 dark:text-gray-300">' .
                                    '<p><strong>Follow these steps to secure your account:</strong></p>' .
                                    '<ul class="space-y-3">' .
                                    '<li class="flex items-start gap-3"><span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">1</span> <span>Install a 2FA app (Google Authenticator, Authy, etc.).</span></li>' .
                                    '<li class="flex items-start gap-3"><span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">2</span> <span>Scan the QR code in your profile settings.</span></li>' .
                                    '<li class="flex items-start gap-3"><span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">3</span> <span>Enter the 6-digit code to verify and enable 2FA.</span></li>' .
                                    '</ul>' .
                                    '<div class="p-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-2xl mt-6">' .
                                    '<p class="text-sm font-bold text-gray-900 dark:text-white mb-0.5">Recovery Codes</p>' .
                                    '<p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">Save your recovery codes in a safe place. If you lose your phone, you will need them to access your account.</p>' .
                                    '</div>' .
                                    '</div>',
                                'vi' => '<div class="space-y-4 text-gray-700 dark:text-gray-300">' .
                                    '<p><strong>Hãy làm theo các bước sau để bảo mật tài khoản của bạn:</strong></p>' .
                                    '<ul class="space-y-3">' .
                                    '<li class="flex items-start gap-3"><span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">1</span> <span>Cài đặt ứng dụng 2FA (Google Authenticator, Authy, v.v.).</span></li>' .
                                    '<li class="flex items-start gap-3"><span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">2</span> <span>Quét mã QR trong phần cài đặt cá nhân.</span></li>' .
                                    '<li class="flex items-start gap-3"><span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">3</span> <span>Nhập mã 6 chữ số để xác minh và kích hoạt 2FA.</span></li>' .
                                    '</ul>' .
                                    '<div class="p-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-2xl mt-6">' .
                                    '<p class="text-sm font-bold text-gray-900 dark:text-white mb-0.5">Mã khôi phục</p>' .
                                    '<p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">Hãy lưu các mã khôi phục ở nơi an toàn. Nếu bạn mất điện thoại, bạn sẽ cần chúng để truy cập tài khoản.</p>' .
                                    '</div>' .
                                    '</div>'
                            ]
                        ]
                    ]
                ];
            }
            return $meta;
        }, 10, 2);
    }

    /**
     * Load module routes
     */
    protected function loadRoutes(): void
    {
        $this->app['router']->middleware(['api'])
            ->prefix('api/v1/auth/google2fa')
            ->name('api.v1.auth.google2fa.')
            ->group(function () {
                $this->app['router']->post('verify', [Controllers\Google2FAController::class, 'verify'])->name('verify');
            });
            
        $this->app['router']->middleware(['api', 'auth:sanctum'])
            ->prefix('api/v1/profile/google2fa')
            ->name('api.v1.profile.google2fa.')
            ->group(function () {
                $this->app['router']->get('qr-code', [Controllers\Google2FAController::class, 'getQrCode'])->name('qr-code');
                $this->app['router']->post('enable', [Controllers\Google2FAController::class, 'enable'])->name('enable');
                $this->app['router']->post('disable', [Controllers\Google2FAController::class, 'disable'])->name('disable');
                $this->app['router']->get('recovery-codes', [Controllers\Google2FAController::class, 'getRecoveryCodes'])->name('recovery-codes');
            });
    }
}
