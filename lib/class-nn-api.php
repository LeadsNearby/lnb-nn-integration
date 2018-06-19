<?php

namespace lnb\core;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit('Direct script access denied.');
}

require_once plugin_dir_path(__FILE__) . '/dom-parser/HtmlDomParser.php';
use \Sunra\PhpSimple\HtmlDomParser;

class NNApi {

    private $devMode = true;
    private $api_key = '';
    private $transient_key = '';

    public function __construct($api_key) {
        $this->api_key = $api_key;
        $this->transient_key = hash('sha256', $api_key);
    }

    public function clear_cache() {
        return delete_transient('nearby_now_data_' . $this->transient_key);
    }

    private function get_local_data() {
        return get_transient('nearby_now_data_' . $this->transient_key);
    }

    private function get_remote_data() {
        $response = wp_remote_get('https://api.sidebox.com/plugin/nearbyserviceareareviewcombo/?storefronttoken=' . $this->api_key . '&reviewcount=50&reviewcityurl=cityurl&checkincount=0');
        if (is_wp_error($response)) {
            return $response;
        }
        $response_body = wp_remote_retrieve_body($response);
        $data = $this->parse_remote_data($response_body);
        set_transient('nearby_now_data_' . $this->transient_key, $data, 60 * 60 * 6);
        return $data;
    }

    private function get_remote_city_data($city, $state) {
        $response = wp_remote_get('https://api.sidebox.com/plugin/nearbyserviceareareviewcombo/?storefronttoken=' . $this->api_key . '&reviewcount=25&checkincount=25&city=' . trim($city) . '&state=' . trim($state));
        if (is_wp_error($response)) {
            return $response;
        }
        $response_body = wp_remote_retrieve_body($response);
        $data = $this->parse_remote_city_data($response_body);
        return $data;
    }

    private function parse_remote_data($raw_data) {

        $dom = HtmlDomParser::str_get_html($raw_data);

        $company_name = $dom->find('[itemprop="name"]', 0)->content;
        $rating_value = $dom->find('[itemprop="ratingValue"]', 0)->plaintext;
        $review_count = $dom->find('[itemprop="reviewCount"]', 0)->plaintext;

        $raw_locations = $dom->find('.nn-samap-topcity > a');
        $locations = array();
        foreach ($raw_locations as $raw_location) {
            $raw_location_array = explode(',', $raw_location->plaintext);
            $city = $raw_location_array[0];
            $state = $raw_location_array[1];
            $locations[] = array(
                'slug' => sanitize_title($city),
                'city' => trim($city),
                'state' => trim($state),
            );
        }

        $raw_reviews = $dom->find('[itemprop="review"]');
        $reviews = array();

        foreach ($raw_reviews as $raw_review) {
            $name = $raw_review->find('[itemprop="name"]', 0)->plaintext;
            $date_published = $raw_review->find('[itemprop="datePublished"]', 0)->datetime;
            $description = $raw_review->find('[itemprop="description"]', 0)->plaintext;
            $author = $raw_review->find('[itemprop="author"]', 0)->find('[itemprop="name"]', 0)->plaintext;
            $city = $raw_review->find('[itemprop="author"]', 0)->find('[itemprop="addressLocality"]', 0)->plaintext;
            $state = $raw_review->find('[itemprop="author"]', 0)->find('[itemprop="addressRegion"]', 0)->plaintext;
            $rating_value = $raw_review->find('[itemprop="ratingValue"]', 0)->plaintext;
            $reviews[] = array(
                '@type' => 'Review',
                'name' => html_entity_decode(trim($name)),
                'datePublished' => $date_published,
                'description' => html_entity_decode(trim($description)),
                'author' => array(
                    '@type' => 'Author',
                    'name' => trim($author),
                    'address' => array(
                        '@type' => 'PostalAddress',
                        'addressLocality' => trim($city),
                        'addressRegion' => trim($state),
                    ),
                ),
                'reviewRating' => array(
                    '@type' => 'Rating',
                    'ratingValue' => (float) trim($rating_value),
                ),
            );
        }

        $data = array(
            'name' => $company_name,
            'cities' => $locations,
            'aggregateRating' => array(
                '@type' => 'AggregateRating',
                'ratingValue' => (float) trim($rating_value),
                'reviewCount' => (int) trim($review_count),
            ),
            'reviews' => $reviews,
        );

        return $data;
    }

    private function parse_remote_city_data($raw_data) {

        $dom = HtmlDomParser::str_get_html($raw_data);

        $raw_reviews = $dom->find('[itemprop="review"]');
        $reviews = array();

        foreach ($raw_reviews as $raw_review) {
            $name = $raw_review->find('[itemprop="name"]', 0)->plaintext;
            $date_published = $raw_review->find('[itemprop="datePublished"]', 0)->datetime;
            $description = $raw_review->find('[itemprop="description"]', 0)->plaintext;
            $author = $raw_review->find('[itemprop="author"]', 0)->find('[itemprop="name"]', 0)->plaintext;
            $city = $raw_review->find('[itemprop="author"]', 0)->find('[itemprop="addressLocality"]', 0)->plaintext;
            $state = $raw_review->find('[itemprop="author"]', 0)->find('[itemprop="addressRegion"]', 0)->plaintext;
            $rating_value = $raw_review->find('[itemprop="ratingValue"]', 0)->plaintext;
            $reviews[] = array(
                '@type' => 'Review',
                'name' => html_entity_decode(trim($name)),
                'datePublished' => $date_published,
                'description' => html_entity_decode(trim($description)),
                'author' => array(
                    '@type' => 'Author',
                    'name' => trim($author),
                    'address' => array(
                        '@type' => 'PostalAddress',
                        'addressLocality' => trim($city),
                        'addressRegion' => trim($state),
                    ),
                ),
                'reviewRating' => array(
                    '@type' => 'Rating',
                    'ratingValue' => trim($rating_value),
                ),
            );
        }

        $raw_checkins = $dom->find('[itemtype="http://schema.org/UserCheckins"]');
        $checkins = array();

        foreach ($raw_checkins as $raw_checkin) {
            $name = $raw_checkin->find('[itemprop="name"]', 0)->content;
            $start_date = $raw_checkin->find('[itemprop="startDate"]', 0)->datetime;
            $attendees = $raw_checkin->find('[itemprop="attendees"]', 0)->plaintext;
            $description = $raw_checkin->find('[itemprop="description"]', 0)->plaintext;
            $raw_address_array = $raw_checkin->find('[itemprop="address"]', 0)->find('span');
            $street_address = $raw_address_array[0]->plaintext;
            $city = $raw_address_array[1]->plaintext;
            $state = $raw_address_array[2]->plaintext;
            $postal_code = $raw_address_array[3]->plaintext;
            $latitude = $raw_checkin->find('[itemprop="latitude"]', 0)->content;
            $longitude = $raw_checkin->find('[itemprop="longitude"]', 0)->content;
            $image = $raw_checkin->find('[itemprop="image"]', 0)->src;
            $checkins[] = array(
                'name' => html_entity_decode(trim($name)),
                'startDate' => trim($start_date),
                'attendees' => trim($attendees),
                'description' => html_entity_decode(trim($description)),
                'location' => array(
                    '@type' => 'Place',
                    'address' => array(
                        '@type' => 'PostalAddresss',
                        'streetAddress' => trim($street_address),
                        'addressLocality' => trim($city),
                        'addressRegion' => trim($state),
                        'postalCode' => trim($postal_code),
                    ),
                    'geo' => array(
                        '@type' => 'GeoCoordinates',
                        'latitude' => trim($latitude),
                        'longitude' => trim($longitude),
                    ),
                ),
                'image' => $image,
            );
        }

        $data = array(
            'reviews' => $reviews,
            'checkins' => $checkins,
        );

        return $data;

    }

    public function get_data() {
        if (!$this->devMode) {
            $local_data = $this->get_local_data();
            if ($local_data) {
                return $local_data;
            }
        }

        $remote_data = $this->get_remote_data();
        return $remote_data;
    }

    public function get_city_data($city) {
        $data = $this->get_data();

        $city_match = '';
        foreach ($data['cities'] as $city_obj) {
            if ($city_obj['slug'] == $city) {
                $city_match = $city_obj;
            }
        }

        if (!$city_match) {
            return false;
        }
        $api_response = $this->get_remote_city_data($city_match['city'], $city_match['state']);
        $response['slug'] = $city_match['slug'];
        $response['city'] = $city_match['city'];
        $response['state'] = $city_match['state'];
        $response['reviews'] = $api_response['reviews'];
        $response['checkins'] = $api_response['checkins'];
        return $response;
    }

    public function get_cities() {
        $data = $this->get_data();
        return $data['cities'];
    }
}
