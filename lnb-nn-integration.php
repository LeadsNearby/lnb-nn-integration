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

$fire_options = get_option('fire_options');
$nn_options = get_option('nearbynow_options');
if (isset($fire_options['nnApiKey'])) {
    $apikey = $fire_options['nnApiKey'];
} else {
    $apikey = $nn_options['text_string'];
}
if ($apikey) {
    $nn_api = new NNApi($apikey);
    $api_routes = NNApiRoutes::get_instance($nn_api);

    add_action('rest_api_init', [$api_routes, 'register_routes']);
    // add_action('save_post', array($nn_api, 'clear_cache'));
    // add_action( 'wp_update_nav_menu', array( 'NN_API', 'reset_cache' ) );
    add_action('after_rocket_clean_cache_dir', array($nn_api, 'clear_cache'));

    require_once plugin_dir_path(__FILE__) . '/lib/class-nn-widget.php';
    new NN_Static_Widget($nn_api);
    require_once plugin_dir_path(__FILE__) . '/lib/class-nn-testimonial-widget.php';
    new NN_Testimonial_Widget($nn_api);
}

add_action('admin_init', function () {
    if (class_exists('\lnb\core\GitHubPluginUpdater')) {
        new \lnb\core\GitHubPluginUpdater(__FILE__, 'lnb-nn-integration');
    }
}, 99);
