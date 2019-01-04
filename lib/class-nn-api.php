<?php

if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('NN_API')):

    class NN_API {

        private static $transient = 'nn_data';

        public static function get_nn_data() {
            return self::get_data();
        }

        public static function get_data() {

            $data = self::get_cache();

            if (!$data) {
                $data = self::get_remote_data();
            }

            return $data;

        }

        private static function get_remote_data() {

            $nn_options = get_option('nearbynow_options');
            $apikey = $nn_options['text_string'];

            if (!$apikey) {
                return null;
            }

            $url = 'https://api.sidebox.com/plugin/nearbyserviceareareviewcombo/?storefronttoken=' . $apikey . '&reviewcount=50&reviewcityurl=cityurl&checkincount=0';
            $response = file_get_contents($url);
            $string = preg_match_all('/<span itemprop="(.*?)">(.*?)<\/span>/', $response, $matches);
            $string_cities = preg_match_all('/<a href="cityurl">(.*?)<\/a>/', $response, $matches_cities);
            $array_cities = array();

            foreach ($matches_cities[1] as $city) {
                $builder = explode(', ', $city);
                $array_cities[] = $builder[0];
            }

            $name_string = preg_match('/<meta itemprop="name" content="(.*?)"/', $response, $name_stringage);
            $itemtype_string = preg_match('/nn-review-inner-cont" itemscope itemtype="https:\/\/schema\.org\/(.*?)"/', $response, $itemtype_stringage);

            $data = array(
                'name' => $name_stringage[1],
                '@itemtype' => $itemtype_stringage[1],
                'rating' => $matches[2][0],
                'count' => $matches[2][1],
                'cities' => $array_cities,
            );

            $review_array_title = preg_match_all('/<h3 itemprop="name"[^\>]*>(.*?)<\/h3>/', $response, $review_array_title_matches);
            $review_array_title = $review_array_title_matches[1];

            $review_array_body = preg_match_all('/<p class="nn-review-body" itemprop="description">[^a-z,A-Z,0-9,$]*(.*)[^a-z,A-Z,0-9,$]*<\/p>/', $response, $review_array_body_matches);
            $review_array_body = $review_array_body_matches[1];

            $review_array_date = preg_match_all('/<time itemprop="datePublished" datetime="(.*)">/', $response, $review_array_date_matches);
            $review_array_date = $review_array_date_matches[1];

            $review_array_people = preg_match_all('/<span itemprop="name"[^\>]*>(.*?)<\/span>/', $response, $review_array_people_matches);
            $review_array_people = $review_array_people_matches[1];

            $review_array_city = preg_match_all('/<span itemprop="addressLocality"[^\>]*>(.*?)<\/span>/', $response, $review_array_city_matches);
            $review_array_city = $review_array_city_matches[1];

            $review_array_state = preg_match_all('/<span itemprop="addressRegion"[^\>]*>(.*?)<\/span>/', $response, $review_array_state_matches);
            $review_array_state = $review_array_state_matches[1];

            $review_array_rating = preg_match_all('/<span itemprop="ratingValue"[^\>]*>(.*?)<\/span>/', $response, $review_array_rating_matches);
            $review_array_rating = $review_array_rating_matches[1];

            $data['reviews'] = array();

            foreach ($review_array_title as $index => $title) {

                $data['reviews'][] = array(
                    'name' => $title,
                    'datePublished' => $review_array_date[$index],
                    'author' => array(
                        '@type' => 'Person',
                        'name' => $review_array_people[$index],
                        'address' => array(
                            '@type' => 'PostalAddress',
                            'addressLocality' => $review_array_city[$index],
                            'addressRegion' => $review_array_state[$index],
                        ),
                    ),
                    'reviewBody' => htmlspecialchars_decode(strip_tags($review_array_body[$index])),
                    'reviewRating' => array(
                        'ratingValue' => $review_array_rating[$index],
                        'bestRating' => '5',
                    ),
                );
            };

            self::cache($data);

            return $data;

        }

        private static function cache($data = null) {

            if (!$data) {
                $data = self::get_remote_data();
            }

            return set_transient(self::$transient, $data, 3 * 60 * 60);

        }

        private static function get_cache() {

            return get_transient(self::$transient);

        }

        private static function clear_cache() {

            return delete_transient(self::$transient);

        }

        public static function reset_cache() {

            if (self::clear_cache()) {
                return self::cache();
            }

            return false;
        }

    }

endif;
