<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/*Add taxonomies from listing categories*/
add_action('init', 'create_stm_listing_category', 0);

function create_stm_listing_category()
{
    $options = get_option('stm_vehicle_listing_options');
    if (!empty($options)) {
        foreach ($options as $option) {

            $show_admin_table = false;
            if (!empty($option['show_in_admin_column']) and $option['show_in_admin_column']) {
                $show_admin_table = true;
            }

            if (empty($option['numeric'])) {
                $numeric = true;
            } else {
                $numeric = false;
                $show_admin_table = false;
            }

            register_taxonomy(
                $option['slug'],
                'listings',
                array(
                    'labels' => array(
                        'name' => $option['plural_name'],
                        'singular_name' => $option['single_name'],
                        'search_items' => __('Search ' . $option['plural_name']),
                        'popular_items' => __('Popular ' . $option['plural_name']),
                        'all_items' => __('All ' . $option['plural_name']),
                        'parent_item' => null,
                        'parent_item_colon' => null,
                        'edit_item' => __('Edit ' . $option['single_name']),
                        'update_item' => __('Update ' . $option['single_name']),
                        'add_new_item' => __('Add New ' . $option['single_name']),
                        'new_item_name' => __('New ' . $option['single_name'] . ' Name'),
                        'separate_items_with_commas' => __('Separate ' . $option['plural_name'] . ' with commas'),
                        'add_or_remove_items' => __('Add or remove ' . $option['plural_name']),
                        'choose_from_most_used' => __('Choose from the most used ' . $option['plural_name']),
                        'not_found' => __('No ' . $option['plural_name'] . ' found.'),
                        'menu_name' => __($option['plural_name']),
                    ),
                    'public' => true,
                    'hierarchical' => $numeric,
                    'show_ui' => true,
                    'show_in_menu' => false,
                    'show_admin_column' => $show_admin_table,
                    'show_in_nav_menus' => false,
                    'show_in_quick_edit' => false,
                    'query_var' => false,
                    'rewrite' => false,
                )
            );
        };
    }

    /*Register additional features hidden taxonomy*/
    register_taxonomy(
        'stm_additional_features',
        'listings',
        array(
            'labels' => array(
                'name' => esc_html__('Additional features', 'stm_vehicles_listing'),
                'singular_name' => esc_html__('Additional features', 'stm_vehicles_listing'),
                'search_items' => esc_html__('Search Additional features', 'stm_vehicles_listing'),
                'popular_items' => esc_html__('Popular Additional features', 'stm_vehicles_listing'),
                'all_items' => esc_html__('All  Additional features', 'stm_vehicles_listing'),
                'parent_item' => null,
                'parent_item_colon' => null,
                'edit_item' => esc_html__('Edit Additional features', 'stm_vehicles_listing'),
                'update_item' => esc_html__('Update Additional features', 'stm_vehicles_listing'),
                'add_new_item' => esc_html__('Add New Additional features', 'stm_vehicles_listing'),
                'new_item_name' => esc_html__('New Additional features', 'stm_vehicles_listing' . ' Name'),
                'separate_items_with_commas' => esc_html__('Separate Additional features', 'stm_vehicles_listing'),
                'add_or_remove_items' => esc_html__('Add or remove Additional features', 'stm_vehicles_listing'),
                'choose_from_most_used' => esc_html__('Choose from the most used  Additional features', 'stm_vehicles_listing'),
                'not_found' => esc_html__('No Additional features found', 'stm_vehicles_listing'),
                'menu_name' => esc_html__('Additional features', 'stm_vehicles_listing'),
            ),
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_admin_column' => false,
            'show_in_nav_menus' => false,
            'show_in_quick_edit' => false,
            'query_var' => false,
            'rewrite' => false,
        )
    );
}

function stm_get_cat_icons($font_pack = 'fa')
{
    $plugin_path = STM_LISTINGS_PATH;

    $fonts = array();

    if ($font_pack == 'fa') {
        $font_icons = file($plugin_path . '/assets/font-awesome.json');
        $font_icons = json_decode(implode('', $font_icons), true);
        foreach ($font_icons as $key => $val) {
            $fonts[] = $key;
        };
    } elseif ($font_pack == 'type_1') {
        $fonts_pack = get_option('stm_fonts');
        if (!empty($fonts_pack)) {
            foreach ($fonts_pack as $font => $info) {
                $icon_set = array();
                $icons = array();
                $upload_dir = wp_upload_dir();
                $path = trailingslashit($upload_dir['basedir']);
                $file = $path . $info['include'] . '/' . $info['config'];
                $output = '';
                include($file);
                if (!empty($icons)) {
                    $icon_set = array_merge($icon_set, $icons);
                }
                if (!empty($icon_set)) {
                    foreach ($icon_set as $icons) {
                        foreach ($icons as $icon) {
                            $fonts[] = $font . '-' . $icon['class'];
                        }
                    }
                }
            }
        }

    } elseif ($font_pack == 'service_icons') {
        $fonts_custom_type_2 = json_decode(file_get_contents($plugin_path . '/assets/service_icons.json'), true);

        foreach ($fonts_custom_type_2['icons'] as $icon) {
            $fonts[] = 'stm-service-icon-' . $icon['properties']['name'];
        }

    } elseif ($font_pack == 'boat_icons') {
        $fonts_custom_type_2 = json_decode(file_get_contents($plugin_path . '/assets/boat_icons.json'), true);

        foreach ($fonts_custom_type_2['icons'] as $icon) {
            $fonts[] = 'stm-boats-icon-' . $icon['properties']['name'];
        }

    } elseif ($font_pack == 'moto_icons') {
        $fonts_custom_type_2 = json_decode(file_get_contents($plugin_path . '/assets/moto_icons.json'), true);

        foreach ($fonts_custom_type_2['icons'] as $icon) {
            $fonts[] = 'stm-moto-icon-' . $icon['properties']['name'];
        }

    } elseif ($font_pack == 'rental_icons') {
        $fonts_custom_type_2 = json_decode(file_get_contents($plugin_path . '/assets/rental_icons.json'), true);

        foreach ($fonts_custom_type_2['icons'] as $icon) {
            $fonts[] = 'stm-rental-' . $icon['properties']['name'];
        }

    }

    return $fonts;
}

function stm_get_car_listings()
{
    return stm_listings_attributes(array('where' => array('use_on_car_listing_page' => true)));
}

function stm_get_car_archive_listings()
{
    return stm_listings_attributes(array('where' => array('use_on_car_archive_listing_page' => true)));
}

function stm_get_single_car_listings()
{
    return stm_listings_attributes(array('where' => array('use_on_single_car_page' => true)));
}

function stm_get_map_listings()
{
    return stm_listings_attributes(array('where' => array('use_on_map_page' => true)));
}

function stm_get_car_filter()
{
    return stm_listings_attributes(array('where' => array('use_on_car_filter' => true)));
}

function stm_get_car_modern_filter()
{
    return stm_listings_attributes(array('where' => array('use_on_car_modern_filter' => true)));
}

function stm_get_car_modern_filter_view_images()
{
    return stm_listings_attributes(array('where' => array('use_on_car_modern_filter_view_images' => true)));
}

function stm_get_car_parent_exist()
{

    $car_listing = array();
    $options = get_option('stm_vehicle_listing_options');
    if (!empty($options)) {
        foreach ($options as $key => $option) {
            if (!empty($options[$key]['listing_taxonomy_parent'])) {
                $car_listing[] = $option;
            }
        }
    }

    return $car_listing;
}

function stm_get_car_filter_links()
{
    return stm_listings_attributes(array('where' => array('use_on_car_filter_links' => true)));
}

function stm_get_footer_taxonomies()
{
    return stm_listings_attributes(array('where' => array('use_in_footer_search' => true)));
}

function stm_get_car_filter_checkboxes()
{

    $car_listing = array();
    $options = get_option('stm_vehicle_listing_options');
    if (!empty($options)) {
        foreach ($options as $key => $option) {
            if (!empty($options[$key]['listing_rows_numbers'])) {
                $car_listing[] = $option;
            }
        }
    }

    return $car_listing;
}

function stm_get_filter_title()
{
    return stm_listings_attributes(array('where' => array('use_on_directory_filter_title' => true)));
}

function stm_get_taxonomies()
{
    //Get all filter options from STM listing plugin - Listing - listing categories
    $filter_options = get_option('stm_vehicle_listing_options');

    $taxonomies = array();

    if (!empty($filter_options)) {
        $i = 0;
        foreach ($filter_options as $filter_option) {

            $taxonomies[$filter_option['single_name']] = $filter_option['slug'];

        }

    }

    return $taxonomies;

}

function stm_get_taxonomies_with_type($taxonomy_slug)
{
    if (!empty($taxonomy_slug)) {
        //Get all filter options from STM listing plugin - Listing - listing categories
        $filter_options = get_option('stm_vehicle_listing_options');

        $taxonomies = array();

        if (!empty($filter_options)) {
            foreach ($filter_options as $filter_option) {
                if ($filter_option['slug'] == $taxonomy_slug) {
                    $taxonomies = $filter_option;
                }
            }

        }

        return $taxonomies;
    }

}

function stm_get_taxonomies_as_div()
{
    //Get all filter options from STM listing plugin - Listing - listing categories
    $filter_options = get_option('stm_vehicle_listing_options');

    $taxonomies = array();

    if (!empty($filter_options)) {
        foreach ($filter_options as $filter_option) {
            if (!$filter_option['numeric']) {
                $taxonomies[$filter_option['single_name']] = $filter_option['slug'] . 'div';
            } else {
                $taxonomies[$filter_option['single_name']] = 'tagsdiv-' . $filter_option['slug'];
            }

        }

    }

    return $taxonomies;

}

function stm_get_categories()
{
    //Get all filter options from STM listing plugin - Listing - listing categories
    $filter_options = get_option('stm_vehicle_listing_options');
    //Creating new array for tax query && meta query
    $categories = array();

    $terms_args = array(
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false,
        'fields' => 'all',
        'pad_counts' => false,
    );

    if (!empty($filter_options)) {
        foreach ($filter_options as $filter_option) {
            if (empty($filter_option['numeric'])) {

                $terms = get_terms($filter_option['slug'], $terms_args);

                foreach ($terms as $term) {
                    $categories[$term->slug] = $term->slug . ' | ' . $filter_option['slug'];
                }
            }
        }
    }

    return $categories;
}

function stm_get_name_by_slug($slug = '')
{
    //Get all filter options from STM listing plugin - Listing - listing categories
    $filter_options = get_option('stm_vehicle_listing_options');
    $name = '';
    if (!empty($filter_options)) {
        if (!empty($slug)) {
            foreach ($filter_options as $filter_option) {
                if ($filter_option['slug'] == $slug) {
                    $name = $filter_option['single_name'];
                }
            }
        }
    }

    return $name;
}

function stm_get_all_by_slug($slug = '')
{
    return stm_listings_attribute($slug);
}

function stm_get_custom_taxonomy_count($slug, $taxonomy)
{
    // this is cached function, so we can use it

    $total = 0;

    $_terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => true
    ));

    if (!is_wp_error($_terms)) {
        foreach ($_terms as $_term) {
            if ($_term->slug == $slug) {
                $total = $_term->count;
            }
        }
    }

    if (empty($slug) and empty($taxonomy)) {
        $total = wp_count_posts(stm_listings_post_type());
        $total = $total->publish;
    }

    return $total;
}

function stm_get_category_by_slug($slug)
{
    if (!empty($slug)) {
        $terms_args = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => true,
            'fields' => 'all',
            'pad_counts' => true,
        );
        $terms = get_terms($slug, $terms_args);

        return $terms;
    }
}

function stm_get_category_by_slug_all($slug)
{
    if (!empty($slug)) {
        $terms_args = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => apply_filters('stm_get_empty_terms', false),
            'fields' => 'all',
            'pad_counts' => apply_filters('stm_get_term_pad_counts', true),
        );
        $terms = get_terms($slug, $terms_args);

        return $terms;
    }
}

function stm_remove_meta_boxes()
{
    $taxonomies_style = stm_get_taxonomies_as_div();

    if (!empty($taxonomies_style)) {
        foreach ($taxonomies_style as $taxonomy_style) {
            remove_meta_box($taxonomy_style, 'listings', 'side');
        }
    }

}

add_action('admin_init', 'stm_remove_meta_boxes');

/*Export settings*/
function stm_export_settings()
{
    if (!empty($_GET['export_settings'])) {
        $options = get_option('stm_vehicle_listing_options');

        header('Content-disposition: attachment; filename=file.json');
        header('Content-type: application/json');
        echo json_encode($options);
        exit();
    }

    if (!empty($_FILES) && !empty($_FILES['import_settings']) && !empty($_FILES['import_settings']['tmp_name'])) {
        $json = json_decode(file_get_contents($_FILES['import_settings']['tmp_name']), true);
        if (!empty($json)) {
            update_option('stm_vehicle_listing_options', $json);
        }
    }

}

add_action('init', 'stm_export_settings');

function stm_listings_get_my_options_list()
{
    return get_option('stm_vehicle_listing_options', array());
}

function stm_listings_parent_choice()
{
    $select_options = array(
        '' => esc_html__('No parent', 'stm_vehicles_listing')
    );
    $options = stm_listings_get_my_options_list();
    foreach ($options as $key => $option) {
        $slug = $option['slug'];
        $select_options[$slug] = $option['single_name'];
    }

    return $select_options;
}

function stm_listings_reserved_terms()
{
    $reserved_terms = array(
        'attachment',
        'attachment_id',
        'author',
        'author_name',
        'calendar',
        'cat',
        'category',
        'category__and',
        'category__in',
        'category__not_in',
        'category_name',
        'comments_per_page',
        'comments_popup',
        'cpage',
        'day',
        'debug',
        'error',
        'exact',
        'feed',
        'hour',
        'link_category',
        'm',
        'minute',
        'monthnum',
        'more',
        'name',
        'nav_menu',
        'nopaging',
        'offset',
        'order',
        'orderby',
        'p',
        'page',
        'page_id',
        'paged',
        'pagename',
        'pb',
        'perm',
        'post',
        'post__in',
        'post__not_in',
        'post_format',
        'post_mime_type',
        'post_status',
        'post_tag',
        'post_type',
        'posts',
        'posts_per_archive_page',
        'posts_per_page',
        'preview',
        'robots',
        's',
        'search',
        'second',
        'sentence',
        'showposts',
        'static',
        'subpost',
        'subpost_id',
        'tag',
        'tag__and',
        'tag__in',
        'tag__not_in',
        'tag_id',
        'tag_slug__and',
        'tag_slug__in',
        'taxonomy',
        'tb',
        'term',
        'type',
        'w',
        'withcomments',
        'withoutcomments',
        'year'
    );

    return apply_filters('stm_listings_reserved_terms_filter', $reserved_terms);
}

function stm_vehicles_listing_get_icons_html()
{
    $fa_icons = stm_get_cat_icons('fa');
    $custom_icons = stm_get_cat_icons('type_1');
    $new_icons_set = stm_get_cat_icons('service_icons');
    $boat_icons_set = stm_get_cat_icons('boat_icons');
    $moto_icons_set = stm_get_cat_icons('moto_icons');
    $rent_icons_set = stm_get_cat_icons('rental_icons');

    if (!empty($new_icons_set)) {
        $custom_icons = array_merge($custom_icons, $new_icons_set);
    }

    if (!empty($boat_icons_set)) {
        $custom_icons = array_merge($custom_icons, $boat_icons_set);
    }

    if (!empty($moto_icons_set)) {
        $custom_icons = array_merge($custom_icons, $moto_icons_set);
    }

    if (!empty($rent_icons_set)) {
        $custom_icons = array_merge($custom_icons, $rent_icons_set);
    }

    $counter = 0;
    ?>

    <div class="stm_vehicles_listing_icons">
        <div class="overlay"></div>
        <div class="inner">
            <!--Nav-->
            <div class="stm_font_nav">
                <div>
                    <a href="#stm_font-awesome"
                       class="active"><?php esc_html_e('Font Awesome', 'stm_vehicles_listing') ?></a>
                </div>
                <div>
                    <a href="#stm_font-motors"><?php esc_html_e('Motors Pack', 'stm_vehicles_listing') ?></a>
                </div>
            </div>
            <div class="scrollable-content">
                <!--Content-->
                <div id="stm_font-awesome" class="stm_theme_font active">
                    <table class="form-table">
                        <tr>
                            <?php foreach ($fa_icons as $fa_icon):
                            $counter++; ?>
                            <td class="stm-listings-pick-icon">
                                <i class="fa fa-<?php echo esc_attr($fa_icon); ?>"></i>
                                <i class="fa fa-<?php echo esc_attr($fa_icon); ?> big_icon"></i>
                            </td>
                            <?php if ($counter % 15 == 0): ?>
                        </tr>
                        <tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    </table>
                </div>

                <div id="stm_font-motors" class="stm_theme_font">
                    <table class="form-table">
                        <tr>
                            <?php $counter = 0;
                            foreach ($custom_icons as $custom_icon):
                            $counter++; ?>
                            <td class="stm-listings-pick-icon stm-listings-<?php echo esc_attr($custom_icon); ?>">
                                <i class="<?php echo esc_attr($custom_icon); ?>"></i>
                                <i class="<?php echo esc_attr($custom_icon); ?> big_icon"></i>
                            </td>
                            <?php if ($counter % 15 == 0): ?>
                        </tr>
                        <tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="stm_how_to_add">
                <a href="https://stylemixthemes.com/manuals/consulting/#custom_icons" target="_blank">
                    <?php esc_html_e('How to add new icon pack', 'stm_vehicles_listing'); ?><i
                        class="fa fa-external-link"></i>
                </a>
            </div>
        </div>
    </div>
<?php }

/*Update option*/
function stm_vehicle_listings_save_options($options)
{

    $settings = stm_listings_page_options();

    foreach ($options as $key => $option) {
        foreach ($option as $name => $item) {
            if ($settings[$name]['type'] == 'checkbox' and !empty($item) and $item) {
                $options[$key][$name] = 1;
            }
        }
    }

    update_option('stm_vehicle_listing_options', $options);
}

/*Ajax saving single option*/
function stm_listings_save_single_option_row()
{
    $data = array(
        'error' => false,
        'message' => ''
    );

    $options = stm_listings_get_my_options_list();

    /*Check number of setting*/
    if (!isset($_POST['stm_vehicle_listing_row_position'])) {
        $data['error'] = true;
        $data['message'] = esc_html__('Some error occurred', 'stm_vehicles_listing');
    } else {
        $option_key = intval($_POST['stm_vehicle_listing_row_position']);
    }

    /*Check if setting exists*/
    if (empty($options[$option_key])) {
        $data['error'] = true;
        $data['message'] = esc_html__('Some error occurred', 'stm_vehicles_listing');
    } else {
        $current_option = $options[$option_key];
    }

    /*Check POST*/
    if (empty($_POST)) {
        $data['error'] = true;
        $data['message'] = esc_html__('Some error occurred', 'stm_vehicles_listing');
    } else {
        $user_choice = $_POST;
    }

    if (!$data['error']) {

        $settings = stm_listings_page_options();

        foreach ($settings as $setting_name => $setting) {
            if (strpos($setting_name, 'divider') === false) {
                if (!empty($user_choice[$setting_name])) {
                    $current_option[$setting_name] = sanitize_text_field($user_choice[$setting_name]);
                } else {
                    $current_option[$setting_name] = '';
                }
            }
        }

        if (empty($current_option['listing_rows_numbers_enable'])) {
            $current_option['enable_checkbox_button'] = $current_option['listing_rows_numbers'] = '';
        }

        $options[$option_key] = $current_option;

        stm_vehicle_listings_save_options($options);

        $data['error'] = false;
        $data['message'] = esc_html__('Settings saved', 'stm_vehicles_listing');
        $data['data'] = $current_option;
    }

    wp_send_json($data);
    exit;
}

add_action('wp_ajax_stm_listings_save_single_option_row', 'stm_listings_save_single_option_row');

/*Deleting row*/
function stm_listings_delete_single_option_row()
{
    if (isset($_POST['number'])) {
        $options = stm_listings_get_my_options_list();
        $option_key = intval($_POST['number']);
        if (!empty($options[$option_key])) {
            unset($options[$option_key]);
            stm_vehicle_listings_save_options($options);
        }
    }
    exit;
}

add_action('wp_ajax_stm_listings_delete_single_option_row', 'stm_listings_delete_single_option_row');

/*Save new order*/
function stm_listings_save_option_order()
{
    if (isset($_POST['order'])) {
        $options = stm_listings_get_my_options_list();
        $new_options = explode(',', sanitize_text_field($_POST['order']));

        $new_order = array();

        foreach ($new_options as $option) {
            if (!empty($options[$option])) {
                $new_order[] = $options[$option];
            }
        }

        stm_vehicle_listings_save_options($new_order);

    }

    exit;
}

add_action('wp_ajax_stm_listings_save_option_order', 'stm_listings_save_option_order');

function stm_listings_add_new_option()
{
    $data = array(
        'error' => false,
        'message' => ''
    );

    $options = stm_listings_get_my_options_list();

    /*Get reserved terms*/
    $reserved_terms = stm_listings_reserved_terms();

    $new_option = $_POST;

    if (empty($new_option['slug']) and !empty($new_option['single_name'])) {
        $new_option['slug'] = sanitize_title($new_option['single_name']);
    }

    if (empty($new_option['single_name']) or empty($new_option['plural_name']) or empty($new_option['slug'])) {
        $data['error'] = true;
        $data['message'] = esc_html__('Singular, Plural names and Slug are required', 'stm_vehicles_listing');
    } else {
        $new_option['slug'] = sanitize_title($new_option['slug']);

        if (in_array($new_option['slug'], $reserved_terms) or taxonomy_exists($new_option['slug'])) {
            $data['error'] = true;
            $data['message'] = esc_html__('Slug name is already in use. Please choose another slug name.', 'stm_vehicles_listing');
        }
    }

    if (!$data['error']) {

        $settings = stm_listings_page_options();

        foreach ($settings as $setting_name => $setting) {

            if (!empty($new_option[$setting_name])) {
                $current_option[$setting_name] = sanitize_text_field($new_option[$setting_name]);
            } else {
                if (strpos($setting_name, 'divider') === false) {
                    $current_option[$setting_name] = '';
                }
            }
        }

        if (empty($current_option['listing_rows_numbers_enable'])) {
            $current_option['enable_checkbox_button'] = $current_option['listing_rows_numbers'] = '';
        }

        $numeric = ($new_option['numeric']) ? esc_html__('Yes', STM_LISTING) : esc_html__('No', STM_LISTING);
        $link = get_site_url() . "/wp-admin/edit-tags.php?taxonomy=" . esc_attr($new_option['slug']) . "&post_type=listings";

        $options[] = $current_option;

        $data['option'] = array(
            'key' => max(array_keys($options)),
            'name' => $new_option['single_name'],
            'plural' => $new_option['plural_name'],
            'slug' => $new_option['slug'],
            'numeric' => $numeric,
            'link' => $link
        );

        stm_vehicle_listings_save_options($options);
    }

    wp_send_json($data);
    exit;
}

add_action('wp_ajax_stm_listings_add_new_option', 'stm_listings_add_new_option');

function stm_test_force_update()
{
    $stm_listings = array(
        1 => array(
            'single_name' => 'Condition',
            'plural_name' => 'Conditions',
            'slug' => 'condition',
            'font' => '',
            'numeric' => false,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => false,
            'use_on_car_archive_listing_page' => false,
            'use_on_single_car_page' => false,
            'use_on_map_page' => false,
            'use_on_car_filter' => true,
            'use_on_car_modern_filter' => true,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => true,
        ),
        2 => array(
            'single_name' => 'Body',
            'plural_name' => 'Bodies',
            'slug' => 'body',
            'font' => 'stm-service-icon-body_type',
            'numeric' => false,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => false,
            'use_on_car_archive_listing_page' => false,
            'use_on_single_car_page' => true,
            'use_on_map_page' => false,
            'use_on_car_filter' => false,
            'use_on_car_modern_filter' => false,
            'use_on_car_modern_filter_view_images' => true,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => false,
            'listing_rows_numbers' => 'two_cols',
            'enable_checkbox_button' => false,
        ),
        3 => array(
            'single_name' => 'Make',
            'plural_name' => 'Makes',
            'slug' => 'make',
            'font' => '',
            'numeric' => false,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => false,
            'use_on_car_archive_listing_page' => false,
            'use_on_single_car_page' => false,
            'use_on_map_page' => true,
            'use_on_car_filter' => true,
            'use_on_car_modern_filter' => true,
            'use_on_car_modern_filter_view_images' => true,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => true,
            'enable_checkbox_button' => false,
            'use_in_footer_search' => true,
        ),
        5 => array(
            'single_name' => 'Model',
            'plural_name' => 'Models',
            'slug' => 'serie',
            'font' => '',
            'numeric' => false,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => false,
            'use_on_car_archive_listing_page' => false,
            'use_on_single_car_page' => false,
            'use_on_map_page' => true,
            'use_on_car_filter' => true,
            'use_on_car_modern_filter' => false,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => true,
            'enable_checkbox_button' => false,
            'use_in_footer_search' => true,
        ),
        6 => array(
            'single_name' => 'Mileage',
            'plural_name' => 'Mileages',
            'slug' => 'mileage',
            'font' => 'stm-icon-road',
            'numeric' => true,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => true,
            'use_on_car_archive_listing_page' => true,
            'use_on_single_car_page' => true,
            'use_on_map_page' => true,
            'use_on_car_filter' => true,
            'use_on_car_modern_filter' => false,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => false,
            'number_field_affix' => 'mi',
            'enable_checkbox_button' => false,
        ),
        7 => array(
            'single_name' => 'Fuel type',
            'plural_name' => 'Fuel types',
            'slug' => 'fuel',
            'font' => 'stm-icon-fuel',
            'numeric' => false,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => false,
            'use_on_car_archive_listing_page' => true,
            'use_on_single_car_page' => true,
            'use_on_map_page' => false,
            'use_on_car_filter' => false,
            'use_on_car_modern_filter' => false,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => false,
        ),
        8 => array(
            'single_name' => 'Engine',
            'plural_name' => 'Engines',
            'slug' => 'engine',
            'font' => 'stm-icon-engine_fill',
            'numeric' => true,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => false,
            'use_on_car_archive_listing_page' => true,
            'use_on_single_car_page' => true,
            'use_on_map_page' => true,
            'use_on_car_filter' => false,
            'use_on_car_modern_filter' => false,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => false,
            'enable_checkbox_button' => false,
            'use_in_footer_search' => false,
        ),
        9 => array(
            'single_name' => 'Year',
            'plural_name' => 'Years',
            'slug' => 'ca-year',
            'font' => 'stm-icon-road',
            'numeric' => false,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => false,
            'use_on_car_archive_listing_page' => false,
            'use_on_single_car_page' => false,
            'use_on_map_page' => true,
            'use_on_car_filter' => true,
            'use_on_car_modern_filter' => true,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => false,
            'enable_checkbox_button' => false,
        ),
        10 => array(
            'single_name' => 'Price',
            'plural_name' => 'Prices',
            'slug' => 'price',
            'font' => 'stm-icon-road',
            'numeric' => true,
            'slider' => true,
            'use_on_single_listing_page' => true,
            'use_on_car_listing_page' => true,
            'use_on_car_archive_listing_page' => true,
            'use_on_single_car_page' => false,
            'use_on_map_page' => true,
            'use_on_car_filter' => true,
            'use_on_car_modern_filter' => true,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => false,
            'enable_checkbox_button' => false,
        ),
        11 => array(
            'single_name' => 'Fuel consumption',
            'plural_name' => 'Fuel consumptions',
            'slug' => 'fuel-consumption',
            'font' => 'stm-icon-fuel',
            'numeric' => true,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => true,
            'use_on_car_archive_listing_page' => false,
            'use_on_single_car_page' => false,
            'use_on_map_page' => false,
            'use_on_car_filter' => false,
            'use_on_car_modern_filter' => false,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
        ),
        12 => array(
            'single_name' => 'Transmission',
            'plural_name' => 'Transmission',
            'slug' => 'transmission',
            'font' => 'stm-icon-transmission_fill',
            'numeric' => false,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => true,
            'use_on_car_archive_listing_page' => false,
            'use_on_single_car_page' => true,
            'use_on_map_page' => true,
            'use_on_car_filter' => true,
            'use_on_car_modern_filter' => true,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => false,
        ),
        13 => array(
            'single_name' => 'Drive',
            'plural_name' => 'Drives',
            'slug' => 'drive',
            'font' => 'stm-icon-drive_2',
            'numeric' => false,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => false,
            'use_on_car_archive_listing_page' => false,
            'use_on_single_car_page' => true,
            'use_on_map_page' => false,
            'use_on_car_filter' => false,
            'use_on_car_modern_filter' => true,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => false,
        ),
        14 => array(
            'single_name' => 'Fuel economy',
            'plural_name' => 'Fuel economy',
            'slug' => 'fuel-economy',
            'font' => '',
            'numeric' => true,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => false,
            'use_on_car_archive_listing_page' => false,
            'use_on_single_car_page' => false,
            'use_on_map_page' => false,
            'use_on_car_filter' => false,
            'use_on_car_modern_filter' => false,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => false,
            'enable_checkbox_button' => false,
        ),
        15 => array(
            'single_name' => 'Exterior Color',
            'plural_name' => 'Exterior Colors',
            'slug' => 'exterior-color',
            'font' => 'stm-service-icon-color_type',
            'numeric' => false,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => false,
            'use_on_car_archive_listing_page' => false,
            'use_on_single_car_page' => true,
            'use_on_map_page' => false,
            'use_on_car_filter' => false,
            'use_on_car_modern_filter' => false,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => false,
            'enable_checkbox_button' => false,
        ),
        16 => array(
            'single_name' => 'Interior Color',
            'plural_name' => 'Interior Colors',
            'slug' => 'interior-color',
            'font' => 'stm-service-icon-color_type',
            'numeric' => false,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => false,
            'use_on_car_archive_listing_page' => false,
            'use_on_single_car_page' => true,
            'use_on_map_page' => false,
            'use_on_car_filter' => false,
            'use_on_car_modern_filter' => false,
            'use_on_car_modern_filter_view_images' => false,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => false,
            'enable_checkbox_button' => false,
        ),
        17 => array(
            'single_name' => 'Features',
            'plural_name' => 'Features',
            'slug' => 'features',
            'font' => '',
            'numeric' => false,
            'use_on_single_listing_page' => false,
            'use_on_car_listing_page' => false,
            'use_on_car_archive_listing_page' => false,
            'use_on_single_car_page' => false,
            'use_on_map_page' => false,
            'use_on_car_filter' => false,
            'use_on_car_modern_filter' => false,
            'use_on_car_modern_filter_view_images' => true,
            'use_on_car_filter_links' => false,
            'use_on_directory_filter_title' => false,
            'listing_rows_numbers' => 'one_col',
            'enable_checkbox_button' => true,
        ),
    );

    update_option('stm_vehicle_listing_options', $stm_listings);
}