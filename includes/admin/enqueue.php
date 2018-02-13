<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stm_listings_admin_enqueue($hook)
{
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    wp_enqueue_style('stm-listings-datetimepicker', STM_LISTINGS_URL . '/assets/css/jquery.stmdatetimepicker.css', null, null, 'all');
    wp_enqueue_script('stm-listings-datetimepicker', STM_LISTINGS_URL . '/assets/js/jquery.stmdatetimepicker.js', array('jquery'), null, true);

    wp_enqueue_media();

    if ($hook == 'listings_page_stm_xml_import_automanager') {
        wp_enqueue_style('stm_listings_materialize', STM_LISTINGS_URL . '/assets/css/materialize.min.css');
    }

    if (get_post_type() == 'product' or get_post_type() == 'listings' or $hook == 'listings_page_listing_categories' or $hook == 'listings_page_stm_csv_import' or $hook == 'listings_page_stm_xml_import' or $hook == 'listings_page_stm_xml_import_automanager') {

        wp_enqueue_script('stm-theme-multiselect', STM_LISTINGS_URL . '/assets/js/jquery.multi-select.js', array('jquery'));
        wp_enqueue_script('stm-listings-materialize', STM_LISTINGS_URL . '/assets/js/materialize.min.js');
        wp_enqueue_script('stm-listings-js', STM_LISTINGS_URL . '/assets/js/vehicles-listing.js', array('jquery','jquery-ui-droppable', 'jquery-ui-datepicker', 'jquery-ui-sortable'));

        wp_enqueue_style('stm_listings_listing_awesome_font', STM_LISTINGS_URL . '/assets/css/font-awesome.min.css');

        /*Google places*/
        $google_api_key = get_theme_mod('google_api_key', '');
        $google_api_map = 'https://maps.googleapis.com/maps/api/js?key=' . $google_api_key . '&libraries=places';

        wp_register_script('stm_gmap', $google_api_map, array('jquery'), null, true);

        //wp_enqueue_script('stm_gmap');
        wp_enqueue_script('stm-google-places', STM_LISTINGS_URL . '/assets/js/stm-google-places.js', 'stm_gmap', null, true);


    }
    wp_enqueue_style('stm_listings_listing_css', STM_LISTINGS_URL . '/assets/css/style.css');
}

add_action('admin_enqueue_scripts', 'stm_listings_admin_enqueue');