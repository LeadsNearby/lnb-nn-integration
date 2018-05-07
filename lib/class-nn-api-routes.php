<?php

namespace lnb\core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct script access denied.' );
}

use \stdClass, \WP_REST_Controller, \WP_REST_Response, \WP_REST_Server, \WP_Error;

class NNApiRoutes extends WP_REST_Controller {

    private static $instance = null;
    private $api = null;

    private function __construct( $api_object ) {
        $this->api = $api_object;
    }

    public static function get_instance( $api_object ) {
        if( self::$instance == null) {
            self::$instance = new self( $api_object );
        }
        return self::$instance;
    }

    public function register_routes() {
        $version = '1';
        $namespace = 'nn/';
        $routes = array(
            array(
                'path' => 'v1',
                'callback' => 'get_all_data'
            ),
            array(
                'path' => 'v1/(?P<city>[\w\-]+)',
                'callback' => 'get_city_data'
            ),
        );
        foreach( $routes as $index => $route ) {
            register_rest_route( $namespace, $route['path'], array(
                'methods' => ! empty( $route['methods'] ) ? $route['methods'] : WP_REST_Server::READABLE,
                'callback' => array( $this, $route['callback'] ),
                'permission_callback' => ! empty( $route['permission_callback'] ) ? $route['permission_callback'] : null
            ) );
        }
    }

    public function get_all_data() {
        $response = $this->api->get_data();
        return new WP_REST_Response( $response, 200 );
    }

    public function get_city_data( \WP_REST_Request $request ) {
        $response = $this->api->get_city_data( $request['city'] );
        if( ! $response ) {
            return new WP_Error( 'rest_post_invalid_city', __('Invalid City'), array( 'status' => 404 ) );
        }
        return new WP_REST_Response( $response, 200 );
    }

}

?>