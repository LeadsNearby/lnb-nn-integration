<?php

namespace lnb\nn;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit('Direct script access denied.');
}

class ApiCache {
    private static $transient_base = 'nearby_now_';
    private static $cached_routes_option = 'nearby_now_routes_cached';

    private static $instance = null;

    private function __construct() {
        // if (!is_network_admin()) {
        //     add_action('wp_before_admin_bar_render', [$this, 'add_admin_bar_node'], 99);
        // }
    }

    // public function add_admin_bar_node() {
    //     $nonce = wp_create_nonce('clear-fire-cache');
    //     global $wp_admin_bar;
    //     global $pagenow;
    //     $site_path = get_blog_details(get_current_blog_id())->path;
    //     $href = $_SERVER['REQUEST_URI'] . (parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ? '&' : '?') . 'clear_fire_api_cache=true&_wpnonce=' . $nonce . '&path=' . urlencode(\str_replace($site_path . 'wp-admin/', '', $_SERVER['REQUEST_URI']));
    //     $wp_admin_bar->add_node(array(
    //         'id' => 'fire-cache-clear',
    //         'title' => 'Clear fire API Cache',
    //         'href' => $href,
    //     ));
    // }

    public static function get_instance() {
        if (static::$instance == null) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    public static function get_cached_routes() {
        $routes = get_option(self::$cached_routes_option, array());
        return $routes;
    }

    public static function clear_cache($route) {
        $transient = self::$transient_base . $route;
        $cached_routes = self::get_cached_routes();
        $index = array_search($route, $cached_routes);
        if ($index >= 0) {
            unset($cached_routes[$index]);
        }
        $option = $option = self::$cached_routes_option;
        update_option($option, $cached_routes);
        return delete_transient($transient);
    }

    public static function clear_all_cache() {
        $cached_routes = self::get_cached_routes();
        foreach ($cached_routes as $route) {
            $transient = self::$transient_base . $route;
            delete_transient($transient);
        }
    }

    public static function set_cache($route, $value, $timeout = 12 * HOUR_IN_SECONDS) {
        $cached_routes = self::get_cached_routes();
        $option = self::$cached_routes_option;
        if (!in_array($route, $cached_routes)) {
            $cached_routes[] = $route;
        }
        update_option($option, $cached_routes);

        $transient = self::$transient_base . $route;
        return set_transient($transient, $value, $timeout);
    }

    public static function get_cache($route) {
        $transient = self::$transient_base . $route;
        $transient_value = get_transient($transient);
        // if ($transient_value) {
        //     $transient_value['cache'] = true;
        // }
        return $transient_value;
    }
}
