<?php

/*
Plugin Name: LeadsNearby Nearby Now Stynamic Integration
Description: Includes an API class that gets and stores Nearby Now data in the database for easy retrieval. Also includes a widget.
Version: 1.3.0
Author: LeadsNearby (Andrew Gillingham)
 */

require_once plugin_dir_path(__FILE__) . '/lib/class-nn-api.php';
require_once plugin_dir_path(__FILE__) . '/lib/class-nn-widget.php';
require_once plugin_dir_path(__FILE__) . '/lib/class-nn-testimonial-widget.php';

new NN_Static_Widget();
new NN_Testimonial_Widget();
// add_action( 'save_post', array( 'NN_API', 'reset_cache' ) );
add_action('wp_update_nav_menu', array('NN_API', 'reset_cache'));
add_action('after_rocket_clean_cache_dir', array('NN_API', 'reset_cache'));

require_once plugin_dir_path(__FILE__) . '/lib/updater/github-updater.php';

add_action('admin_init', function () {
    if (class_exists('\lnb\core\GitHubPluginUpdater')) {
        new \lnb\core\GitHubPluginUpdater(__FILE__, 'lnb-nn-integration');
    }
}, 99);
