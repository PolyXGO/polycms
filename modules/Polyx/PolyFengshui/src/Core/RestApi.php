<?php
namespace Modules\Polyx\PolyFengshui\Core;

defined('ABSPATH') || exit;

class RestApi {
    public static function register_routes() {
        $controllers = [
            'Modules\Polyx\PolyFengshui\Api\MovingDateController',
            'Modules\Polyx\PolyFengshui\Api\MovingDateLookupController',
            'Modules\Polyx\PolyFengshui\Api\DateController',
        ];

        foreach ($controllers as $controller) {
            if (class_exists($controller)) {
                (new $controller())->register_routes();
            }
        }
    }

    public static function add_custom_rewrite_rules() {
        add_rewrite_rule('^api/(.*)?', 'index.php?rest_route=/$matches[1]', 'top');
    }

    public static function modify_rest_url($url, $path, $blog_id, $scheme) {
        if (strpos($path, 'feng-shui/v2/') === 0) {
            $url_api = home_url('api/' . str_replace('wp-json/', '', $path), $scheme);
            return $url_api;
        }
        return $url;
    }
}

// Register API routes to `wp-json/`
add_action('rest_api_init', ['Modules\Polyx\PolyFengshui\Core\RestApi', 'register_routes']);

// Add rewrite rule to `init`
add_action('init', ['Modules\Polyx\PolyFengshui\Core\RestApi', 'add_custom_rewrite_rules']);

// Overwrite `rest_url()` use `/api/` & `wp-json/`
add_filter('rest_url', ['Modules\Polyx\PolyFengshui\Core\RestApi', 'modify_rest_url'], 10, 4);
