<?php
/**
Plugin Name: Motors - Classified Listing
Plugin URI: http://stylemixthemes.com/
Description: Manage classified listings from the WordPress admin panel, and allow users to post classified listings directly to your website.
Author: StylemixThemes
Author URI: http://stylemixthemes.com/
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

require_once __DIR__ . '/includes/wp-async-request.php';
require_once __DIR__ . '/includes/wp-background-process.php';
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


class Hendcorp_Background_Processing {

    /**
     * @var WP_Example_Process
     */
    protected $process_all;

    /**
     * the constructor.
     */
    public function __construct() {
        register_activation_hook( __FILE__, array($this, 'tmf_run_on_activate') );
        register_deactivation_hook( __FILE__, array($this, 'tmf_run_on_deactivate') );
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        add_action( 'init', array( $this, 'process_handler' ) );
        add_action ('tmf_scheduler', array( $this, 'tmf_execute_cron') );
    }

    /**
     * Init
     */
    public function init() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/hendcorp_data_fetcher.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/hendcorp_processor.php';

        $this->process_all    = new Hendcorp_Processor();
    }


    public function tmf_run_on_activate()
    {
        // for notifications
        if( !wp_next_scheduled( 'tmf_scheduler' ) )
        {
            wp_schedule_event( time(), 'daily', 'tmf_scheduler' );
        }

    } // end tmf_run_on_activate()    


    public function tmf_run_on_deactivate()
    {
        wp_clear_scheduled_hook('tmf_scheduler');
    } // end tmf_run_on_activate()

    /**
     * Process handler
     */
    public function process_handler() {
        if ( ! isset( $_GET['fetchvehicle'] ) ) {
            return;
        }

        if ( 'all' === $_GET['fetchvehicle'] ) {
            $this->handle_all();
        }

    }

    /**
    * Cron processor
    */
    public function tmf_execute_cron() {
        $this->handle_all();
    }


    /**
     * Handle all
     */
    protected function handle_all() {
        delete_all_posts_beforehand();
        
        $csv = fetch_vehicles_api();

        foreach($csv as $row) {
            $this->process_all->push_to_queue( $row );
        }

        $this->process_all->save()->dispatch();
    }

}

new Hendcorp_Background_Processing();