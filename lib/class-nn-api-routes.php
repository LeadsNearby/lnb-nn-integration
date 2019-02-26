<?php

namespace lnb\core;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit('Direct script access denied.');
}

use \lnb\nn\ApiCache as ApiCache;
use \stdClass;
use \WP_Error;
use \WP_REST_Controller;
use \WP_REST_Response;
use \WP_REST_Server;

class NNApiRoutes extends WP_REST_Controller {

    private static $instance = null;
    private $api = null;

    private function __construct($api_object) {
        $this->api = $api_object;
    }

    public static function get_instance($api_object) {
        if (self::$instance == null) {
            self::$instance = new self($api_object);
        }
        return self::$instance;
    }

    public function register_routes() {
        $version = '1';
        $namespace = 'nn/v1';
        $routes = array(
            array(
                'path' => '/overview',
                'callback' => 'get_all_data',
            ),
            array(
                'path' => '/aggregate',
                'callback' => 'get_aggregate_data',
            ),
            array(
                'path' => '/cities',
                'callback' => 'get_cities',
            ),
            array(
                'path' => '/cities/main',
                'callback' => 'get_main_page_data',
            ),
            array(
                'path' => '/cities/(?P<city>[\w\-]+)',
                'callback' => 'get_city_data',
            ),
        );
        foreach ($routes as $index => $route) {
            register_rest_route($namespace, $route['path'], array(
                'methods' => !empty($route['methods']) ? $route['methods'] : WP_REST_Server::READABLE,
                'callback' => array($this, $route['callback']),
                'permission_callback' => !empty($route['permission_callback']) ? $route['permission_callback'] : null,
            ));
        }
    }

    public function get_all_data() {
        $response = $this->api->get_data();

        if (wp_get_theme('fire')->exists()) {
            $options = get_option('fire_options');
            $response['company']['priceRange'] = $options['priceRange'];
            $response['company']['telephone'] = $options['phone'];
        }

        return new WP_REST_Response($response, 200);
    }

    public function get_aggregate_data() {
        $response = $this->api->get_data()['aggregateRating'];
        return new WP_REST_Response($response, 200);
    }

    private function insert_location_data($request, $string) {
        return str_replace(['{city}', '{state}', '{company}'], [$request['city'], $request['state'], get_bloginfo('name')], $string);
    }

    public function get_cities() {
        $cache = ApiCache::get_cache('cities');
        if ($cache) {
            return $this->return_cache_response($cache);
        }
        $response = $this->api->get_data();
        $cities = $response['cities'];
        if (wp_get_theme('fire')->exists()) {
            $options = get_option('fire_options');
            foreach ($cities as $index => $city) {
                $cities[$index]['permalink'] = sanitize_title($this->insert_location_data(array('city' => $city['city'], 'state' => $city['state']), $options['nnSlugTemplate']));
            }
        }
        ApiCache::set_cache('cities', $cities, 24 * HOUR_IN_SECONDS);
        return new WP_REST_Response($cities, 200);
    }

    public function get_main_page_data() {
        $cache = ApiCache::get_cache('main_page');
        if ($cache) {
            return $this->return_cache_response($cache);
        }

        if (!wp_get_theme('fire')->exists()) {
            return false;
        }
        $data = $this->api->get_data();
        $response = array(
            'cities' => $data['cities'],
        );
        $options = get_option('fire_options');
        foreach ($response['cities'] as $index => $city) {
            $response['cities'][$index]['permalink'] = sanitize_title($this->insert_location_data(array('city' => $city['city'], 'state' => $city['state']), $options['nnSlugTemplate']));
        }
        // Add meta to response
        $response['meta'] = array(
            'title' => $this->insert_location_data($response, $options['nnMainMetaTitle']),
            'desc' => $this->insert_location_data($response, $options['nnMainMetaDesc']),
        );
        $response['slug'] = $this->insert_location_data(array('city' => 'Main', 'state' => 'TA'), $options['nnMain']);
        // Add title to response
        $response['content'] = $this->insert_location_data($response, $options['nnMainContent']);
        // Add content to response
        $response['title'] = $this->insert_location_data($response, $options['nnMainTitle']);

        ApiCache::set_cache('main_page', $response, 24 * HOUR_IN_SECONDS);
        return new WP_REST_Response($response, 200);
    }

    public function get_city_data(\WP_REST_Request $request) {
        $cache = ApiCache::get_cache($request['city']);
        if ($cache) {
            return $this->return_cache_response($cache);
        }
        $response = $this->api->get_city_data($request['city']);
        if (wp_get_theme('fire')->exists()) {
            // Add meta to response
            $options = get_option('fire_options');
            $response['meta'] = array(
                'title' => $this->insert_location_data($response, $options['nnMetaTitle']),
                'desc' => $this->insert_location_data($response, $options['nnMetaDesc']),
            );

            $response['company']['priceRange'] = $options['priceRange'];
            $response['company']['telephone'] = $options['phone'];

            // Add page title to response
            $response['title'] = $this->insert_location_data($response, $options['nnPageTitle']);
            // Add content to response
            $response['content'] = $this->insert_location_data($response, $options['nnContent']);
        }
        if (!$response) {
            return new WP_Error('rest_post_invalid_city', __('Invalid City'), array('status' => 404));
        }
        ApiCache::set_cache($request['city'], $response, 24 * HOUR_IN_SECONDS);
        return new WP_REST_Response($response, 200);
    }

    private function return_cache_response($cache) {
        $response = new WP_REST_Response($cache, 200);
        $response->header('X-Api-Cache', 'hit');
        return $response;
    }

}
