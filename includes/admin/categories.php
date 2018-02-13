<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stm_listings_page_options() {
    $options = array(
        'single_name' => array(
            'label' => esc_html__('Singular name', 'stm_vehicles_listing'),
            'value' => '',
            'type' => 'text'
        ),
        'plural_name' => array(
            'label' => esc_html__('Plural name', 'stm_vehicles_listing'),
            'value' => '',
            'type' => 'text'
        ),
        'slug' => array(
            'label' => esc_html__('Slug', 'stm_vehicles_listing'),
            'description' => esc_html__('Caution, you will not be able to change the link later', 'stm_vehicles_listing'),
            'value' => '',
            'type' => 'text',
        ),
        'font' => array(
            'label' => esc_html__('Choose icon', 'stm_vehicles_listing'),
            'value' => '',
            'type' => 'icon',
        ),
        'divider_1' => array(
            'type' => 'divider'
        ),
        'numeric' => array(
            'label' => esc_html__('Number field', 'stm_vehicles_listing'),
            'description' => esc_html__('Numeric value will be compared in another way (useful for mileage or fuel economy)', 'stm_vehicles_listing'),
            'value' => '',
            'type' => 'checkbox'
        ),
        'number_field_affix' => array(
            'label' => '',
            'description' => esc_html__('This affix will be shown after number. Example: mi, pcs, etc.', 'stm_vehicles_listing'),
            'value' => '',
            'dependency' => array(
                'slug' => 'numeric',
                'type' => 'not_empty'
            ),
            'attributes' => array(
                'placeholder' => esc_html__('Number field affix', 'stm_vehicles_listing')
            ),
            'type' => 'text'
        ),
        'slider' => array(
            'label' => esc_html__('Display field as slider', 'stm_vehicles_listing'),
            'description' => esc_html__('Only for number field', 'stm_vehicles_listing'),
            'dependency' => array(
                'slug' => 'numeric',
                'type' => 'not_empty'
            ),
            'value' => '',
            'type' => 'checkbox'
        ),
        'use_on_car_listing_page' => array(
            'label' => esc_html__('Use on item grid view', 'stm_vehicles_listing'),
            'description' => esc_html__('Check if you want to see this category on car listing page (machine card)', 'stm_vehicles_listing'),
            'value' => '',
            'preview' => 'grid.jpg',
            'type' => 'checkbox'
        ),
        'use_on_car_archive_listing_page' => array(
            'label' => esc_html__('Use on item list view', 'stm_vehicles_listing'),
            'description' => esc_html__('Check if you want to see this category on car listing archive page with icon', 'stm_vehicles_listing'),
            'value' => '',
            'preview' => 'list.jpg',
            'type' => 'checkbox'
        ),
        'use_on_single_car_page' => array(
            'label' => esc_html__('Use on single car page', 'stm_vehicles_listing'),
            'description' => esc_html__('Check if you want to see this category on single car page', 'stm_vehicles_listing'),
            'value' => '',
            'preview' => 'single_car_page.jpg',
            'type' => 'checkbox'
        ),
        'use_on_car_filter' => array(
            'label' => esc_html__('Use on car filter', 'stm_vehicles_listing'),
            'description' => esc_html__('Check if you want to see this category in filter', 'stm_vehicles_listing'),
            'value' => '',
            'type' => 'checkbox'
        ),
        'divider_2' => array(
            'type' => 'divider'
        ),
        'use_on_tabs' => array(
            'label' => esc_html__('Use in tabs', 'stm_vehicles_listing'),
            'description' => esc_html__('Check if you want to see this in Archive Page and Single Motorcycle Page', 'stm_vehicles_listing'),
            'value' => '',
            'preview' => 'tabs.jpg',
            'type' => 'checkbox'
        ),
        'use_on_car_modern_filter' => array(
            'label' => esc_html__('Use on car modern filter', 'stm_vehicles_listing'),
            'description' => esc_html__('Check if you want to see this category in modern filter', 'stm_vehicles_listing'),
            'value' => '',
            'preview' => 'modern.png',
            'type' => 'checkbox'
        ),
        'use_on_car_modern_filter_view_images' => array(
            'label' => esc_html__('Use images for this category', 'stm_vehicles_listing'),
            'description' => esc_html__('Check if you want to see this category with images', 'stm_vehicles_listing'),
            'value' => '',
            'type' => 'checkbox'
        ),
        'use_on_car_filter_links' => array(
            'label' => esc_html__('Use on car filter as block with links', 'stm_vehicles_listing'),
            'description' => esc_html__('Be aware of using both as filter option', 'stm_vehicles_listing'),
            'value' => '',
            'preview' => 'car-filter-as-block-with-links.jpg',
            'type' => 'checkbox'
        ),
        'use_in_footer_search' => array(
            'label' => esc_html__('Use in footer search', 'stm_vehicles_listing'),
            'value' => '',
            'preview' => 'footer.jpg',
            'type' => 'checkbox'
        ),
        'divider_3' => array(
            'type' => 'divider'
        ),
        'use_on_directory_filter_title' => array(
            'label' => esc_html__('Use this category in generated Listing Filter title', 'stm_vehicles_listing'),
            'description' => esc_html__('Enable this field, if you want to include category in Listing Filter title.', 'stm_vehicles_listing'),
            'value' => '',
            'preview' => 'title.jpg',
            'type' => 'checkbox'
        ),
        'use_on_single_listing_page' => array(
            'label' => esc_html__('Use on single listing page', 'stm_vehicles_listing'),
            'description' => esc_html__('Check if you want to see this category on single page', 'stm_vehicles_listing'),
            'value' => '',
            'type' => 'checkbox'
        ),
        'listing_taxonomy_parent' => array(
            'label' => esc_html__('Set parent taxonomy', 'stm_vehicles_listing'),
            'value' => '',
            'type' => 'select',
            'choices' => stm_listings_parent_choice()
        ),
        'listing_rows_numbers_enable' => array(
            'label' => esc_html__('Use on listing archive as checkboxes', 'stm_vehicles_listing'),
            'description' => esc_html__('Use as checkboxes with images 1 or 2 columns', 'stm_vehicles_listing'),
            'value' => '',
            'preview' => 'column.png',
            'type' => 'checkbox',
        ),
        'listing_rows_numbers' => array(
            'label' => esc_html__('Use on listing archive as checkboxes', 'stm_vehicles_listing'),
            'description' => esc_html__('Use as checkboxes with images 1 or 2 columns', 'stm_vehicles_listing'),
            'value' => '',
            'type' => 'radio',
            'dependency' => array(
                'slug' => 'listing_rows_numbers_enable',
                'type' => 'not_empty'
            ),
            'choices' => array(
                'one_col' => esc_html__('Use as 1 column per row', 'stm_vehicles_listing'),
                'two_cols' => esc_html__('Use as 2 columns per row', 'stm_vehicles_listing'),
            )
        ),
        'enable_checkbox_button' => array(
            'label' => esc_html__('Add submit button to this checkbox area', 'stm_vehicles_listing'),
            'description' => esc_html__('AJAX filter will be triggered after clicking on button', 'stm_vehicles_listing'),
            'value' => '',
            'type' => 'checkbox',
            'dependency' => array(
                'slug' => 'listing_rows_numbers_enable',
                'type' => 'not_empty'
            ),
        ),
        'show_in_admin_column' => array(
            'label' => esc_html__('Show in admin column table', 'stm_vehicles_listing'),
            'description' => esc_html__('This column will be shown in admin', 'stm_vehicles_listing'),
            'value' => '',
            'type' => 'checkbox',
            'preview' => 'admin_table.png',
        ),
    );

    if(stm_is_listing()){
        $options = array_merge($options, array(
        'use_on_map_page' => array(
            'label' => esc_html__('Use on map page', 'stm_vehicles_listing'),
            'description' => esc_html__('Check if you want to see this category on map page', 'stm_vehicles_listing'),
            'value' => '',
            'preview' => 'map_infowindow.png',
            'type' => 'checkbox'
        )));
    }

    return apply_filters('stm_listings_page_options_filter', $options);
}

function stm_add_listing_theme_menu_item() {
    add_submenu_page(
        'edit.php?post_type=listings',
        __("Listing Categories", 'stm_vehicles_listing'),
        __("Listing Categories", 'stm_vehicles_listing'),
        'manage_options',
        'listing_categories',
        'stm_listings_vehicle_listing_settings_page'
    );
}

add_action("admin_menu", "stm_add_listing_theme_menu_item");

function stm_listings_vehicle_listing_settings_page() {
    /*Get all stored options*/
    $options = stm_listings_get_my_options_list();
    /*Get options to show*/
    $options_list = stm_listings_page_options();
?>

    <div class="stm_vehicles_listing_categories">
        <div class="image-preview">
            <div class="overlay"></div>
        </div>
        <div class="stm_start"><?php esc_html_e('Vehicle listing Settings', 'stm_vehicles_listing'); ?></div>
        <div class="stm_import_export">
            <div class="export_settings">
                <form method="post" enctype="multipart/form-data">
                    <div class="stm_admin_listings_fake">
                        <input type="file" name="import_settings" required/>
                        <div class="fake_button"></div>
                        <div class="fake_text"><?php esc_html_e('Choose a file...', 'stm_vehicles_listing'); ?></div>
                    </div>
                    <input type="hidden" name="import_listing_settings" value="import" required/>
                    <button type="submit" class="button button-primary" disabled><i class="fa fa-sign-in"></i> <?php esc_html_e('Set my settings'); ?></button>
                </form>

                <a href="<?php echo esc_url( add_query_arg( 'export_settings', '1', 'edit.php?post_type=listings&amp;page=listing_categories' ) ); ?>" class="button button-primary">
                    <i class="fa fa-sign-out"></i> <?php esc_html_e('Get my settings', 'stm_vehicles_listing'); ?>
                </a>
            </div>
        </div>

        <div class="stm_vehicles_listing_content">
            <table class="wp-list-table widefat listing_categories listing_categories_edit">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Singular', 'stm_vehicles_listing'); ?></th>
                        <th><?php esc_html_e('Plural', 'stm_vehicles_listing'); ?></th>
                        <th><?php esc_html_e('Slug', 'stm_vehicles_listing'); ?></th>
                        <th><?php esc_html_e('Numeric', 'stm_vehicles_listing'); ?></th>
                        <th><?php esc_html_e('Manage', 'stm_vehicles_listing'); ?></th>
                        <th><?php esc_html_e('Edit', 'stm_vehicles_listing'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($options)): ?>
                        <?php foreach($options as $option_key => $option): ?>
                            <tr class="stm_listings_settings_head" data-tr="<?php echo $option_key ?>">
                                <td class="highlited"><?php printf(esc_html__('%s', 'stm_vehicles_listing'), $option['single_name']); ?></td>
                                <td><?php printf(esc_html__('%s', 'stm_vehicles_listing'), $option['plural_name']); ?></td>
                                <td><?php printf(esc_html__('%s', 'stm_vehicles_listing'), $option['slug']); ?></td>
                                <td><?php $option['numeric'] ? esc_html_e('Yes', 'stm_vehicles_listing') : esc_html_e('No', 'stm_vehicles_listing'); ?></td>
                                <td class="manage"><i class="fa fa-list-ul" data-url="<?php echo get_site_url() . "/wp-admin/edit-tags.php?taxonomy=" . esc_attr($option['slug']) . "&post_type=listings"; ?>"></i></td>
                                <td><i class="fa fa-pencil"></i></td>
                            </tr>
                            <tr class="stm_listings_settings_tr" data-tr="<?php echo $option_key ?>">
                                <td colspan="7">
                                    <form action="" method="post">
                                        <div class="stm_vehicles_listing_option_meta">
                                            <div class="stm_vehicles_listing_row_options">
                                                <div class="stm_listings_col_4">
                                                    <div class="inner">
                                                        <input name="stm_vehicle_listing_row_position" type="hidden" value="<?php echo $option_key; ?>" />
                                                        <?php foreach($options_list as $option_name => $option_settings): ?>

                                                            <?php stm_vehicles_listings_show_field($option_name, $option_settings, $option); ?>

                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="stm_vehicles_listing_row_actions">
                                                <a href="#save" class="button button-primary button-large"><?php esc_html_e('Save', 'stm_vehicles_listing'); ?></a>
                                                <div class="stm_response_message"></div>

                                                <a href="#cancel" class="button button-secondary button-large"><?php esc_html_e('Cancel', 'stm_vehicles_listing'); ?></a>
                                                <a href="#delete" class="button button-secondary button-large">
                                                    <i class="fa fa-trash-o"></i>
                                                    <?php esc_html_e('Delete', 'stm_vehicles_listing'); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </tbody>
            </table>

            <div class="stm_vehicles_add_new">
                <div class="stm_vehicles_listings_add_new_row">
                    <i class="fa fa-plus"></i><?php esc_html_e('Add new', 'stm_vehicles_listing'); ?>
                </div>
                <table class="wp-list-table widefat listing_categories listing_categories_add_new">
                    <tbody>
                        <tr class="stm_listings_settings_tr">
                            <td colspan="7">
                                <form action="" method="post">
                                    <div class="stm_vehicles_listing_option_meta">
                                        <div class="stm_vehicles_listing_row_options">
                                            <div class="stm_listings_col_4">
                                                <div class="inner">
                                                    <?php foreach($options_list as $option_name => $option_settings): ?>

                                                        <?php stm_vehicles_listings_show_field($option_name, $option_settings, array()); ?>

                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="stm_vehicles_listing_row_actions">
                                            <a href="#add_new" class="button button-primary button-large"><?php esc_html_e('Save', 'stm_vehicles_listing'); ?></a>
                                            <div class="stm_response_message"></div>

                                            <a href="#delete" class="button button-secondary button-large">
                                                <i class="fa fa-trash-o"></i>
                                                <?php esc_html_e('Delete', 'stm_vehicles_listing'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php stm_vehicles_listing_get_icons_html(); ?>

<?php }

function stm_vehicles_listings_show_field($name, $settings, $values) {
    $type = 'stm_vehicle_listings_field_text';
    if(!empty($settings['type'])) {
        $type = 'stm_vehicle_listings_field_' . $settings['type'];
    }

    $type($name, $settings, $values);
}

function stm_vehicle_listings_field_text($name, $settings, $values) {
    $value = (!empty($values[$name])) ? $values[$name] : '';
    $atts = (!empty($settings['attributes'])) ? $settings['attributes'] : array();
    $input_atts = '';
    if(!empty($atts)) {
        foreach($atts as $key => $att) {
            $input_atts .= $key . '="' . $att . '" ';
        }
    }
    ?>
    <div class="stm_form_wrapper stm_form_wrapper_<?php echo $settings['type']; ?> stm_form_wrapper_<?php echo $name; ?> <?php stm_vehicles_listing_has_preview($settings); ?>" <?php stm_vehicles_listing_show_dependency($settings); ?>>
        <label>
            <span><?php echo $settings['label']; ?></span>
            <input <?php echo $input_atts; ?> type="<?php echo $settings['type'] ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />
        </label>
        <?php stm_vehicles_listings_preview($settings); ?>
    </div>
<?php }

function stm_vehicle_listings_field_select($name, $settings, $values) { ?>
    <div class="stm_form_wrapper stm_form_wrapper_<?php echo $settings['type']; ?>" <?php stm_vehicles_listing_show_dependency($settings); ?>>
        <span><?php echo $settings['label']; ?></span>
        <select name="<?php echo $name; ?>">
            <?php foreach($settings['choices'] as $value => $label):
                $selected = (!empty($values[$name]) and $values[$name] == $value) ? 'selected' : ''; ?>
                <option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
<?php }

function stm_vehicle_listings_field_radio($name, $settings, $values) {
        if(empty($values[$name])) {
            $first_key = array_keys($settings['choices']);
            $values[$name] = $first_key[0];
        }
    ?>
    <div class="stm_form_wrapper stm_form_wrapper_<?php echo $settings['type']; ?>" <?php stm_vehicles_listing_show_dependency($settings); ?>>
        <?php foreach($settings['choices'] as $value => $label): ?>
            <label>
                <input type="radio" name="<?php echo $name; ?>" value="<?php echo $value; ?>" <?php checked($values[$name], $value); ?> />
                <span><?php echo $label; ?></span>
            </label>
        <?php endforeach; ?>
    </div>
<?php }

function stm_vehicle_listings_field_checkbox($name, $settings, $values) {
    $selected = (!empty($values[$name])) ? 'checked' : '';
    ?>
    <div class="stm_form_wrapper stm_form_wrapper_<?php echo $settings['type']; ?>  stm_form_wrapper_<?php echo $name; ?> <?php stm_vehicles_listing_has_preview($settings); ?>" <?php stm_vehicles_listing_show_dependency($settings); ?>>
        <label>
            <input type="<?php echo $settings['type'] ?>" name="<?php echo $name; ?>" <?php echo $selected; ?> />
            <span><?php echo $settings['label']; ?></span>
        </label>
        <?php stm_vehicles_listings_preview($settings); ?>
    </div>
<?php }

function stm_vehicle_listings_field_divider($name) { ?>
    </div></div><div class="stm_listings_col_4 stm_<?php echo $name; ?>"><div class="inner">
<?php }

function stm_vehicle_listings_field_icon($name, $settings, $values) {
    $icon = (!empty($values[$name])) ? $values[$name] : '';
    $value = (!empty($values[$name])) ? $values[$name] : ''; ?>
    <div class="stm_form_wrapper stm_form_wrapper_<?php echo $settings['type']; ?>">
        <span><?php echo $settings['label']; ?></span>
        <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />
        <div class="stm_vehicles_listing_icon">
            <div class="icon">
                <img src="<?php echo STM_LISTINGS_URL ?>/assets/images/plus.svg" class="stm-default-icon_<?php echo $value ?>" />
                <i class="<?php echo $icon; ?>"></i>
            </div>
            <?php if(empty($value)): ?>
                <div class="stm_change_icon"><?php esc_html_e('Add icon', 'stm_vehicles_listing'); ?></div>
            <?php else: ?>
                <div class="stm_change_icon"><?php esc_html_e('Change icon', 'stm_vehicles_listing'); ?></div>
            <?php endif; ?>
            <div class="stm_delete_icon"><?php esc_html_e('Delete icon', 'stm_vehicles_listing'); ?></div>
        </div>
    </div>
<?php }

function stm_vehicles_listing_show_dependency($settings) {
    $dependency = '';
    if(!empty($settings['dependency'])) {
        $dependency = 'data-depended="true" ';
        foreach($settings['dependency'] as $slug => $value) {
            $dependency .= 'data-' . $slug . '="' . $value . '"' ;
        }
    }
    echo $dependency;
}

function stm_vehicles_listing_has_preview($settings) {
    $class = '';
    if(!empty($settings['preview'])) {
        $class = 'stm-has-preview-image';
    }
    echo $class;
}

function stm_vehicles_listings_preview($settings) {
    if(!empty($settings['preview'])): ?>
        <a href="#" data-image="<?php echo STM_LISTINGS_URL ?>/assets/images/tmp/<?php echo $settings['preview']; ?>">
            <?php esc_html_e('Preview', 'stm_vehicles_listing'); ?>
        </a>
    <?php endif;
}

/*Admin panel code*/
/*Add featured image in admin table*/
function stm_listings_display_posts_stickiness( $column, $post_id ) {
	if ( $column == 'stm_image' ) {

		if(has_post_thumbnail($post_id)) {
		    echo '<div class="attachment">';
		    echo '<div class="attachment-preview">';
		    echo '<div class="thumbnail">';
		    echo '<div class="centered">';
			echo '<a href="' . get_edit_post_link( $post_id ) . '">' . get_the_post_thumbnail($post_id, 'medium') . '</a>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
	}

	$user_columns = stm_get_numeric_admin_fields();
	if(!empty($user_columns[$column])) {
	    $col = str_replace('stm-column-', '', $column);
	    if($col == 'price') {
	        $col = 'stm_genuine_price';
	    }
	    $value = get_post_meta($post_id, $col, true);
	    if(empty($value)) {
	        $value = '—';
	    } else {
	        if(function_exists('stm_listing_price_view')) {
	            if($col == 'stm_genuine_price') {
	                $value = stm_listing_price_view($value);
	            }
	        }
	    }
	    echo $value;
	}

}
add_action( 'manage_' . stm_listings_post_type() . '_posts_custom_column', 'stm_listings_display_posts_stickiness', 10, 2 );

/* Add custom column to post list */
function stm_listings_add_sticky_column( $columns ) {
	unset( $columns['author'], $columns['comments'], $columns['date'] );

	$new_columns = array();
	$new_columns['cb']    = '<input type="checkbox" />';
	$new_columns['stm_image'] = __( 'Image', 'stm_vehicles_listing' );

	$user_columns = stm_get_numeric_admin_fields();
	if(!empty($user_columns)) {
        foreach($user_columns as $key => $value) {
            $columns[$key] = $value;
        }
	}

	return array_merge($new_columns, $columns);
}

add_filter( 'manage_' . stm_listings_post_type() . '_posts_columns', 'stm_listings_add_sticky_column' );

function stm_get_numeric_admin_fields() {
    $cols = array();
    $options = get_option('stm_vehicle_listing_options');
    if (!empty($options)) {
        foreach ($options as $option) {
            if(!empty($option['numeric'])) {
                if (!empty($option['show_in_admin_column']) and $option['show_in_admin_column'] and $option['numeric']) {
                    $cols['stm-column-' . $option['slug']] = esc_html__($option['single_name'], 'stm_vehicles_listing');
                }
            }
        }
    }
    return $cols;
}