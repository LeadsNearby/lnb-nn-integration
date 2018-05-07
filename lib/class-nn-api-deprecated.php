<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use \lnb\core\NNApi;

if( ! class_exists( 'NN_API' ) ) :

	class NN_API {

		public static function get_data() {

			$nn_options = get_option('nearbynow_options');
			$apikey = $nn_options['text_string'];

			$nn_api = new NNApi( $apikey );

			return $nn_api->get_data();

		}

		public static function reset_cache() {

			$nn_options = get_option('nearbynow_options');
			$apikey = $nn_options['text_string'];

			$nn_api = new NNApi( $apikey );

			return $nn_api->clear_cache();

		}

	}

endif;