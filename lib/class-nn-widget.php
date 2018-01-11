<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists( 'NN_Static_Widget' ) ) :

	class NN_Static_Widget {

		function __construct() {

			add_shortcode( 'static-nn-widget', [ $this, 'get_html' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'register_styles' ] );

		}

		function register_styles() {
			wp_register_style( 'lnb-reviews-widget-styles', plugins_url( 'assets/css/style.css', dirname( __FILE__ ) ), array(), null );
		}

		function get_html( $shortcode_atts ) {

			extract(
				shortcode_atts(
					array(
						'type' => null
						),
					$shortcode_atts
					)
				);
			
			$nn_data = array();
			global $post;
			$html;

			if( class_exists( 'NN_API' ) ) {

				$nn_data = NN_API::get_data();

			} else {

				$html = "This widget requires the NN_API class";
			}

			if( !empty( $nn_data ) ) {

				if( $post->ID != 4 && $post->post_parent != 4 ) :

					wp_enqueue_style( 'lnb-reviews-widget-styles' );

					ob_start(); ?>

					<div class="lnbReviewsWidget">
						<h3 class="lnbReviewsWidget__title"><?php echo $nn_data['name']; ?></h3>
						<?php echo file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . '/assets/svg-stars.svg' ); ?>
						<span class="lnbReviewsWidget__data">Rated <?php echo $nn_data['rating']; ?> out of <?php echo $nn_data['count']; ?> reviews</span>
					</div>

					<?php $html = ob_get_clean();

				endif;

			} else {

				$html = 'Error retrieving NearbyNow data';

			}

			return $html;

		}

	}

endif;