<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


/**
 * Get filter configuration
 *
 * @param array $args
 *
 * @return array
 */
function stm_listings_attributes($args = array())
{
    $args = wp_parse_args($args, array(
        'where' => array(),
        'key_by' => ''
    ));

    $result = array();
    $data = array_filter((array)get_option('stm_vehicle_listing_options'));

    foreach ($data as $key => $_data) {
        $passed = true;
        foreach ($args['where'] as $_field => $_val) {
            if (array_key_exists($_field, $_data) && $_data[$_field] != $_val) {
                $passed = false;
                break;
            }
        }

        if ($passed) {
            if ($args['key_by']) {
                $result[$_data[$args['key_by']]] = $_data;
            } else {
                $result[] = $_data;
            }
        }
    }

    return apply_filters('stm_listings_attributes', $result, $args);
}

/**
 * Get single attribute configuration by taxonomy slug
 *
 * @param $taxonomy
 *
 * @return array|mixed
 */
function stm_listings_attribute($taxonomy)
{
    $attributes = stm_listings_attributes(array('key_by' => 'slug'));
    if (array_key_exists($taxonomy, $attributes)) {
        return $attributes[$taxonomy];
    }

    return array();
}

/**
 * Get all terms grouped by taxonomy for the filter
 *
 * @return array
 */
function stm_listings_filter_terms()
{
    static $terms;

    if (isset($terms)) {
        return $terms;
    }

    $filters = stm_listings_attributes(array('where' => array('use_on_car_filter' => true), 'key_by' => 'slug'));

    $numeric = array_keys(stm_listings_attributes(array(
        'where' => array(
            'use_on_car_filter' => true,
            'numeric' => true
        ),
        'key_by' => 'slug'
    )));
    $_terms = get_terms(array(
        'taxonomy' => $numeric,
        'hide_empty' => false,
        'update_term_meta_cache' => false,
    ));

    $taxes = array_diff(array_keys($filters), $numeric);
    $taxes = apply_filters('stm_listings_filter_taxonomies', $taxes);

    $_terms = array_merge($_terms, get_terms(array(
        'taxonomy' => $taxes,
        'hide_empty' => false,
        'update_term_meta_cache' => false,
    )));

    $terms = array();

    foreach ($taxes as $tax) {
        $terms[$tax] = array();
    }

    foreach ($_terms as $_term) {
        $terms[$_term->taxonomy][$_term->slug] = $_term;
    }

    $terms = apply_filters('stm_listings_filter_terms', $terms);

    return $terms;
}

/**
 * Drop-down options grouped by attribute for the filter
 *
 * @return array
 */
function stm_listings_filter_options()
{
    static $options;

    if (isset($options)) {
        return $options;
    }

    $filters = stm_listings_attributes(array('where' => array('use_on_car_filter' => true), 'key_by' => 'slug'));
    $terms = stm_listings_filter_terms();
    $options = array();

    foreach ($terms as $tax => $_terms) {
        $_filter = isset($filters[$tax]) ? $filters[$tax] : array();
        $options[$tax] = _stm_listings_filter_attribute_options($tax, $_terms);

        if (empty($_filter['numeric'])) {
            $_remaining = stm_listings_options_remaining($terms[$tax], stm_listings_query());

			foreach ($_terms as $_term) {
				if (isset($_remaining[$_term->term_taxonomy_id])) {
					$options[$tax][$_term->slug]['count'] = (int) $_remaining[$_term->term_taxonomy_id];
				}
				else {
					$options[$tax][$_term->slug]['count'] = 0;
					$options[$tax][$_term->slug]['disabled'] = true;
				}
			}
        }
    }

    $options = apply_filters('stm_listings_filter_options', $options);

    return $options;
}

/**
 * Get list of attribute options filtered by query
 *
 * @param array $terms
 * @param WP_Query $from
 *
 * @return array
 */
function stm_listings_options_remaining($terms, $from = null)
{
    /** @var WP_Query $from */
    $from = is_null($from) ? $GLOBALS['wp_query'] : $from;

    if (empty($terms) || (!count($from->get('meta_query', array())) && !count($from->get('tax_query')))) {
        return array();
    }

    global $wpdb;
    $meta_query = new WP_Meta_Query($from->get('meta_query', array()));
    $tax_query = new WP_Tax_Query($from->get('tax_query', array()));
    $meta_query_sql = $meta_query->get_sql('post', $wpdb->posts, 'ID');
    $tax_query_sql = $tax_query->get_sql($wpdb->posts, 'ID');
    $term_ids = wp_list_pluck($terms, 'term_taxonomy_id');
    $post_type = $from->get('post_type');

    // Generate query
    $query = array();
    $query['select'] = "SELECT term_taxonomy.term_taxonomy_id, COUNT( {$wpdb->posts}.ID ) as count";
    $query['from'] = "FROM {$wpdb->posts}";
    $query['join'] = "INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id";
    $query['join'] .= "\nINNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )";
    //$query['join'] .= "\nINNER JOIN {$wpdb->terms} AS terms USING( term_id )";
    $query['join'] .= "\n" . $tax_query_sql['join'] . $meta_query_sql['join'];
    $query['where'] = "WHERE {$wpdb->posts}.post_type IN ( '{$post_type}' ) AND {$wpdb->posts}.post_status = 'publish' ";
    $query['where'] .= "\n" . $tax_query_sql['where'] . $meta_query_sql['where'];
    $query['where'] .= "\nAND term_taxonomy.term_taxonomy_id IN (" . implode(',', array_map('absint', $term_ids)) . ")";
    $query['group_by'] = "GROUP BY term_taxonomy.term_taxonomy_id";

    $query = apply_filters('stm_listings_options_remaining_query', $query);
    $query = join("\n", $query);

    $results = $wpdb->get_results($query);
    $results = wp_list_pluck($results, 'count', 'term_taxonomy_id');
    return $results;

//    $terms = wp_list_pluck($terms, 'slug', 'term_taxonomy_id');
//    $remaining = array_intersect_key($terms, $results);
//    $remaining = array_flip($remaining);
//
//    return $remaining;
}

/**
 * Filter configuration array
 *
 * @return array
 */
function stm_listings_filter()
{
    $query = stm_listings_query();
    $total = $query->found_posts;
    $filters = stm_listings_attributes(array('where' => array('use_on_car_filter' => true), 'key_by' => 'slug'));
    $options = stm_listings_filter_options();
    $terms = stm_listings_filter_terms();
    $url = stm_get_listing_archive_link( array_diff_key( $_GET, array_flip( array( 'ajax_action', 'fragments' ) ) ) );

    return apply_filters( 'stm_listings_filter', compact( 'options', 'filters', 'total', 'url' ), $terms );
}

/**
 * Retrieve input data from $_POST, $_GET by path
 *
 * @param $path
 * @param $default
 *
 * @return mixed
 */
function stm_listings_input($path, $default = null)
{

    if (trim($path, '.') == '') {
        return $default;
    }

    foreach (array($_POST, $_GET) as $source) {
        $value = $source;
        foreach (explode('.', $path) as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                $value = null;
                break;
            }

            $value = &$value[$key];
        }

        if (!is_null($value)) {
            return $value;
        }
    }

    return $default;
}

/**
 * Current URL with native WP query string parameters ()
 *
 * @return string
 */
function stm_listings_current_url()
{
    global $wp, $wp_rewrite;

    $url = preg_replace("/\/page\/\d+/", '', $wp->request);
    $url = home_url($url . '/');
    if (!$wp_rewrite->permalink_structure) {
        parse_str($wp->query_string, $query_string);

        $leave = array('post_type', 'pagename', 'page_id', 'p');
        $query_string = array_intersect_key($query_string, array_flip($leave));

        $url = trim(add_query_arg($query_string, $url), '&');
        $url = str_replace('&&', '&', $url);
    }

    return apply_filters( 'stm_listings_current_url', $url );
}

function _stm_listings_filter_attribute_options($taxonomy, $_terms)
{

    $attribute = stm_listings_attribute($taxonomy);
    $attribute = wp_parse_args($attribute, array(
        'slug' => $taxonomy,
        'single_name' => '',
        'numeric' => false,
        'slider' => false,
    ));

    $options = array();

    if (!$attribute['numeric']) {


        $options[''] = array(
            'label' => apply_filters('stm_listings_default_tax_name', $attribute['single_name']),
            'selected' => stm_listings_input($attribute['slug']) == null,
            'disabled' => false,
        );

        foreach ($_terms as $_term) {
            $options[$_term->slug] = array(
                'label' => $_term->name,
                'selected' => stm_listings_input($attribute['slug']) == $_term->slug,
                'disabled' => false,
                'count' => $_term->count,
            );
        }
    } else {
        $numbers = array();
        foreach ($_terms as $_term) {
            $numbers[intval($_term->slug)] = $_term->name;
        }
        ksort($numbers);

        if (!empty($attribute['slider'])) {
            foreach ($numbers as $_number => $_label) {
                $options[$_number] = array(
                    'label' => $_label,
                    'selected' => stm_listings_input($attribute['slug']) == $_label,
                    'disabled' => false,
                );
            }
        } else {

            $options[''] = array(
                'label' => sprintf(__('Max %s', 'stm_vehicles_listing'), $attribute['single_name']),
                'selected' => stm_listings_input($attribute['slug']) == null,
                'disabled' => false,
            );

            $_prev = null;
            $_affix = empty($attribute['affix']) ? '' : __($attribute['affix'], 'stm_vehicles_listing');

            foreach ($numbers as $_number => $_label) {

                if ($_prev === null) {
                    $_value = '<' . $_number;
                    $_label = '< ' . $_label . ' ' . $_affix;
                } else {
                    $_value = $_prev . '-' . $_number;
                    $_label = $_prev . '-' . $_label . ' ' . $_affix;
                }

                $options[$_value] = array(
                    'label' => $_label,
                    'selected' => stm_listings_input($attribute['slug']) == $_value,
                    'disabled' => false,
                );

                $_prev = $_number;
            }

            if ($_prev) {
                $_value = '>' . $_prev;
                $options[$_value] = array(
                    'label' => '>' . $_prev . ' ' . $_affix,
                    'selected' => stm_listings_input($attribute['slug']) == $_value,
                    'disabled' => false,
                );
            }
        }
    }

    return $options;
}

if (!function_exists('stm_listings_user_defined_filter_page')) {
    function stm_listings_user_defined_filter_page()
    {
        return apply_filters('stm_listings_inventory_page_id', get_theme_mod('listing_archive', false));
    }
}

function stm_listings_paged_var()
{
    global $wp;

    $paged = null;

    if (isset($wp->query_vars['paged'])) {
        $paged = $wp->query_vars['paged'];
    } elseif (isset($_GET['paged'])) {
        $paged = sanitize_text_field($_GET['paged']);
    }

    return $paged;
}

/**
 * Listings post type identifier
 *
 * @return string
 */
if (!function_exists('stm_listings_post_type')) {
    function stm_listings_post_type()
    {
        return apply_filters('stm_listings_post_type', 'listings');
    }
}

add_action('init', 'stm_listings_init', 1);

function stm_listings_init()
{

    $options = get_option('stm_post_types_options');

    $stm_vehicle_options = wp_parse_args($options, array(
        'listings' => array(
            'title' => __('Listings', 'stm_vehicles_listing'),
            'plural_title' => __('Listings', 'stm_vehicles_listing'),
            'rewrite' => 'listings'
        ),
    ));

    register_post_type(stm_listings_post_type(), array(
        'labels' => array(
            'name' => $stm_vehicle_options['listings']['plural_title'],
            'singular_name' => $stm_vehicle_options['listings']['title'],
            'add_new' => __('Add New', 'stm_vehicles_listing'),
            'add_new_item' => __('Add New Item', 'stm_vehicles_listing'),
            'edit_item' => __('Edit Item', 'stm_vehicles_listing'),
            'new_item' => __('New Item', 'stm_vehicles_listing'),
            'all_items' => __('All Items', 'stm_vehicles_listing'),
            'view_item' => __('View Item', 'stm_vehicles_listing'),
            'search_items' => __('Search Items', 'stm_vehicles_listing'),
            'not_found' => __('No items found', 'stm_vehicles_listing'),
            'not_found_in_trash' => __('No items found in Trash', 'stm_vehicles_listing'),
            'parent_item_colon' => '',
            'menu_name' => __($stm_vehicle_options['listings']['plural_title'], 'stm_vehicles_listing'),
        ),
        'menu_icon' => 'dashicons-location-alt',
        'show_in_nav_menus' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'comments', 'excerpt', 'author'),
        'rewrite' => array('slug' => $stm_vehicle_options['listings']['rewrite']),
        'has_archive' => true,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'hierarchical' => false,
        'menu_position' => null,
    ));

}

add_filter('get_pagenum_link', 'stm_listings_get_pagenum_link');

function stm_listings_get_pagenum_link($link)
{
    return remove_query_arg('ajax_action', $link);
}

/*Functions*/
function stm_check_motors()
{
    return apply_filters('stm_listing_is_motors_theme', false);
}

require_once 'templates.php';
require_once 'enqueue.php';
require_once 'vehicle_functions.php';

add_action('init', 'stm_listings_include_customizer');

function stm_listings_include_customizer()
{
    if (!stm_check_motors()) {
        require_once 'customizer/customizer.class.php';
    }
}

function stm_listings_search_inventory()
{
    return apply_filters('stm_listings_default_search_inventory', false);
}

$solfa_api_key = get_theme_mod('solfa_api_key', '' );
$solfa_secret = get_theme_mod( 'solfa_secret_key', '' );

function  fetchCurlD($url, $headers ){


  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL             => $url,
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_ENCODING        => "",
    CURLOPT_MAXREDIRS       => 10,
    CURLOPT_SSL_VERIFYPEER  => false,
    CURLOPT_SSL_VERIFYHOST  => false,
    CURLOPT_TIMEOUT         => 30,
    CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST   => "GET",
    CURLOPT_HTTPHEADER      => $headers,
  ));

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    // echo "cURL Error #:" . $err;
    return false;
  } else {
    return json_decode($response, true);
  }
}

function getDateHmac($secret, $data){
  return hash_hmac('SHA256', $data, $secret, false);
}


function getCount($url, $headers){
  $url .= 'count';
  $data = fetchCurlD($url, $headers);
  if($data && isset($data['VehicleCount']))
  {
    return $data['VehicleCount'];
  }

  return false;
}

function getBodies(){
	global $solfa_api_key;
	global $solfa_secret;
	echo "The api key is: ".$solfa_api_key;
  $url = 'https://showcase.vehicleversion.com/v1.0/vehicle/read/body';
  //$secret = get_field('api_secret', 'option');
  $secret = $solfa_secret;
  $date = gmdate('D, d M Y H:i:s T');
  $headers = array();
 // $headers[] = 'x-apikey: '.get_field('api_key', 'option');
 $headers[] = 'x-apikey: '.$solfa_api_key;
  $headers[] = "x-apihmac: ".getDateHmac($secret, $date);
  $headers[] = "x-apidate: ".$date;
  $headers[] = 'DealerId: 86';
  $data = fetchCurlD($url, $headers);
  if($data)
 
  {
	  $body_array = array();
	  foreach ($data as $key) {
	  $body_array[$key["Body_Id"]] = $key["Body_Name"];
		}
    return $body_array;
  }

  return false;
}

function fetch_vehicle_info($url, $headers, $i){
  $url .= 'get';
  $headers[] = 'Index: '.$i;
  $data = fetchCurlD($url, $headers);
  if($data)
    return $data;
  // print_r($data);
  // echo '<hr>';
  // if($data && isset($data['VehicleCount']))
  // {
  //   return $data['VehicleCount'];
  // }

  return false;
}
function fetch_vehicles_api(){
	global $solfa_api_key;
	global $solfa_secret;
  $url = 'https://showcase.vehicleversion.com/v1.0/vehicle/read/';
  //$secret = get_field('api_secret', 'option');
  $secret = $solfa_secret;
  $date = gmdate('D, d M Y H:i:s T');
  $headers = array();
 // $headers[] = 'x-apikey: '.get_field('api_key', 'option');
 $headers[] = 'x-apikey: '.$solfa_api_key;
  $headers[] = "x-apihmac: ".getDateHmac($secret, $date);
  $headers[] = "x-apidate: ".$date;
  $headers[] = 'DealerId: 86';

  // print_r($headers);
  $count = getCount($url, $headers);
  $vehicles = array();
  if($count){
    //echo $count;
    for($i = 1;  $i  <= $count ; $i++){
      $tmp = fetch_vehicle_info($url, $headers, $i);
      if($tmp){
        $vehicles[] = $tmp;
      }
    }
    //echo count($vehicles);
    return $vehicles;
  }else{
    echo 'No vehicles available';
    return false;
  }
}
function delete_all_posts_beforehand(){
    $k = new WP_Query(array('post_type'=>'listings', 'posts_per_page'=>-1));
    while($k->have_posts())
    {
        $k->the_post();
        $pid = get_the_id();
        $attachments = get_attached_media( 'image', $pid );
         if($attachments)
        {
	      foreach ($attachments as $attachment){
          wp_delete_attachment($attachment->ID, true);
          }
        }
        wp_delete_post($pid, true);
    }
    wp_reset_query();

}

function init_import_invetory2() {
//   if ( !current_user_can( 'manage_options' ) )  {
//     wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
//   }
ini_set('max_execution_time', 300);
  $show_msg = false;


        // required libraries for media_sideload_image
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

  if( isset($_GET['upload_vehicles'])   )
  {

   // $show_msg = true;


    $csv = fetch_vehicles_api();
    if(!$csv){
      $csv = [];
    }

    $counts = array( 'added' => 0 , 'updated' => 0 ,  'skipped' => 0 );



    delete_all_posts_beforehand();


	$body_array = getBodies();
	
	$cars = 0;
    foreach($csv as $row){
	    //if ($cars > 5) break;
	    echo "Row<br><pre>";
	    print_r($row);
	    echo "</pre>";
      $ttl = '';
      if($row['NewUsed']){
        $ttl .=ucwords($row['NewUsed']).' ';
        $row['NewUsed'] = strtolower($row['NewUsed']);
        $type_id = term_exists( $row['NewUsed'], 'condition' );
        if($type_id){
          $condition_ids = array( $type_id['term_id'] );
        }else{
          $type_id =   wp_insert_term(ucwords($row['NewUsed']), 'condition');
          if($type_id){
            $condition_ids = array( $type_id['term_id'] );
          }else{
            $condition_ids = array();
          }
        }
      }

      if($row['Year']){
        $ttl .=$row['Year'].' ';
        $row['Year'] = strtolower($row['Year']);
        $year_obj = term_exists( $row['Year'], 'ca-year' );
        if($year_obj){
          $year_ids = array( $year_obj['term_id'] );
        }else{
          $year_obj =   wp_insert_term($row['Year'], 'ca-year');
          if($year_obj){
           //    print_r($year_obj);
            $year_ids = array( $year_obj['term_id'] );
          }else{
            $year_ids = array();
          }
        }
      }
      if($row['Body_Id']){
        $t = $row['Body_Id'];
        $row['Body_Id'] = $body_array[$row['Body_Id']];
        $row['Body_Id'] = strtolower($row['Body_Id']);
        $body_obj = term_exists( $row['Body_Id'], 'body' );
        if($body_obj){
            $body_ids = array( $body_obj['term_id'] );
        }
        else{
            $body_obj =   wp_insert_term(ucwords($row['Body_Id']), 'body');
            if($body_obj){
                $body_ids = array( $body_obj['term_id'] );
            }
            else{
                $body_ids = array();
            }
        }
      }
      
      if($row['Body_Style']){
        $row['Body_Style'] = strtolower($row['Body_Style']);
        $body_type_obj = term_exists( $row['Body_Style'], 'body-type' );
        if($body_type_obj){
            $body_type_ids = array( $body_type_obj['term_id'] );
        }
        else{
            $body_type_obj =   wp_insert_term(ucwords($row['Body_Style']), 'body-type');
            if($body_type_obj){
                $body_type_ids = array( $body_type_obj['term_id'] );
            }
            else{
                $body_type_ids = array();
            }
        }
      }



      if($row['Ext_Color']){
	    $row['Ext_Color'] = strtolower($row['Ext_Color']);
        $ext_color = term_exists( $row['Ext_Color'], 'exterior-color' );
        if($ext_color){
            $ext_color_ids = array( $ext_color['term_id'] );
        }
        else{
            $ext_color =   wp_insert_term(ucwords($row['Ext_Color']), 'exterior-color');
            if($ext_color){
                $ext_color_ids = array( $ext_color['term_id'] );
            }
            else{
                $ext_color_ids = array();
            }
        }
      }
      
      if($row['Int_Color']){
	    $row['Int_Color'] = strtolower($row['Int_Color']);
        $int_color = term_exists( $row['Int_Color'], 'interior-color' );
        if($int_color){
            $int_color_ids = array( $int_color['term_id'] );
        }
        else{
            $int_color =   wp_insert_term(ucwords($row['Int_Color']), 'interior-color');
            print_r($int_color);
            if($int_color){
                $int_color_ids = array( $int_color['term_id'] );
            }
            else{
                $int_color_ids = array();
            }
        }
      }
      
      
      if($row['Make']){
        $ttl .=ucwords($row['Make']).' ';
        $row['Make'] = strtolower($row['Make']);
        $make_id = term_exists( $row['Make'], 'make' );
        if($make_id){
          $make_ids = array( $make_id['term_id'] );
        }
        else{
          $make_id =   wp_insert_term(ucwords($row['Make']), 'make');
          if($make_id){
            // print_r($make_id);
            $make_ids = array( $make_id['term_id'] );
          }else{
            $make_ids = array();
          }
        }
      }
      if($row['Model']){
          $row['Model'] = strtolower($row['Model']);
        $ttl .=ucwords($row['Model']);
        $model_id = term_exists( $row['Model'], 'serie' );
        if($model_id){
          $model_ids = array( $model_id['term_id'] );
        }
        else{
          $model_id =   wp_insert_term(ucwords($row['Model']), 'serie');
          if($model_id){
            // print_r($make_id);
            $model_ids = array( $model_id['term_id'] );
          }else{
            $model_ids = array();
          }
        }
      }
      if($row['Fuel']){
          $row['Fuel'] = strtolower($row['Fuel']);
        $fuel_id = term_exists( $row['Fuel'], 'fuel' );
        if($fuel_id){
          $fuel_ids = array( $fuel_id['term_id'] );
        }
        else{
          $fuel_id =   wp_insert_term(ucwords($row['Fuel']), 'fuel');
          if($fuel_id){
            // print_r($make_id);
            $fuel_ids = array( $fuel_id['term_id'] );
          }else{
            $fuel_id = array();
          }
        }
      }
      if($row['Selling_Price']){
          $selling_price = strtolower($row['Selling_Price']);
          $selling_price = str_replace("$", "", $selling_price);
        $selling_price = str_replace(",", "", $selling_price);
       // $ttl .=ucwords($row['Selling_Price']);
        $price_id = term_exists( $selling_price, 'price' );
        if($price_id){
          $price_ids = array( $price_id['term_id'] );
        }
        else{
          $price_id =   wp_insert_term($selling_price, 'price');
          if($price_id){
            // print_r($make_id);
            $price_ids = array( $price_id['term_id'] );
          }else{
            $price_ids = array();
          }
        }
      }
	  if($row['Odometer']){
          $row['Odometer'] = strtolower($row['Odometer']);
       // $ttl .=$row['Model'];
        $mileage_id = term_exists( $row['Odometer'], 'mileage' );
        if($mileage_id){
          $mileage_ids = array( $mileage_id['term_id'] );
        }
        else{
          $mileage_id =   wp_insert_term($row['Odometer'], 'mileage');
          if($mileage_id){
            // print_r($make_id);
            $mileage_ids = array( $mileage_id['term_id'] );
          }else{
            $mileage_ids = array();
          }
        }
      }
	  if($row['TransType']){
          $row['TransType'] = strtolower($row['TransType']);
       // $ttl .=$row['Model'];
        $trans_id = term_exists( $row['TransType'], 'transmission' );
        if($trans_id){
          $trans_ids = array( $trans_id['term_id'] );
        }
        else{
          $trans_id =   wp_insert_term(ucwords($row['TransType']), 'transmission');
          if($trans_id){
            // print_r($make_id);
            $trans_ids = array( $trans_id['term_id'] );
          }else{
            $trans_ids = array();
          }
        }
      }
	  
	  if($row['DriveTrain']){
          $row['DriveTrain'] = strtolower($row['DriveTrain']);
       // $ttl .=$row['Model'];
        $drive_id = term_exists( $row['DriveTrain'], 'drive' );
        if($drive_id){
          $drive_ids = array( $drive_id['term_id'] );
        }
        else{
          $drive_id =   wp_insert_term(strtoupper($row['DriveTrain']), 'drive');
          if($drive_id){
            // print_r($make_id);
            $drive_ids = array( $drive_id['term_id'] );
          }else{
            $drive_ids = array();
          }
        }
      }
	  
	  if($row['Options']){
       $options = explode("|", $row['Options']);
       $options_ids = array();
       foreach ($options as $option){
        $option_id = term_exists( $option, 'stm_additional_features' );
        if($option_id){
          $options_ids[] = $option_id['term_id'] ;
        }
        else{
          $option_id =   wp_insert_term(ucwords($option), 'stm_additional_features');
          if($option_id){
            // print_r($make_id);
            $options_ids[] = $option_id['term_id'] ;
          }
        }
        }
      }

	  
      $my_post = array(
        'post_title'    => $ttl,
        'post_name'		=> sanitize_title_with_dashes($ttl.' '.$row['Selling_Price'].' '.$row['Vin'],'','save'),
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_type'     => 'listings',
        'tax_input'     => array(
          'make'         => $make_ids,
          'condition'    => $condition_ids,
          'ca-year'    => $row['Year'],
          'body'       => $body_ids,
          'body-type'	=> $body_type_ids,
          'mileage'       => $row['Odometer'],
          'serie'		=> $model_ids,
          'exterior-color'		=> $ext_color_ids,
          'interior-color'		=> $int_color_ids,
          'price'			=> $selling_price,
          'transmission'	=> $trans_ids,
          'drive'		=> $drive_ids,
          'fuel'	=> $fuel_ids,
          'stm_additional_features'		=> implode( ',', $options )
        ),
      );
      
      echo "<pre>";
	  print_r($my_post);
	  echo "</pre>";
	  $row = array_map('strtolower', $row);
      $m = wp_insert_post($my_post );
     /* wp_set_object_terms( $m, $make_ids, 'make' );
      wp_set_object_terms( $m, $condition_ids, 'condition' );
      wp_set_object_terms( $m, $year_ids, 'ca-year' );
      wp_set_object_terms( $m, $body_ids, 'body' );
      wp_set_object_terms( $m, $mileage_ids, 'mileage' );
      wp_set_object_terms( $m, $model_ids, 'serie' );
      wp_set_object_terms( $m, $ext_color_ids, 'exterior-color' );
      wp_set_object_terms( $m, $int_color_ids, 'interior-color' );
      wp_set_object_terms( $m, $price_ids, 'price' );
      wp_set_object_terms( $m, $trans_ids, 'transmission' );
      wp_set_object_terms( $m, $drive_ids, 'drive' );
      wp_set_object_terms( $m, $fuel_ids, 'fuel' );
      wp_set_object_terms( $m, $options_ids, 'stm_additional_features' );*/
      $row['Image_Medium'] = implode("|", array_slice(explode("|", $row['Image_Medium']), 0, 9));
      if( $m ) {


        $arr_meta = array(
          'stock_number'                   =>  ( ( !empty( $row['Stock_Num'] ) ) ? $row[ 'Stock_Num' ]  : '' ),
          'vin_number'                   =>  ( ( !empty( $row['Vin'] ) ) ? strtoupper($row[ 'Vin' ])   : '' ),
          'serie'                   => ((!empty($row['Model']))? sanitize_title_with_dashes($row[ 'Model']) :''),
          'condition'                   => ((!empty($row['NewUsed']))? $row[ 'NewUsed'] :''),
           'make'                   => ((!empty($row['Make']))? $row[ 'Make'] :''),
          'ca-year'                   => ((!empty($row['Year']))? $row[ 'Year'] :''),
          'body'                    => ((!empty($row['Body_Id']))? sanitize_title_with_dashes($row['Body_Id']) :''),
          'body-type'                    => ((!empty($row['Body_Style']))? sanitize_title_with_dashes($row['Body_Style']) :''),
          // 'cabtype'                 => ((!empty($row['CabType']))? $row[ 'CabType'] :''),
          // 'trim'                    => ((!empty($row['Trim']))? $row[ 'Trim'] :''),
          // 'modelnumber'             => ((!empty($row['ModelNumber']))? $row[ 'ModelNumber'] :''),
          // 'doors'                   => ((!empty($row['Doors']))? $row[ 'Doors'] :''),
          'exterior-color'           => ((!empty($row['Ext_Color']))? $row[ 'Ext_Color'] :''),
          'interior-color'           => ((!empty($row['Int_Color']))? $row[ 'Int_Color'] :''),
          'enginecylinders'         => ((!empty($row['Cylinders']))? $row[ 'Cylinders'] :''),
          'enginedisplacement'      => ((!empty($row['Displacement']))? $row[ 'Displacement'] :''),
          'transmission'            => ((!empty($row['TransType']))? $row[ 'TransType'] :''),
          'mileage'                   => ((!empty($row['Odometer']))? $row[ 'Odometer'] :''),
          'price'            => ((!empty($row['Selling_Price']))? $row[ 'Selling_Price'] :''),
          'stm_genuine_price'            => ((!empty($selling_price))? $selling_price :''),
          'msrp'                    => ((!empty($row['MSRP']))? $row[ 'MSRP'] :''),
          'internetprice'           => ((!empty($row['InternetPrice']))? $row[ 'InternetPrice'] :''),
          'bookvalue'               => ((!empty($row['Book_Value']))? $row[ 'Book_Value'] :''),
          'invoice'                 => ((!empty($row['Invoice']))? $row[ 'Invoice'] :''),
      //    'certified'               => ((!empty($row['Certified']))? $row[ 'Certified'] :''),
          'dateinstock'             => ((!empty($row['DateInStock']))? $row[ 'DateInStock'] :''),
          'description'             => ((!empty($row['Description']))? $row[ 'Description'] :''),
          //'options'                 => ((!empty($row['Options']))? $row[ 'Options'] :''),
          // 'categorized_options'     => ((!empty($row['Categorized Options'] ) ) ?  $row[ 'Categorized Options'] :''),
          // 'engineblocktype'         => ((!empty($row['EngineBlockType']))? $row[ 'EngineBlockType'] :''),
          // 'enginaspirationtype'     => ((!empty($row['EnginAspirationType']))? $row[ 'EnginAspirationType'] :''),
          // 'enginedescription'       => ((!empty($row['EngineDescription']))? $row[ 'EngineDescription'] :''),
          'transmissionaspeed'      => ((isset($row['TransASpeed']) && !empty($row['TransASpeed']))? $row[ 'TransASpeed'] :''),
          'transmissiondescription' => ((!empty($row['Trans_Descript']))? $row[ 'Trans_Descript'] :''),
          'drive'              => ((!empty($row['DriveTrain']))? $row[ 'DriveTrain']  :''),
          'fuel'                => ((!empty($row['Fuel']))? $row[ 'Fuel'] :''),
          'city_mpg'                 => ((!empty($row['MPGCity']))? $row[ 'MPGCity'] :''),
          'highway_mpg'              => ((!empty($row['MPGHwy']))? $row[ 'MPGHwy'] :''),
          'wheelbasecode'           => ((!empty($row['WheelBase']))? $row[ 'WheelBase'] :''),
          // 'packagecodes'            => ((!empty($row['PackageCodes']))? $row[ 'PackageCodes'] :''),
          'additional_features'       => ((!empty($row['Options']))? ucwords(str_replace("|", ",", $row[ 'Options']),', ')  :''),
          'stm_car_user'			=>	'3',
          'title'					=> 'hide',
          'images'                  => ((!empty($row['Image_Medium']))? $row[ 'Image_Medium'] :''),
          'stm_car_location'		=> ((!empty($row['DealerStreet']))? ucwords($row[ 'DealerStreet']) .", ".ucwords($row[ 'DealerCity']).", ".strtoupper($row[ 'DealerState']).", United States" :''),
          'stm_lng_car_admin'		=> '-91.38642600000003',
          'stm_lat_car_admin'		=> '44.9275779'
        );

        $arr_meta['price'] = str_replace("$", "", $arr_meta['price']);
        $arr_meta['price'] = str_replace(",", "", $arr_meta['price']);
      /*  if($arr_meta['images']!=''){
          $arr_meta['images'] = explode('|', $arr_meta['images']);
          $featured_image = array_shift($arr_meta['images']);
          $result = media_sideload_image($featured_image, $m, '','id' );
          set_post_thumbnail($m, $result);
        }*/
        if($row['Image_Medium']!=''){
          $row['Image_Medium'] = explode('|', $row['Image_Medium']);
           echo "<pre>Images before<br/>";
	  print_r($row['Image_Medium']);
	  echo "</pre>";
          $featured_image = array_shift($row['Image_Medium']);
          $result = media_sideload_image($featured_image, $m, '','id' );
          set_post_thumbnail($m, $result);
       /*   $ids = array();
          //$attachments = get_posts(array('numberposts' => '1', 'post_parent' => $m, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC'));
          //if(sizeof($attachments) > 0){
            
            //$ids[] = $result;
         // }
         echo "<pre>Images after<br/>";
	  print_r($row['Image_Medium']);
	  echo "</pre>";
         $img_count = 0;
          foreach ($row['Image_Medium'] as $images){
	          if ($img_count>7) break;
	          $image_id = media_sideload_image($images, $m, '','id');
	          $ids[] = $image_id;
	          $img_count++;
          }
          add_post_meta($m, 'gallery', $ids);  */
        }


        $counts[ 'added' ]++;
        echo "<pre>";
	  print_r($arr_meta);
	  echo "</pre>";
        foreach($arr_meta as $meta_k => $meta_v  ){
          if($meta_v  != '')
           if(is_array($meta_v)){
              update_post_meta($m, $meta_k,  $meta_v );
          }else{
            update_post_meta($m, $meta_k, trim($meta_v));
          }

        }

      }else{
          
        $counts[ 'skipped' ]++;
      }

    
    $cars++;
    }
    
  }



}

add_action('init', 'api_vehicle_import');
add_action('init', 'delete_all_vehicles');
function api_vehicle_import(){
    if( isset($_GET['upload_vehicles'])   )
    {
        init_import_invetory2();
        wp_die();
    }

}
function delete_all_vehicles(){
    if( isset($_GET['delete_vehicles'])   )
    {
        delete_all_posts_beforehand();
        wp_die();
    }

}