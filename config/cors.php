<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| Here you may configure your settings for cross-origin resource sharing
| or "CORS". This determines what cross-origin operations may execute
| in web browsers. You are free to adjust these settings as needed.
|
| Modules can dynamically extend these settings via PolyCMS filter hooks:
| - 'polycms_cors_paths'
| - 'polycms_cors_supports_credentials'
| - 'polycms_cors_allowed_origins_patterns'
|
*/

$paths = ['api/*', 'sanctum/csrf-cookie'];
$supportsCredentials = false;
$allowedOriginsPatterns = [];

if (function_exists('apply_filters')) {
    $paths = apply_filters('polycms_cors_paths', $paths);
    $supportsCredentials = (bool) apply_filters('polycms_cors_supports_credentials', $supportsCredentials);
    $allowedOriginsPatterns = (array) apply_filters('polycms_cors_allowed_origins_patterns', $allowedOriginsPatterns);
}

return [
    'paths' => is_array($paths) ? array_values(array_unique($paths)) : ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => is_array($allowedOriginsPatterns) ? $allowedOriginsPatterns : [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => $supportsCredentials,
];
