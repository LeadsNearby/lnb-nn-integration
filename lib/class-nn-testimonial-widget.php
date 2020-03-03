<?php

if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

use \lnb\core\NNApi;

if (!class_exists('NN_Testimonial_Widget')):

    class NN_Testimonial_Widget {

        private $api = null;

        public function __construct($api_object) {

            $this->api = $api_object;

            add_shortcode('dyn-test-widget', [$this, 'get_html']);
            add_action('wp_enqueue_scripts', [$this, 'register_styles']);

        }

        public function register_styles() {
            wp_register_style('dyn-test-widget-styles', plugins_url('assets/css/testimonial.css', dirname(__FILE__)), array(), null);
        }

        public function get_html($shortcode_atts) {

            extract(shortcode_atts(
                array(
                    'stars' => '#faab5b',
                    'review_count' => 3,
                ),
                $shortcode_atts,
                'dyn-test-widget'
            ));

            $css_widget_vars = array(
                '--stars-color' => $stars,
            );

            $css_widget_string = '';

            foreach ($css_widget_vars as $key => $value) {
                if ($value) {
                    $css_widget_string .= $key . ':' . $value . ';';
                }
            }

            $nn_data = array();
            global $post;
            $html;

            if (class_exists('\lnb\core\NNApi')) {
                $nn_data = $this->api->get_data();
            } else {
                $html = "This widget requires the NN_API class";
            }

            if (!empty($nn_data)) {

                $fiveStarReviews = array_values(array_filter($nn_data['reviews'], [$this, 'findReviews']));

                wp_enqueue_style('dyn-test-widget-styles');

                ob_start();?>

				<div class="lnbTestimonialsWidget"<?php if ($css_widget_string) {?> style="<?php echo $css_widget_string; ?>"<?php }?>>
				<?php foreach ($fiveStarReviews as $index => $review) {
                    if ($index == $review_count) {break;}?>
						<div class="lnbTestimonialsWidget__review">
                            <span class="lnbTestimonialsWidget__name"><?php echo $review['name']; ?></span>
                            <?php echo file_get_contents(plugin_dir_path(dirname(__FILE__)) . '/assets/svg-stars.svg'); ?>
                            <span class="lnbTestimonialsWidget__description"><?php echo $review['description']; ?></span>
                            <span class="lnbTestimonialsWidget__author"><span class="lnbTestimonialsWidget__authorText"><?php echo $review['author']['name']; ?></span></span>
                            <span class="lnbTestimoniasWidget__meta"><span class="lnbTestimonialsWidget__metaLocation"><i class="far fa-map-pin"></i><?php echo $review['author']['address']['addressLocality']; ?></span></span>
						</div>
					<?php }?>
				</div>

				<?php $html = ob_get_clean();

            } else {
				var_dump($nn_data);
                $html = 'Error retrieving NearbyNow data';

            }

            return $html;

        }
        public function findReviews($review) {
            return ($review['reviewRating']['ratingValue'] == 5 && !empty($review['description']) && str_word_count($review['description']) > 20 && str_word_count($review['description']) < 60);
        }
    }

endif;
