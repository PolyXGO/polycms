<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait HasDynamicImageUrls
 * 
 * Automatically adjusts absolute image URLs stored in the database
 * to use the current application host.
 */
trait HasDynamicImageUrls
{
    /**
     * Fix the image URL host based on the current request
     * 
     * @param string|null $value
     * @return string|null
     */
    protected function fixImageUrl(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // If it's a full URL, ensure it uses the current host
        if (str_starts_with($value, 'http')) {
            // Use request() if available, otherwise fallback to app URL config
            $currentHost = function_exists('request') && request() 
                ? request()->getSchemeAndHttpHost() 
                : rtrim(config('app.url'), '/');
                
            $parts = parse_url($value);
            $path = $parts['path'] ?? '';
            
            // If it's a local storage path, swap the host
            if (str_contains($path, '/storage/')) {
                return $currentHost . $path;
            }
        }

        return $value;
    }
}
