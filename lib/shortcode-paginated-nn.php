<?php

function paginated_serviceareareviewcombo_html($atts) {
    $shortcode_atts = shortcode_atts(array(
        'state' => '',
        'city' => '',
        'showmap' => 'yes',
        'reviewstart' => 0,
        'reviewcount' => 25,
        'checkinstart' => 0,
        'checkincount' => 25,
        'zoomlevel' => 11,
        'reviewcityurl' => '',
        'mapsize' => 'medium',
        'mapscrollwheel' => 'no',
        'fblike' => 'no',
        'fbcomment' => 'no',
        'showphotos' => 'yes',
        'showminimap' => 'no',
    ), $atts, 'paginated_serviceareareviewcombo');

    $page = get_query_var('page', 0);
    $shortcode_atts['reviewstart'] = $page * $shortcode_atts['reviewcount'] + 1;
    $shortcode_atts['checkinstart'] = $page * $shortcode_atts['checkincount'] + 1;

    $att_string = '';
    foreach ($shortcode_atts as $key => $att) {
        $att_string .= $key . '="' . $att . '" ';
    }

    var_dump($att_string);

    $nn_string = do_shortcode('[serviceareareviewcombo ' . $att_string . ']');
    preg_match('/based on <span[\s]+?>([0-9]+)<\/span>/', $nn_string, $_number_of_reviews);
    $number_of_reviews = isset($_number_of_reviews[1]) ? $_number_of_reviews[1] : 0;
    if ($number_of_reviews / ($page * $shortcode_atts['reviewcount']) > 1) {
        return $nn_string . '<a style="display: inline-block; margin-top: 2em" href="' . user_trailingslashit(get_the_permalink() . ($page <= 1 ? 2 : $page + 1)) . '">More Reviews >></a>';
    }
    return $nn_string;
}

add_shortcode('paginated_serviceareareviewcombo', 'paginated_serviceareareviewcombo_html');
