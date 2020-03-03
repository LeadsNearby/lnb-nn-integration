<?php

/*
Plugin Name: LeadsNearby Nearby Now Stynamic Integration
Description: Includes an API class that gets and stores Nearby Now data in the database for easy retrieval. Also includes a widget.
Version: 2.4.0
Author: LeadsNearby (Andrew Gillingham)
 */

require_once plugin_dir_path(__FILE__) . '/lib/class-nn-api.php';
require_once plugin_dir_path(__FILE__) . '/lib/class-nn-api-routes.php';
require_once plugin_dir_path(__FILE__) . '/lib/class-nn-api-routes-cache.php';
use \lnb\core\NnApi;
use \lnb\core\NnApiRoutes;

function lnb_get_nn_api_key() {
    $fire_options = get_option('fire_options');
    $nn_options = get_option('nearbynow_options');
    if (isset($fire_options['nnApiKey'])) {
        $apikey = $fire_options['nnApiKey'];
    } else {
        $apikey = $nn_options['text_string'];
    }

    return $apikey;

}

$apikey = lnb_get_nn_api_key();
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

    $nn_data = $nn_api->get_data();

}

function wpseo_filter_in_nn_data($graph_piece) {
    $nn_api_ley = lnb_get_nn_api_key();
    if (!$nn_api_ley) {
        return;
    }
    $nn_api = $nn_api = new NNApi($nn_api_ley);
    $nn_data = $nn_api->get_data();

    // Override type with type from nn
    if ($nn_data['type'] !== $graph_piece['@type']) {
        $graph_piece['@type'] = $nn_data['type'];
    }

    // Add aggregate rating and review count
    if (!empty($nn_data['aggregateRating'])) {
        $graph_piece['aggregateRating'] = $nn_data['aggregateRating'];
    }

    return $graph_piece;
}

if (wp_get_theme()->get('Template') == 'hypercore') {
    add_filter('wpseo_schema_organization', 'wpseo_filter_in_nn_data', 15);
}

add_action('admin_init', function () {
    if (class_exists('\lnb\core\GitHubPluginUpdater')) {
        new \lnb\core\GitHubPluginUpdater(__FILE__, 'lnb-nn-integration');
    }
}, 99);
