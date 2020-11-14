<?php

if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

use \lnb\core\NNApi;

if (!class_exists('NN_Static_Badge')):

    class NN_Static_Badge {

        private $api = null;

        public function __construct($api_object) {

            $this->api = $api_object;

            add_shortcode('static-nn-badge', [$this, 'get_html']);
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
					'url'=> '',
                ),
                $shortcode_atts,
                'static-nn-badge'
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

	<div class="lnbNNBadge lnbReviewsWidget--<?php echo $type; ?>"<?php if ($css_widget_string) {?> style="<?php echo $css_widget_string; ?>"<?php }?>>
					<?php if (!empty($url)) { ?>
				<a href="<?php echo $url; ?>">
			<?php } ?>	
		<div class="nn-badge-container">	
			<div class="badge-col-1">
				<img src="<?php echo plugin_dir_url(dirname(__FILE__)); ?>/assets/nn-icon-128x128.png" />
			</div>
			<div class="badge-col-3">

                <?php if ($name !== "false"){ ?>
                <div class="nn-badge-title">
                    <?php echo $nn_data['name']; ?>
                </div>
                <?php } else { ?>
				<div class="nn-badge-title">
					Nearby Now Reviews
				</div>
                <?php } ?>
				<div class="rating-container">
				<?php if ($nn_data['aggregateRating']['reviewCount'] > 0 && $reviewdata !== "false"): ?>		
					<span class="lnbReviewsWidget__data"><?php echo $nn_data['aggregateRating']['ratingValue']; ?></span>
				<?php endif;?>
                <?php echo file_get_contents(plugin_dir_path(dirname(__FILE__)) . '/assets/svg-stars.svg'); ?>
				</div>	
                <div class="nn-badge-subtitle">
					Based on <?php echo $nn_data['aggregateRating']['reviewCount']; ?> reviews
				</div>
			</div>
		</div>
		<?php if (!empty($url)) { ?>
				</a>
			<?php } ?>	
	</div>

				<?php $html = ob_get_clean();

        } else {

            $html = 'Error retrieving NearbyNow data';

        }

        return $html;

    }

}

endif;