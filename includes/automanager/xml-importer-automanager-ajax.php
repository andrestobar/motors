<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// Step one
add_action( 'wp_ajax_stm_ajax_file_automanager_upload', 'stm_ajax_file_automanager_upload' );

function stm_ajax_file_automanager_upload() {
	$response = array();
	$response['errors'] = array();
	$response['url'] = '';
		
	if(empty($_POST['url'])) {
		$response['errors']['url'] = true;
	} else {
		if(filter_var($_POST['url'], FILTER_VALIDATE_URL) === FALSE) {
			$response['errors']['url'] = true;
		} else {
			$response['url'] = esc_url($_POST['url']);
		}
	}
	
	// Parse from url
	if(!empty($response['url'])) {
		$filter_taxes = stm_get_taxonomies();
		if(!empty($filter_taxes)) {
			$response['filter'] = $filter_taxes;
		}
		
		$response['xml'] =  json_decode(json_encode(simplexml_load_file($response['url'], 'SimpleXMLElement', LIBXML_NOCDATA)), TRUE);
		
		update_option('stm_xml_url_tmp', $response['url']);
	}
	
	$response = json_encode( $response );

	echo $response;
	exit;
}

// Step two
add_action( 'wp_ajax_stm_ajax_automanager_save_associations', 'stm_ajax_automanager_save_associations' );

function stm_ajax_automanager_save_associations() {
	$response = array();
	$response['errors'] = array();
	
	if(empty($_POST['title'])) {
		$response['errors']['title'] = true;
	}
	
	if(empty($_POST['content'])) {
		$response['errors']['content'] = true;
	}
	
	if(empty($response['errors'])) {
		$associations = array_diff_key( $_POST, array_flip( array('action') ) );
		
		if(!empty($associations)) {
			update_option('stm_xml_associations_tmp', $associations);
		}
	}
	
	$response = json_encode( $response );

	echo $response;
	exit;
}




// Step three
add_action( 'wp_ajax_stm_ajax_automanager_save_template', 'stm_ajax_automanager_save_template' );

function stm_ajax_automanager_save_template() {
	$response = array();
	$response['errors'] = array();
	
	if(empty($_POST['template_name'])){
		$response['errors']['template_name'] = true;
	}
	
	if(empty($response['errors'])) {
		$settings = array_diff_key( $_POST, array_flip( array('action') ) );
		
		if(!empty($settings)) {
			$url = get_option('stm_xml_url_tmp');
			$associations = get_option('stm_xml_associations_tmp');
			
			$template_name = sanitize_title($_POST['template_name']);
			
			$template = array(
				'name' => sanitize_text_field($_POST['template_name']),
				'template_name' => $template_name,
				'url'  => $url,
				'associations' => $associations,
				'settings' => $settings
			);
			
			$templates = get_option('stm_xml_templates');
			
			if(empty($templates)) {
				$templates = array();	
			}
			
			$templates = array();
			
			$templates[$template_name] = $template;
			$response['iframe_url'] = get_site_url().'/wp-admin/edit.php?post_type=listings&page=stm_xml_import_automanager&stm_xml_do_import_automanager=1';
			
			if(!empty($_POST['run_import_now'])) {
				$response['run_import_now'] = true;
			}
			
			update_option('stm_current_template', $template_name);
			update_option('stm_xml_templates', $templates);

			
		}
	}
	
	$response = json_encode( $response );

	echo $response;
	exit;
}