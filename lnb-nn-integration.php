<?php

/*
Plugin Name: LeadsNearby Nearby Now Stynamic Integration
Description: Includes an API class that gets and stores Nearby Now data in the database for easy retrieval. Also includes a widget.
Version: 2.0.0
Author: LeadsNearby (Andrew Gillingham)
 */

require_once plugin_dir_path(__FILE__) . '/lib/class-nn-api.php';
require_once plugin_dir_path(__FILE__) . '/lib/class-nn-api-routes.php';
use \lnb\core\NnApi;
use \lnb\core\NnApiRoutes;

$nn_options = get_option('nearbynow_options');
$apikey = $nn_options['text_string'];
if ($apikey) {
    $nn_api = new NNApi('8a249b3e-d870-415b-8185-9c51c986aa93');
    $api_routes = NNApiRoutes::get_instance($nn_api);

    add_action('rest_api_init', [$api_routes, 'register_routes']);
    // add_action( 'save_post', array( 'NN_API', 'reset_cache' ) );
    // add_action( 'wp_update_nav_menu', array( 'NN_API', 'reset_cache' ) );
    add_action('after_rocket_clean_cache_dir', array($nn_api, 'clear_cache'));

    require_once plugin_dir_path(__FILE__) . '/lib/class-nn-widget.php';
    new NN_Static_Widget($nn_api);
}

add_action('admin_init', function () {
    if (class_exists('\lnb\core\GitHubPluginUpdater')) {
        new \lnb\core\GitHubPluginUpdater(__FILE__, 'lnb-nn-integration');
    }
}, 99);
