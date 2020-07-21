<?php

if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

use \lnb\core\NNApi;

if (!class_exists('NN_Static_Widget')):

    class NN_Static_Widget {

        private $api = null;

        public function __construct($api_object) {

            $this->api = $api_object;

            add_shortcode('static-nn-widget', [$this, 'get_html']);
            add_action('wp_enqueue_scripts', [$this, 'register_styles']);

        }

        public function register_styles() {
            wp_register_style('lnb-reviews-widget-styles', plugins_url('assets/css/style.css', dirname(__FILE__)), array(), null);
        }

        public function get_html($shortcode_atts) {

            extract(shortcode_atts(
                array(
                    'name' => 'true',
                    'reviewdata' => 'true',	
                    'ratingtext' => 'Rated {rating} out of {review-count} reviews',
                    'type' => 'block',
                    'size' => 'medium',
                    'accent' => '#000',
                    'stars' => '#fee300',
                ),
                $shortcode_atts,
                'static-nn-widget'
            ));

            $type = !empty($type) ? $type : 'block';

            $css_widget_vars = array(
                '--accent-color' => $accent,
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

                $html = "This widget requires the NNApi class found in \lnb\core";
            }

            if (!empty($nn_data) && !is_wp_error($nn_data)) {

                wp_enqueue_style('lnb-reviews-widget-styles');

                $rating_string = str_replace(
                    array(
                        '{rating}',
                        '{review-count}'
                    ),
                    array(
                        $nn_data['aggregateRating']['ratingValue'],
                        $nn_data['aggregateRating']['reviewCount']
                    ),
                    $ratingtext
                );

                ob_start();?>

					<div class="lnbReviewsWidget lnbReviewsWidget--<?php echo $type; ?>"<?php if ($css_widget_string) {?> style="<?php echo $css_widget_string; ?>"<?php }?>>
	                    <?php if ($name !== "false"): ?>
	                    <h3 class="lnbReviewsWidget__title"><?php echo $nn_data['name']; ?></h3>
	                    <?php endif;?>
                    <?php echo file_get_contents(plugin_dir_path(dirname(__FILE__)) . '/assets/svg-stars.svg'); ?>
				<?php if ($nn_data['aggregateRating']['reviewCount'] > 0 && $reviewdata !== "false"): ?>		
					<span class="lnbReviewsWidget__data"><?php echo $rating_string; ?></span>
				<?php endif;?>
                                       </div>

				<?php $html = ob_get_clean();

        } else {

            $html = 'Error retrieving NearbyNow data';

        }

        return $html;

    }

}

endif;
