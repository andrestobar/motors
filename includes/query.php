<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_filter('request', 'stm_listings_query_vars');

function stm_listings_query_vars($query_vars)
{
    $include_search = stm_listings_search_inventory();

    $is_listing = isset($query_vars['post_type'])
        && in_array(stm_listings_post_type(), (array)$query_vars['post_type']);

    /*Include search*/
    if($include_search and !empty($_GET['s'])) {
        $is_listing = true;
    }

    if (isset($query_vars['pagename'])) {
        $listing_id = stm_listings_user_defined_filter_page();
        if ($listing_id) {
            $requested = get_page_by_path($query_vars['pagename']);
            if (!empty($requested) and $is_listing = $listing_id == $requested->ID) {
                unset($query_vars['pagename']);
            }
        }
    }

    if (!empty($_GET['ajax_action']) and $_GET['ajax_action'] == 'listings-result') {
        unset($query_vars['pagename']);
        unset($query_vars['page_id']);
        $is_listing = true;
    }

    if ($is_listing and !is_admin()) {
        $query_vars = apply_filters('stm_listings_query_vars', _stm_listings_build_query_args($query_vars));
    }

    return $query_vars;
}

add_action('template_redirect', 'stm_listings_template_redirect', 0);

function stm_listings_template_redirect()
{
    if (is_feed()) {
        return;
    }

    if ($listing_id = stm_listings_user_defined_filter_page()) {
        if ((is_post_type_archive('listings'))) {
            $GLOBALS['listings_query'] = $GLOBALS['wp_the_query'];
            $query = new WP_Query('pagename=' . get_page_uri($listing_id));
            $GLOBALS['wp_query'] = $query;
            $GLOBALS['wp_the_query'] = $query;
            $GLOBALS['wp']->register_globals();
        }
    }
}

/**
 * Get current listings query
 *
 * @return WP_Query
 */
function stm_listings_query()
{
    $newQuery = "";
    if(isset($GLOBALS['listings_query'])) {
        $newQuery = $GLOBALS['listings_query'];
    } else {
       $newQuery = new WP_Query(_stm_listings_build_query_args(array(
           'paged' => stm_listings_paged_var()
       )));
       $GLOBALS['listings_query'] = $newQuery;
    }

    /*return isset($GLOBALS['listings_query']) ? $GLOBALS['listings_query'] : new WP_Query(_stm_listings_build_query_args(array(
        'paged' => stm_listings_paged_var()
    )));*/
    return $newQuery;
}


add_filter('posts_clauses_request', 'stm_listings_posts_clauses', 100, 2);

function stm_listings_posts_clauses($clauses, WP_Query $query)
{
    if (!$query->get('filter_location') || !stm_listings_input('stm_lat') || !stm_listings_input('stm_lng')) {
        return $clauses;
    }

    $formula = '6378.137 * ACOS(COS(RADIANS(stm_lat_prefix.meta_value)) * COS(RADIANS(:lat)) * COS(RADIANS(stm_lng_prefix.meta_value) - RADIANS(:lng)) + SIN(RADIANS(stm_lat_prefix.meta_value)) * SIN(RADIANS(:lat)))';
    $formula = strtr($formula, array(
        ':lat' => floatval(stm_listings_input('stm_lat')),
        ':lng' => floatval(stm_listings_input('stm_lng')),
    ));

    $clauses['fields'] .= ", ($formula) AS stm_distance";

    global $wpdb;
    $table_prefix = $wpdb->prefix;

    $clauses['join'] .= " INNER JOIN {$table_prefix}postmeta stm_lat_prefix ON ({$table_prefix}posts.ID = stm_lat_prefix.post_id AND stm_lat_prefix.meta_key = 'stm_lat_car_admin')";
    $clauses['join'] .= " INNER JOIN {$table_prefix}postmeta stm_lng_prefix ON ({$table_prefix}posts.ID = stm_lng_prefix.post_id AND stm_lng_prefix.meta_key = 'stm_lng_car_admin') ";
    $clauses['orderby'] = 'stm_distance ASC, ' . $clauses['orderby'];

    return apply_filters('stm_listings_clauses_filter', $clauses);
}

function _stm_listings_build_query_args($args, $source = null)
{

    if (is_null($source)) {
        $source = $_GET;
    }

    $args['post_type'] = stm_listings_post_type();

    foreach (stm_listings_attributes(array('key_by' => 'slug')) as $attribute => $filter_option) {

        if ($filter_option['numeric']) {
            // Compatibility for min_
            if (!empty($source['min_' . $attribute])) {
                $source[$attribute]['min'] = $source['min_' . $attribute];
            }

            // Compatibility for max_
            if (!empty($source['max_' . $attribute])) {
                $source[$attribute]['max'] = $source['max_' . $attribute];
            }
        }

        if (empty($source[$attribute])) {
            continue;
        }

        $_value = $source[$attribute];

        if (!is_array($_value) && $filter_option['numeric']) {
            if (strpos(trim($_value, '-'), '-') !== false) {
                $_value = explode('-', $_value);
                $_value = array(
                    'min' => $_value[0],
                    'max' => $_value[1],
                );
            } elseif (strpos($_value, '>') === 0) {
                $_value = array(
                    'min' => str_replace('>', '', $_value),
                );
            } elseif (strpos($_value, '<') === 0) {
                $_value = array(
                    'max' => str_replace('<', '', $_value),
                );
            }
        }

        if (!is_array($_value)) {
            // Exact value
            $args['tax_query'][] = array(
                'taxonomy' => $attribute,
                'field' => 'slug',
                'terms' => (array)$_value,
            );
            continue;
        }

        if (!empty($_value['min']) || !empty($_value['max'])) {

            if ($attribute == 'price') {
                $args['meta_query'][] = array(
                    array(
                        'key' => 'stm_genuine_price',
                        'value' => array_merge(array('min' => 0, 'max' => 9999999999), $_value),
                        'type' => 'DECIMAL',
                        'compare' => 'BETWEEN',
                    ),
                );

                continue;
            }

            // Range condition
            $args['meta_query'][] = array(
                'key' => $attribute,
                'value' => array_merge(array('min' => 0, 'max' => 9999999999), $_value),
                'type' => 'DECIMAL',
                'compare' => 'BETWEEN',
            );
        } else {
            // Multiple values
            $args['tax_query'][] = array(
                'taxonomy' => $attribute,
                'terms' => $_value,
                'field' => 'slug',
            );
        }
    }

    if (!empty($source['popular']) && $source['popular'] == 'true') {
        $args['order'] = 'DESC';
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = 'stm_car_views';
    }

    if (!empty($source['sort_order'])) {
        switch ($source['sort_order']) {
            case "price_low":
                $args['order'] = 'ASC';
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'stm_genuine_price';
                break;
            case "price_high":
                $args['order'] = 'DESC';
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'stm_genuine_price';
                break;
            case "date_low":
                $args['order'] = 'DESC';
                $args['orderby'] = 'date';
                break;
            case "mileage_low":
                $args['order'] = 'ASC';
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'mileage';
                break;
            case "mileage_high":
                $args['order'] = 'DESC';
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'mileage';
                break;
            default:
                $args['order'] = 'ASC';
                $args['orderby'] = 'date';
        }
    }

    if (!empty($source['posts_per_page'])) {
        $args['posts_per_page'] = $source['posts_per_page'];
    }

    // Enables adding location conditions
    $args['filter_location'] = true;

    return apply_filters('stm_listings_build_query_args', $args);
}