<?php
/**
Plugin Name: SOLFA Listings (modified from Motors - Classified Listing)

Description: Manage classified listings from the WordPress admin panel, and allow users to post classified listings directly to your website.
Author: Andres Tobar

License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: stm_vehicles_listing
Version: 5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'STM_LISTINGS_PATH', dirname( __FILE__ ) );
define( 'STM_LISTINGS_URL', plugins_url( '', __FILE__ ) );
define( 'STM_LISTINGS', 'stm_vehicles_listing' );

define( 'STM_LISTINGS_IMAGES', STM_LISTINGS_URL . '/includes/admin/butterbean/images/' );

if ( ! is_textdomain_loaded( 'stm_vehicles_listing' ) ) {
	load_plugin_textdomain( 'stm_vehicles_listing', false, 'stm_vehicles_listing/languages' );
}

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/query.php';
require_once __DIR__ . '/includes/options.php';
require_once __DIR__ . '/includes/actions.php';
require_once __DIR__ . '/includes/fix-image-orientation.php';

if ( is_admin() ) {
    require_once __DIR__ . '/includes/admin/categories.php';
    require_once __DIR__ . '/includes/admin/enqueue.php';
    require_once __DIR__ . '/includes/admin/butterbean_metaboxes.php';
    require_once __DIR__ . '/includes/admin/category-image.php';
	require_once __DIR__ . '/includes/automanager/xml-importer-automanager.php';
	require_once __DIR__ . '/includes/automanager/xml-importer-automanager-ajax.php';
	require_once __DIR__ . '/includes/automanager/xml-importer-automanager-iframe.php';
	require_once __DIR__ . '/includes/automanager/xml-importer-automanager-cron.php';

    /*For plugin only*/
    //require_once __DIR__ . '/includes/admin/startup.php';
}