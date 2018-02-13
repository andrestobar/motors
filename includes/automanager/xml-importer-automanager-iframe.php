<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function getFieldsFromString($content)
{
    $matches = array();
    preg_match_all('/{(.*?)}/', $content, $matches);
    return $matches[1];
}

function stm_get_image_id($image_url) {
    global $wpdb;
    $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ));
    return $attachment[0];
}


if (!empty($_GET['stm_xml_do_import_automanager']) and $_GET['stm_xml_do_import_automanager']) {

    function stmImportXML()
    {

        $current_template = get_option('stm_current_template');
        $templates = get_option('stm_xml_templates');

        if (!empty($current_template) and !empty($templates)) {

            $parsed_post_fields = importFromTemplateName($current_template);

            StmDoXMLAutomanagerImport($parsed_post_fields);
        }

        exit;
    }


    add_action('admin_init', 'stmImportXML', 2);

}

// Function, that parse user choices and creates actual content to import, function only gets template name
function importFromTemplateName($template_name)
{
    $templates = get_option('stm_xml_templates');
    if (!empty($template_name)) {
        $xml = json_decode(json_encode(simplexml_load_file($templates[$template_name]['url'], 'SimpleXMLElement', LIBXML_NOCDATA)), TRUE);

        if (empty($xml['Vehicle'][0])) {
            $tmp_array = $xml['Vehicle'];
            $xml['Vehicle'] = array();
            $xml['Vehicle'][0] = $tmp_array;
        }

        $parsed_post_fields = array();
        $car_ids = array();

        foreach ($xml['Vehicle'] as $vehicle_key => $vehicle_info) {

            $car_ids[] = $vehicle_info['ID'];
            foreach ($templates[$template_name]['associations'] as $key => $value) {
                if (!empty($value)) {
                    $matches = getFieldsFromString($value);

                    $value = preg_replace_callback('{stm_tab_id}', 'stm_unique_id', $value);

                    foreach ($matches as $match) {

                        if ($match !== 'stm_tab_id') {

                            // Get all string values
                            if (!empty($xml['Vehicle'][$vehicle_key][$match])) {
                                if (gettype($xml['Vehicle'][$vehicle_key][$match]) == 'string') {
                                    $value = str_replace('{' . $match . '}', $xml['Vehicle'][$vehicle_key][$match], $value);
                                }
                            } else {
                                if ($key == 'stock_number') {
                                    $value = '';
                                }
                            }

                        }
                    }

                    if (empty($parsed_post_fields[$vehicle_key])) {
                        $parsed_post_fields[$vehicle_key] = array();
                    }

                    // Here we save all values, even shortcodes
                    if (!empty($value)) {
                        $parsed_post_fields[$vehicle_key][$key] = $value; //save all values
                    }

                    if (!empty($xml['Vehicle'][$vehicle_key]['ID'])) {
                        $parsed_post_fields[$vehicle_key]['id'] = $xml['Vehicle'][$vehicle_key]['ID'];
                    }

                    // Content
                    if ($key == 'content') {
                        // Features
                        $features_content = '';
                        if (!empty($xml['Vehicle'][$vehicle_key]['Features'])) {
                            $features_content = '<div class="stm_automanager_features_list clearfix">';

                            foreach ($xml['Vehicle'][$vehicle_key]['Features']['Category'] as $feature_category) {

                                if (!empty($feature_category['Feature'])) {
                                    $features_content .= '<div class="stm_automanager_single">';
                                    $features_content .= '<h3>' . str_replace('_', ' ', $feature_category['@attributes']['Name']) . '</h3>';
                                    $features_content .= '<ul class="list-style-2">';
                                    if (!empty($feature_category['Feature']) and gettype($feature_category['Feature']) == 'array') {
                                        foreach ($feature_category['Feature'] as $feature) {
                                            $features_content .= '<li>' . $feature . '</li>';
                                        }
                                    } else if (!empty($feature_category['Feature']) and gettype($feature_category['Feature']) == 'string') {
                                        $features_content .= '<li>' . $feature_category['Feature'] . '</li>';
                                    }
                                    $features_content .= '</ul>';
                                    $features_content .= '</div>';
                                }
                            }

                            $features_content .= '</div>';

                        }

                        $value = str_replace('{Features}', $features_content, $value);

                        $parsed_post_fields[$vehicle_key]['content'] = stripslashes($value);
                    }


                    // Create featured image
                    if ($key == 'featured_image') {
                        if (!empty($xml['Vehicle'][$vehicle_key]['PhotoURLs']['PhotoURL'][0])) {
                            if (gettype($xml['Vehicle'][$vehicle_key]['PhotoURLs']['PhotoURL']) == 'string') {
                                $parsed_post_fields[$vehicle_key]['featured_image'] = $xml['Vehicle'][$vehicle_key]['PhotoURLs']['PhotoURL'];
                            } else {
                                $parsed_post_fields[$vehicle_key]['featured_image'] = $xml['Vehicle'][$vehicle_key]['PhotoURLs']['PhotoURL'][0];
                            }
                        }
                    } // feat image

                    // Create gallery
                    if ($key == 'gallery') {
                        if (!empty($xml['Vehicle'][$vehicle_key]['PhotoURLs'])) {
                            if (!empty($xml['Vehicle'][$vehicle_key]['PhotoURLs']['PhotoURL'])) {

                                $parsed_post_fields[$vehicle_key]['gallery'] = array();

                                if (gettype($xml['Vehicle'][$vehicle_key]['PhotoURLs']['PhotoURL']) != 'string') {

                                    foreach ($xml['Vehicle'][$vehicle_key]['PhotoURLs']['PhotoURL'] as $photo_key => $photourl) {
                                        if ($photo_key != 0) {
                                            $parsed_post_fields[$vehicle_key]['gallery'][] = $photourl;
                                        }
                                    }
                                } else {
                                    unset($parsed_post_fields[$vehicle_key]['gallery']);
                                }

                            }
                        }
                    } // gallery
                }
            }
        }


        foreach ($parsed_post_fields as $current_vehicle_key => $parsed_post_field) {

            foreach ($parsed_post_field as $field_key => $field) {
                $parsed_post_fields[$current_vehicle_key][$field_key] = preg_replace('/{[^}]+}/', esc_html__('N/A', 'stm_vehicles_listing'), $field);
            }
        }

        update_option('stm_automanager_car_ids', $car_ids);

        return ($parsed_post_fields);
    }
}

function StmDoXMLAutomanagerImport($posts_info)
{
    ?>
    <style type="text/css">
        body {
            color: #fff;
            font-family: Trebuchet, sans-serif;
            line-height: 22px;
        }

        .pulse {
            margin: 20px auto 20px;
            display: block;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            box-shadow: 0 0 0 rgba(255, 255, 255, 0.4);
            animation: pulse 1s infinite;
        }

        @-webkit-keyframes pulse {
            0% {
                -webkit-box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
            }
            70% {
                -webkit-box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
            }
            100% {
                -webkit-box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        @keyframes pulse {
            0% {
                -moz-box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
            }
            70% {
                -moz-box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
                box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
            }
            100% {
                -moz-box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }
    </style>

    <?php


    $update_post = false;

    $total_query = count($posts_info);
    $current_queried = get_option('current_queried_xml');

    $car_ids = get_option('stm_automanager_car_ids');

    if (empty($current_queried)) {
        $current_queried = 0;
    }

    if ($current_queried < $total_query - 1) {
        echo '<div class="pulse"></div>';
    }

    //$current_queried = 0;

    $post_info = $posts_info[$current_queried];


    /* Strict fields */
    $post_to_insert = array(
        'post_title' => str_replace('N/A', '', $post_info['title']),
        'post_content' => $post_info['content'],
        'post_status' => $post_info['status'],
        'post_type' => 'listings',
    );

    /* Additional fields, included by theme */
    $additional_fields = array(
        'stock_number' => '',
        'vin' => '',
        'city_mpg' => '',
        'highway_mpg' => '',
        'regular_price_label' => '',
        'regular_price_description' => '',
        'special_price_label' => '',
        'instant_savings_label' => '',
    );

    foreach ($additional_fields as $key => $value) {

        if (!empty($post_info[$key])) {
            $additional_fields[$key] = $post_info[$key];
        }
    }

    $additional_fields = array_filter($additional_fields);

    // Filter fields
    $filter_taxes = stm_get_taxonomies();
    $filter_fields = array();

    if (!empty($filter_taxes)) {
        foreach ($filter_taxes as $key => $value) {
            if (!empty($post_info[$value])) {
                $filter_fields[$value] = $post_info[$value];
            }
        }
        if (!empty($post_info['sale_price'])) {
            $filter_fields['sale_price'] = $post_info['sale_price'];
        }
    }

    $args = array(
        'post_type' => 'listings',
        'post_status' => array('publish', 'draft'),
        'meta_query' => array(
            array(
                'key' => 'automanager_id',
                'value' => $post_info['id'],
                'compare' => '=',
            ),
        ),
    );
    $query = new WP_Query($args);

    if ($query->post_count == 1 and !empty($query->posts[0])) {
        $post_to_insert_id = $query->posts[0]->ID;
        $update_post = true;
    } else {
        // Insert post
        $post_to_insert_id = wp_insert_post($post_to_insert);
        $update_post = false;
    }

    if (!empty($post_to_insert_id)) {

        if ($update_post) {
            $update_post_args = array(
                'ID' => $post_to_insert_id,
                'post_title' => str_replace('N/A', '', $post_info['title']),
                'post_content' => $post_info['content'],
                'post_status' => $post_info['status'],
            );

            wp_update_post($update_post_args);
        }

        // Add vehicle ID
        if (!empty($post_info['id'])) {
            update_post_meta($post_to_insert_id, 'automanager_id', $post_info['id']);
        }

        update_post_meta($post_to_insert_id, 'title', 'hide');

        // Default theme fields
        if (!empty($additional_fields)) {
            foreach ($additional_fields as $additional_key => $additional_field) {
                if ($additional_key == 'vin') {
                    $additional_key = 'vin_number';
                    $history_link = 'http://clients.automanager.com/scripts/autocheckreport.aspx?VID=' . $additional_field;
                    update_post_meta($post_to_insert_id, 'history_link', $history_link);
                }
                update_post_meta($post_to_insert_id, $additional_key, $additional_field);
            }
        }

        // Insert filter fields in categories and in post meta
        if (!empty($filter_fields)) {
            foreach ($filter_fields as $filter_key => $filter_value) {
                if (!empty($filter_value)) {
                    if ($filter_key != 'sale_price') {
                        if ($filter_key == 'price') {
                            update_post_meta($post_to_insert_id, $filter_key, intval($filter_value));

                        } else {
                            $numeric = stm_get_taxonomies_with_type($filter_key);
                            if (!empty($numeric) and !empty($numeric['numeric']) and $numeric['numeric']) {
                                update_post_meta($post_to_insert_id, $filter_key, $filter_value);
                            } else {
                                $terms = wp_add_object_terms($post_to_insert_id, $filter_value, $filter_key);

                                if (!is_wp_error($terms)) {
                                    $current_term = get_term(reset($terms), $filter_key);

                                    update_post_meta($post_to_insert_id, $filter_key, $current_term->slug);

                                }
                            }
                        }
                    } else {
                        //If no price, but we have sale price, set sale price as main price
                        if (!empty($filter_fields['price']) and $filter_fields['price'] != 'N/A') {
                            update_post_meta($post_to_insert_id, $filter_key, intval($filter_value));
                        } else {
                            update_post_meta($post_to_insert_id, 'price', intval($filter_value));
                        }
                    }
                }
            }
        }

        // Featured image
        if (!empty($post_info['featured_image'])) {

            /*Download again*/
            $featured_exist = false;
            if ($update_post) {
                $current_featured_image_id = get_post_thumbnail_id($post_to_insert_id);
                if (!empty($current_featured_image_id)) {
                    if (md5_file(esc_url($post_info['featured_image'])) == md5_file(get_attached_file($current_featured_image_id))) {
                        $featured_exist = true;
                    }
                }
            }

            if (!$featured_exist) {
                $featured_image_id = media_sideload_image($post_info['featured_image'], intval($post_to_insert_id), $post_info['title'], 'src');
                if (gettype($featured_image_id) == 'string') {
                    set_post_thumbnail($post_to_insert_id, stm_get_image_id($featured_image_id));

                    echo '<div>';
                    esc_html_e('Featured image downloaded.', 'stm_vehicles_listing');
                    echo '</div>';
                }
            }
        }

        // Add gallery
        if (!empty($post_info['gallery']) and gettype($post_info['gallery']) == 'array') {
            $gallery_images = $post_info['gallery'];

            $gallery_keys = array();
            $exist_photos = array();

            /*Get uploaded images*/
            if ($update_post) {
                $current_gallery = get_post_meta($post_to_insert_id, 'gallery', true);

                if (!empty($current_gallery)) {
                    foreach ($current_gallery as $current_gallery_media_id) {
                        $post_thumbnail = md5_file(get_attached_file($current_gallery_media_id));
                        $exist_photos[$current_gallery_media_id] = $post_thumbnail;
                    }
                }
            }

            foreach ($gallery_images as $gallery_image) {
                $exist = false;

                if ($update_post) {
                    $image_url = md5_file(esc_url($gallery_image));

                    $key = array_search($image_url, $exist_photos);

                    if (!empty($key)) {
                        $gallery_keys[] = $key;
                        $exist = true;
                    }
                }


                if (!$exist) {
                    $featured_image_src = media_sideload_image($gallery_image, 0, $post_info['title'], 'src');
                    if (gettype($featured_image_src) == 'string') {
                        $gallery_keys[] = stm_get_image_id($featured_image_src);
                    }
                }
            }

            update_post_meta($post_to_insert_id, 'gallery', $gallery_keys);

            echo '<div>';
            esc_html_e('Gallery downloaded and added.', 'stm_vehicles_listing');
            echo '</div>';
        }

        echo '<div>';
        if ($update_post) {
            echo '<strong>' . (str_replace('N/A', '', $post_info['title'])) . '</strong> ';
            esc_html_e('updated.', 'stm_vehicles_listing');
        } else {
            echo '<strong>' . (str_replace('N/A', '', $post_info['title'])) . '</strong> ';
            esc_html_e('added.', 'stm_vehicles_listing');
        }
        echo '</div>';


        // Increase or stop import
        if ($current_queried < $total_query - 1) {
            $current_queried++;
            update_option('current_queried_xml', $current_queried);
            echo "<script>location.reload();</script>";
        } else {
            update_option('current_queried_xml', 0);
            echo '<div>';
            esc_html_e('Import done. Reload the page.', 'stm_vehicles_listing');
            echo '</div>';

            stm_place_draft_deleted();

            do_action('stm_cron_hook');
        }

    }


}

function stm_unique_id($matches)
{
    $uniq = uniqid() . '-' . rand(10000, 99999);
    return $uniq;
}

function stm_place_draft_deleted()
{
    $args = array(
        'post_type' => 'listings',
        'post_status' => array('publish', 'draft'),
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'automanager_id',
                'compare' => 'EXISTS',
            ),
        ),
    );

    $query = new WP_Query($args);
    $car_ids = get_option('stm_automanager_car_ids');
    $car_ids_db = array();


    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_id();
            $automanager_id = get_post_meta($post_id, 'automanager_id', true);
            $car_ids_db[$post_id] = $automanager_id;
        };
    };

    $draft_ids = array_diff($car_ids_db, $car_ids);

    if (!empty($draft_ids)) {
        foreach ($draft_ids as $draft_key => $draft_id) {
            $post = array(
                'ID' => $draft_key,
                'post_type' => 'listings',
                'post_status' => 'draft'
            );
            wp_update_post($post);
        }
    }
}