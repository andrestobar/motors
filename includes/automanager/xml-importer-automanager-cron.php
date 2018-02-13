<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action( 'stm_cron_hook', 'stm_cron_hook' );

function stm_cron_hook() {

	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	require_once(ABSPATH . "wp-admin" . '/includes/file.php');
	require_once(ABSPATH . "wp-admin" . '/includes/media.php');

	$templates = get_option('stm_xml_templates');
	$current_template = get_option('stm_current_template');

	$posts_info = importFromTemplateName($current_template);

	//$mail_titles = array();

	foreach($posts_info as $post_info) {

		$update_post = false;

		/* Strict fields */
		$post_to_insert = array(
			'post_title' => str_replace('N/A', '', $post_info['title']),
			'post_content' => $post_info['content'],
			'post_status' => $post_info['status'],
			'post_type' => 'listings',
		);

		/* Additional fields, included by theme */
		$additional_fields = array(
			'stock_number'   			 => '',
			'vin'	       				 => '',
			'city_mpg'	   				 => '',
			'highway_mpg'  				 => '',
			'regular_price_label'        => '',
			'regular_price_description'  => '',
			'special_price_label'        => '',
			'instant_savings_label'      => '',
		);

		foreach($additional_fields as $key => $value) {
			if(!empty($post_info[$key])){
				$additional_fields[$key] = $post_info[$key];
			}
		}

		$additional_fields = array_filter($additional_fields);

		// Filter fields
		$filter_taxes = stm_get_taxonomies();
		$filter_fields = array();

		if(!empty($filter_taxes)){
			foreach($filter_taxes as $key => $value) {
				if(!empty($post_info[$value])){
					$filter_fields[$value] = $post_info[$value];
				}
			}
			if(!empty($post_info['sale_price'])){
				$filter_fields['sale_price'] = $post_info['sale_price'];
			}
		}

		$args = array(
			'post_type'  => 'listings',
			'post_status' => array('publish', 'draft'),
			'meta_query' => array(
				array(
					'key'     => 'automanager_id',
					'value'   => $post_info['id'],
					'compare' => '=',
				),
			),
		);

		$query = new WP_Query( $args );

		if($query->post_count == 1 and !empty($query->posts[0])) {
			$post_to_insert_id = $query->posts[0]->ID;
			$update_post = true;
		} else {
			// Insert post
			$post_to_insert_id = wp_insert_post( $post_to_insert );
			$update_post = false;
		}

		if(!empty($post_to_insert_id)) {

			if($update_post) {
				$update_post_args = array(
					'ID' => $post_to_insert_id,
					'post_title' => str_replace('N/A', '', $post_info['title']),
					'post_content' => $post_info['content'],
				);

				wp_update_post($update_post_args);
			}

			// Add vehicle ID
			if(!empty($post_info['id'])) {
				update_post_meta( $post_to_insert_id, 'automanager_id', $post_info['id'] );
			}

			update_post_meta($post_to_insert_id, 'title', 'hide');

			// Default theme fields
			if(!empty($additional_fields)){
				foreach($additional_fields as $additional_key => $additional_field){
					if($additional_key == 'vin') {
						$additional_key = 'vin_number';
						$history_link = 'http://clients.automanager.com/scripts/autocheckreport.aspx?VID=' . $additional_field;
						update_post_meta( $post_to_insert_id, 'history_link', $history_link );
					}
					update_post_meta( $post_to_insert_id, $additional_key, $additional_field );
				}
			}

			// Insert filter fields in categories and in post meta
			if(!empty($filter_fields)){
				foreach($filter_fields as $filter_key => $filter_value) {
					if(!empty($filter_value)) {
						if($filter_key != 'sale_price') {
							if($filter_key == 'price') {
								update_post_meta( $post_to_insert_id, $filter_key, intval($filter_value) );
							} else {
								$numeric = stm_get_taxonomies_with_type($filter_key);
								if(!empty($numeric) and !empty($numeric['numeric']) and $numeric['numeric']) {
									update_post_meta( $post_to_insert_id, $filter_key, $filter_value );
								} else {
									$terms = wp_add_object_terms( $post_to_insert_id, $filter_value, $filter_key );

									if(!is_wp_error($terms)){
										$current_term = get_term(reset($terms), $filter_key);

										update_post_meta( $post_to_insert_id, $filter_key, $current_term->slug );
									}
								}
							}
						} else {
							//If no price, but we have sale price, set sale price as main price
							if(!empty($filter_fields['price']) and $filter_fields['price'] != 'N/A') {
								update_post_meta( $post_to_insert_id, $filter_key, intval( $filter_value ) );
							} else {
								update_post_meta( $post_to_insert_id, 'price', intval( $filter_value ) );
							}
						}
					}
				}
			}

			// Featured image
			if(!empty($post_info['featured_image'])) {

				/*Download again*/
				$featured_exist = false;
				if($update_post) {
					$current_featured_image_id = get_post_thumbnail_id( $post_to_insert_id );
					if(!empty($current_featured_image_id)) {
						if(md5_file(esc_url($post_info['featured_image'])) == md5_file(get_attached_file( $current_featured_image_id ))) {
							$featured_exist = true;
						}
					}
				}

				if(!$featured_exist) {
					$featured_image_id = media_sideload_image( $post_info['featured_image'], intval( $post_to_insert_id ), $post_info['title'], 'src' );
					if ( gettype( $featured_image_id ) == 'string' ) {
						set_post_thumbnail( $post_to_insert_id, stm_get_image_id( $featured_image_id ) );

						echo '<div>';
						esc_html_e( 'Featured image downloaded.', 'stm_vehicles_listing' );
						echo '</div>';
					}
				}
			}

			// Add gallery
			if(!empty($post_info['gallery']) and gettype($post_info['gallery']) == 'array') {
				$gallery_images = $post_info['gallery'];

				$gallery_keys = array();
				$exist_photos = array();

				/*Get uploaded images*/
				if($update_post) {
					$current_gallery = get_post_meta( $post_to_insert_id, 'gallery', true );

					if ( ! empty( $current_gallery ) ) {
						foreach ( $current_gallery as $current_gallery_media_id ) {
							$post_thumbnail = md5_file( get_attached_file( $current_gallery_media_id ) );
							$exist_photos[$current_gallery_media_id] = $post_thumbnail;
						}
					}
				}

				foreach ( $gallery_images as $gallery_image ) {
					$exist = false;

					if ( $update_post ) {
						$image_url = md5_file( esc_url( $gallery_image) );

						$key = array_search( $image_url, $exist_photos );

						if ( ! empty( $key ) ) {
							$gallery_keys[] = $key;
							$exist          = true;
						}
					}


					if ( ! $exist ) {
						$featured_image_src = media_sideload_image( $gallery_image, 0, $post_info['title'], 'src' );
						if ( gettype( $featured_image_src ) == 'string' ) {
							$gallery_keys[] = stm_get_image_id( $featured_image_src );
						}
					}
				}

				update_post_meta( $post_to_insert_id, 'gallery', $gallery_keys );
			}
		}
	}

	stm_place_draft_deleted();
}

$cron_active = get_option('stm_enable_cron_automanager');

if(!empty($cron_active) and $cron_active) {
	$templates = get_option('stm_xml_templates');
	$current_template = get_option('stm_current_template');

	$delay = $templates[$current_template]['settings']['import_delay'];
	if( !wp_next_scheduled( 'stm_cron_hook' ) ) {

		$first_occur = 3600;
		if($delay == 'twicedaily') {
			$first_occur = $first_occur * 12;
		} elseif ($delay == 'daily') {
			$first_occur = $first_occur * 24;
		}

		wp_schedule_event( time() + $first_occur, $delay, 'stm_cron_hook' );
		update_option('stm_enable_cron_automanager', '1');
	}
} else {
	wp_unschedule_event( wp_next_scheduled('stm_cron_hook'), 'stm_cron_hook' );
}