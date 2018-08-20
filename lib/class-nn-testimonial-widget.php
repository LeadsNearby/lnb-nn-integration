<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists( 'NN_Testimonial_Widget' ) ) :

	class NN_Testimonial_Widget {

		function __construct() {

			add_shortcode( 'dyn-test-widget', [ $this, 'get_html' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'register_styles' ] );

		}

		function register_styles() {
			wp_register_style( 'dyn-test-widget-styles', plugins_url( 'assets/css/testimonial.css', dirname( __FILE__ ) ), array(), null );
		}

		function get_html( $shortcode_atts ) {

			extract( shortcode_atts(
				array(
                    'name' => 'true',
					'type' => 'block',
					'size' => 'medium',
					'accent' => '#000',
					'stars' => '#faab5b',
					'background' => '#efefef',
				),
				$shortcode_atts,
				'dyn-test-widget'
			) );

			$type = ! empty( $type ) ? $type : 'block';

			$css_widget_vars = array(
				'--accent-color' => $accent,
				'--stars-color' => $stars,
				'--background' => $background,
			);

			$css_widget_string = '';

			foreach( $css_widget_vars as $key => $value ) {
				if( $value ) {
					$css_widget_string .= $key . ':' . $value . ';'; 
				}
			}
			
			$nn_data = array();
			global $post;
			$html;

			if( class_exists( 'NN_API' ) ) {

				$nn_data = NN_API::get_data();

			} else {

				$html = "This widget requires the NN_API class";
			}
            
			if( ! empty( $nn_data ) ) {
			    
			    $fiveStarReviews = array_values(array_filter($nn_data['reviews'], [$this, 'findFive']));

                wp_enqueue_style( 'dyn-test-widget-styles' );
                
				ob_start(); ?>
                <pre><?php /*print_r ($nn_data); */?></pre>
				<div class="lnbTestimonialsWidget lnbTestimonialsWidget--<?php echo $type; ?>"<?php if( $css_widget_string ) { ?> style="<?php echo $css_widget_string; ?>"<?php } ?>>
                    <?php foreach($fiveStarReviews as $index => $review){                    
                        if ($index == 3){
                        break;}?>
						<div class="lnbTestimonialsWidget__review">
							<span class="lnbTestimonialsWidget__starsContainer">
								<?php echo file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . '/assets/svg-stars.svg' ); ?>
							</span>
							<div class="lnbTestimonialsWidget__content">
								<span class="lnbTestimonialsWidget__author"><span class="lnbTestimonialsWidget__authorText"><?php echo $review['author']['name'];?></span></span>
                       			<span class="lnbTestimonialsWidget__name"><?php echo $review['name'];?></span>
								
								<span class="lnbTestimoniasWidget__meta"><span class="lnbTestimonialsWidget__metaLocation"><i class="fal fa-map-pin"></i><?php echo $review['author']['address']['addressLocality'];?></span></span>
							</div>
						</div>
                    <?php } ?>
				</div>

				<?php $html = ob_get_clean();

			} else {

				$html = 'Error retrieving NearbyNow data';

			}

			return $html;

		}
        function findFive($review){
            return ($review['reviewRating']['ratingValue'] == 5);
        }
	}

endif;