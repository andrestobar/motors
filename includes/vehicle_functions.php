<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('stm_ajaxurl')) {
    /**
     * Ajax admin Url declaration.
     */
    function stm_ajaxurl()
    {
        $my_locale = explode('_', get_locale());
        ?>
        <script type="text/javascript">
            var stm_lang_code = '<?php echo $my_locale[0]; ?>';
            <?php if (class_exists('SitePress')): ?>
            stm_lang_code = '<?php echo ICL_LANGUAGE_CODE; ?>';
            <?php endif; ?>
            var ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
            var stm_site_blog_id = "<?php echo get_current_blog_id(); ?>";
            var stm_added_to_compare_text = "<?php esc_html_e('Added to compare', 'stm_vehicles_listing'); ?>";
            var stm_removed_from_compare_text = "<?php esc_html_e('was removed from compare', 'stm_vehicles_listing'); ?>";
            <?php if(stm_is_boats()): ?>
            var stm_already_added_to_compare_text = "<?php esc_html_e('You have already added 3 boats', 'stm_vehicles_listing'); ?>";
            <?php else: ?>
            var stm_already_added_to_compare_text = "<?php esc_html_e('You have already added 3 cars', 'stm_vehicles_listing'); ?>";
            <?php endif; ?>
        </script>
        <?php
    }

    add_action('wp_head', 'stm_ajaxurl');
}

if (!function_exists('stm_get_listing_archive_link')) {
    /**
     * Get inventory URL.
     */
    function stm_get_listing_archive_link( $filters = array() )
    {
        $listing_link = stm_listings_user_defined_filter_page();

        if (!$listing_link) {

            $options = get_option('stm_post_types_options');

            $defaultPostTypesOptions = array(
                'listings' => array(
                    'title' => __('Listings', 'stm_vehicles_listing'),
                    'plural_title' => __('Listings', 'stm_vehicles_listing'),
                    'rewrite' => 'listings'
                ),
            );

            $stm_vehicle_options = wp_parse_args($options, $defaultPostTypesOptions);

            $listing_link = site_url() . '/' . $stm_vehicle_options['listings']['rewrite'] . '/';
        } else {
            $listing_link = get_permalink($listing_link);
        }

        $qs = array();
	    foreach ( $filters as $key => $val ) {
		    $info = stm_get_all_by_slug( preg_replace( '/^(min_|max_)/',  '', $key ) );
		    $qs[] = $key . ( ! empty( $info['listing_rows_numbers'] ) ? '[]=' : '=' ) . $val;
	    }

        if ( count( $qs ) ) {
	    	$listing_link .= '?' . join( '&', $qs );
		}

        return apply_filters( 'stm_filter_listing_link', $listing_link, $filters );
    }
}


if (!function_exists('stm_listing_price_view')) {
    /**
     * Price delimeter
     */
    function stm_listing_price_view($price)
    {
        if (!empty($price)) {
            $price_label = stm_get_price_currency();
            $price_label_position = get_theme_mod('price_currency_position', 'left');
            $price_delimeter = get_theme_mod('price_delimeter', ' ');

			if(strpos($price, '<') !== false || strpos($price, '>') !== false){
				$priceConvert = number_format(getConverPrice(filter_var($price, FILTER_SANITIZE_NUMBER_INT)), 0, '', $price_delimeter);
			}
			elseif(strpos($price, '-') !== false){
				$priceArr = explode('-', $price);
				$priceConvert = number_format(getConverPrice($priceArr[0]), 0, '', $price_delimeter) . '-' . number_format(getConverPrice($priceArr[1]), 0, '', $price_delimeter);
			} else {
				$priceConvert = number_format(getConverPrice($price), 0, '', $price_delimeter);
			}

            if ($price_label_position == 'left') {

				$response = $price_label . $priceConvert;

                if(strpos($price, '<') !== false){
					$response = '&lt; ' . $price_label . $priceConvert;
				} elseif(strpos($price, '>') !== false){
					$response = '&gt; ' . $price_label . $priceConvert;
				}
            } else {
				$response = $priceConvert . $price_label;

				if(strpos($price, '<') !== false){
					$response = '&lt; ' . $priceConvert . $price_label;
				} elseif(strpos($price, '>') !== false){
					$response = '&gt; ' . $priceConvert . $price_label;
				}
            }

            return apply_filters('stm_filter_price_view', $response);
        }
    }
}

if (!function_exists('stm_get_price_currency')) {
    /**
     * Get price currency
     */
    function stm_get_price_currency()
    {
        $currency = get_theme_mod('price_currency', '$');
        if(isset($_COOKIE["stm_current_currency"])) {
            $cookie = explode("-", $_COOKIE["stm_current_currency"]);
            $currency = $cookie[0];
        }
        return $currency;
    }
}

if (!function_exists('stm_add_admin_body_class')) {
    /**
     * Add class
     */
    function stm_add_admin_body_class($classes)
    {
        $name = 'not_motors';
        if (stm_check_motors()) {
            $name = '';
        }

        return apply_filters("stm_listings_admin_body_class", "$classes stm-template-" . $name);
    }

    add_filter('admin_body_class', 'stm_add_admin_body_class');
}


if (!function_exists('stm_get_post_limits')) {
    /**
     * Get user adds and media limitations
     *
     * @param $user_id
     *
     * @return mixed|void
     */
    function stm_get_post_limits($user_id)
    {
        $user_id = intval($user_id);

        $restrictions = array(
            'posts' => 0,
            'images' => 0,
        );

        if (!empty($user_id)) {

            $created_posts = 0;

            $post_status = 'any';

            $args = array(
                'post_type' => 'listings',
                'post_status' => $post_status,
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'stm_car_user',
                        'value' => $user_id,
                        'compare' => '='
                    )
                )
            );

            $query = new WP_Query($args);

            if (!empty($query->found_posts)) {
                $created_posts = intval($query->found_posts);
            }


            $posts_allowed = get_theme_mod('user_post_limit', '3');
            $restrictions['posts_allowed'] = intval($posts_allowed);
            $restrictions['premoderation'] = get_theme_mod('user_premoderation', true);
            $restrictions['images'] = get_theme_mod('user_post_images_limit', '5');
            $restrictions['role'] = 'user';

            $user_can_create = intval($posts_allowed) - intval($created_posts);

            if ($user_can_create < 1) {
                $user_can_create = 0;
            }

            $restrictions['posts'] = intval($user_can_create);
        } else {
            $restrictions['premoderation'] = get_theme_mod('user_premoderation', true);
            $restrictions['posts'] = get_theme_mod('user_post_limit', '3');
            $restrictions['images'] = get_theme_mod('user_post_images_limit', '5');
            $restrictions['role'] = 'user';
        }

        /*IF is admin, set all */
        if (user_can($user_id, 'manage_options')) {
            $restrictions['premoderation'] = false;
            $restrictions['posts'] = '9999';
            $restrictions['images'] = '9999';
            $restrictions['role'] = 'user';
        }

        return apply_filters('stm_filter_user_restrictions', $restrictions, $user_id);

    }
}

if (!function_exists('stm_get_user_custom_fields')) {
    /**
     * Get user additional information
     *
     * @param $user_id
     *
     * @return mixed|void
     */
    function stm_get_user_custom_fields($user_id)
    {
        $response = array();

        if (empty($user_id)) {
            $user_current = wp_get_current_user();
            $user_id = $user_current->ID;
        }

        //Phone
        $user_phone = '';
        $user_phone = get_the_author_meta('stm_phone', $user_id);

        $user_mail = '';
        $user_mail = get_the_author_meta('email', $user_id);

        $user_show_mail = '';
        $user_show_mail = get_the_author_meta('stm_show_email', $user_id);

        $user_name = '';
        $user_name = get_the_author_meta('first_name', $user_id);

        $user_last_name = '';
        $user_last_name = get_the_author_meta('last_name', $user_id);

        //Image
        $user_image = '';
        $user_image = get_the_author_meta('stm_user_avatar', $user_id);


        //Socials
        $socials = array('facebook', 'twitter', 'linkedin', 'youtube');
        $user_socials = array();
        foreach ($socials as $social) {
            $user_soc = get_the_author_meta('stm_user_' . $social, $user_id);
            if (!empty($user_soc)) {
                $user_socials[$social] = $user_soc;
            }
        }

        $response['user_id'] = $user_id;
        $response['phone'] = $user_phone;
        $response['image'] = $user_image;
        $response['name'] = $user_name;
        $response['last_name'] = $user_last_name;
        $response['socials'] = $user_socials;
        $response['email'] = $user_mail;
        $response['show_mail'] = $user_show_mail;

        /*Dealer fields*/
        $logo = '';
        $logo = get_the_author_meta('stm_dealer_logo', $user_id);

        $dealer_image = '';
        $dealer_image = get_the_author_meta('stm_dealer_image', $user_id);

        $license = '';
        $license = get_the_author_meta('stm_company_license', $user_id);

        $website = '';
        $website = get_the_author_meta('stm_website_url', $user_id);

        $location = '';
        $location = get_the_author_meta('stm_dealer_location', $user_id);

        $location_lat = '';
        $location_lat = get_the_author_meta('stm_dealer_location_lat', $user_id);

        $location_lng = '';
        $location_lng = get_the_author_meta('stm_dealer_location_lng', $user_id);

        $stm_company_name = '';
        $stm_company_name = get_the_author_meta('stm_company_name', $user_id);

        $stm_company_license = '';
        $stm_company_license = get_the_author_meta('stm_company_license', $user_id);

        $stm_message_to_user = '';
        $stm_message_to_user = get_the_author_meta('stm_message_to_user', $user_id);

        $stm_sales_hours = '';
        $stm_sales_hours = get_the_author_meta('stm_sales_hours', $user_id);

        $stm_seller_notes = '';
        $stm_seller_notes = get_the_author_meta('stm_seller_notes', $user_id);

        $stm_payment_status = '';
        $stm_payment_status = get_the_author_meta('stm_payment_status', $user_id);


        $response['logo'] = $logo;
        $response['dealer_image'] = $dealer_image;
        $response['license'] = $license;
        $response['website'] = $website;
        $response['location'] = $location;
        $response['location_lat'] = $location_lat;
        $response['location_lng'] = $location_lng;
        $response['stm_company_name'] = $stm_company_name;
        $response['stm_company_license'] = $stm_company_license;
        $response['stm_message_to_user'] = $stm_message_to_user;
        $response['stm_sales_hours'] = $stm_sales_hours;
        $response['stm_seller_notes'] = $stm_seller_notes;
        $response['stm_payment_status'] = $stm_payment_status;


        return apply_filters('stm_filter_user_fields', $response);


    }
}

if (!function_exists('stm_display_user_name')) {
    /**
     * User display name
     *
     * @param $user_id
     * @param string $user_login
     * @param string $f_name
     * @param string $l_name
     */
    function stm_display_user_name($user_id, $user_login = '', $f_name = '', $l_name = '')
    {
        $user = get_userdata($user_id);

        if (empty($user_login)) {
            $login = $user->data->user_login;
        } else {
            $login = $user_login;
        }
        if (empty($f_name)) {
            $first_name = get_the_author_meta('first_name', $user_id);
        } else {
            $first_name = $f_name;
        }

        if (empty($l_name)) {
            $last_name = get_the_author_meta('last_name', $user_id);
        } else {
            $last_name = $l_name;
        }

        $display_name = $login;

        if (!empty($first_name)) {
            $display_name = $first_name;
        }

        if (!empty($first_name) and !empty($last_name)) {
            $display_name .= ' ' . $last_name;
        }

        if (empty($first_name) and !empty($last_name)) {
            $display_name = $last_name;
        }


        echo apply_filters('stm_filter_display_user_name', $display_name, $user_id, $user_login, $f_name, $l_name);

    }
}

if (!function_exists('stm_custom_login')) {
    /**
     * Log in
     */
    function stm_custom_login()
    {
        $errors = array();

        if (empty($_POST['stm_user_login'])) {
            $errors['stm_user_login'] = true;
        } else {
            $username = sanitize_text_field($_POST['stm_user_login']);
        }

        if (empty($_POST['stm_user_password'])) {
            $errors['stm_user_password'] = true;
        } else {
            $password = $_POST['stm_user_password'];
        }
        
        if(isset($_POST['redirect_path'])) {
	        $redirect_path = $_POST['redirect_path'];
        }

        $remember = false;

        if (!empty($_POST['stm_remember_me']) and $_POST['stm_remember_me'] == 'on') {
            $remember = true;
        }

        if (!empty($_POST['redirect']) and $_POST['redirect'] == 'disable') {
            $redirect = false;
        } else {
            $redirect = true;
        }

        //AUTH
	    $errors = apply_filters("stm_user_login", $errors, $username);
        
        if (empty($errors)) {
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $user = get_user_by('email', $username);
            } else {
                $user = get_user_by('login', $username);
            }

            if ($user) {
                $username = $user->data->user_login;
            }

            $creds = array();
            $creds['user_login'] = $username;
            $creds['user_password'] = $password;
            $creds['remember'] = $remember;
            $secure_cookie = is_ssl() ? true : false;


            $user = wp_signon($creds, $secure_cookie);
            if (is_wp_error($user)) {
                $response['message'] = esc_html__('Wrong Username or Password', 'stm_vehicles_listing');
            } else {
                if ($redirect) {
                    $response['message'] = esc_html__('Successfully logged in. Redirecting...', 'stm_vehicles_listing');
					$response['redirect_url'] = (!empty($redirect_path)) ? $redirect_path : get_author_posts_url($user->ID);
                } else {
                    ob_start();
                    stm_add_a_car_user_info('', '', '', $user->ID);
                    $response['user_html'] = ob_get_clean();
                }
            }
        } else {
            $response['message'] = esc_html__('Please fill required fields', 'stm_vehicles_listing');
        }

        $response['errors'] = $errors;

        $response = json_encode($response);
        echo apply_filters('stm_filter_custom_login', $response);
        exit;
    }

    add_action('wp_ajax_stm_custom_login', 'stm_custom_login');
    add_action('wp_ajax_nopriv_stm_custom_login', 'stm_custom_login');
}

if (!function_exists('stm_custom_register')) {
    /**
     * Registration
     */
    function stm_custom_register()
    {
        $response = array();
        $errors = array();

        if (empty($_POST['stm_nickname'])) {
            $errors['stm_nickname'] = true;
        } else {
            $user_login = sanitize_text_field($_POST['stm_nickname']);
        }

        if (empty($_POST['stm_user_first_name'])) {
            $user_name = '';
        } else {
            $user_name = sanitize_text_field($_POST['stm_user_first_name']);
        }

        if (empty($_POST['stm_user_last_name'])) {
            $user_lastname = '';
        } else {
            $user_lastname = sanitize_text_field($_POST['stm_user_last_name']);
        }

        if (isset($_POST['g-recaptcha-response'])) {
            if (empty($_POST['g-recaptcha-response'])) {
                $errors['captcha'] = true;
                $response['message'] = esc_html__('Please, enter captcha', 'stm_vehicles_listing');
            }
        }

        if (empty($_POST['stm_user_phone'])) {
            $user_phone = '';
        } elseif (empty($_POST['stm_user_phone'])) {
            $errors['stm_user_phone'] = true;
        } else {
            $user_phone = sanitize_text_field($_POST['stm_user_phone']);
        }

        if (!is_email($_POST['stm_user_mail'])) {
            $errors['stm_user_mail'] = true;
        } else {
            $user_mail = sanitize_email($_POST['stm_user_mail']);
        }

        if (empty($_POST['stm_user_password'])) {
            $errors['stm_user_password'] = true;
        } else {
            $user_pass = $_POST['stm_user_password'];
        }

        if (!empty($_POST['redirect']) and $_POST['redirect'] == 'disable') {
            $redirect = false;
        } else {
            $redirect = true;
        }

        $demo = stm_is_site_demo_mode();
        if ($demo) {
            $errors['demo'] = true;
        }

        if (empty($errors)) {
            $user_data = array(
                'user_login' => $user_login,
                'user_pass' => $user_pass,
                'first_name' => $user_name,
                'last_name' => $user_lastname,
                'user_email' => $user_mail
            );

            $user_id = wp_insert_user($user_data);

            if (!is_wp_error($user_id)) {
                update_user_meta($user_id, 'stm_phone', $user_phone);
                update_user_meta($user_id, 'stm_show_email', 'show');

                wp_set_current_user($user_id, $user_login);
                wp_set_auth_cookie($user_id);
                do_action('wp_login', $user_login, new WP_User($user_id));

                if ($redirect) {
                    $response['message'] = esc_html__('Congratulations! You have been successfully registered. Redirecting to your account profile page.', 'stm_vehicles_listing');
                    $response['redirect_url'] = get_author_posts_url($user_id);
                } else {
                    ob_start();
                    stm_add_a_car_user_info($user_login, $user_name, $user_lastname, $user_id);
                    $response['user_html'] = ob_get_clean();
                }

                //AUTH
	            do_action( 'stm_register_new_user', $user_id );
	            if((int)get_option('users_can_register')){
		            $response['message'] = esc_html__('Congratulations! You have been successfully registered. Please, activate your account', 'stm_vehicles_listing');
	            }else{
		            wp_set_current_user($user_id, $user_login);
		            wp_set_auth_cookie($user_id);
		            do_action('wp_login', $user_login, new WP_User($user_id));
		            if ($redirect) {
			            $response['message'] = esc_html__('Congratulations! You have been successfully registered. Redirecting to your account profile page.', 'stm_vehicles_listing');
			            $response['redirect_url'] = get_author_posts_url($user_id);
		            } else {
			            ob_start();
			            stm_add_a_car_user_info($user_login, $user_name, $user_lastname, $user_id);
			            $response['user_html'] = ob_get_clean();
		            }
	            }
                

                /*Mail*/
                $to = get_bloginfo('admin_email');
                $subject = esc_html__('New User', 'stm_vehicles_listing');
                $body = esc_html__('New user Registered. Nickname: ', 'stm_vehicles_listing') . $user_login;

                wp_mail($to, $subject, $body);

            } else {
                $response['message'] = $user_id->get_error_message();
                $response['user'] = $user_id;
            }
        } else {

            if ($demo) {
                $response['message'] = esc_html__('Site is on demo mode', 'stm_vehicles_listing');
            } else {
                $response['message'] = esc_html__('Please fill required fields', 'stm_vehicles_listing');
            }
        }


        $response['errors'] = $errors;
        $response = json_encode($response);
        echo apply_filters('stm_filter_custom_register', $response);
        exit;
    }

    add_action('wp_ajax_stm_custom_register', 'stm_custom_register');
    add_action('wp_ajax_nopriv_stm_custom_register', 'stm_custom_register');
}

if (!function_exists('stm_add_a_car_user_info')) {
    /**
     * Add car user info
     *
     * @param string $user_login
     * @param string $f_name
     * @param string $l_name
     * @param string $user_id
     */
    function stm_add_a_car_user_info($user_login = '', $f_name = '', $l_name = '', $user_id = '')
    {
        $user = stm_get_user_custom_fields($user_id);
        if (!is_wp_error($user)) {
            $user_id = $user['user_id'];
            do_action('stm_car_user_info_before_action');
            ?>

            <div class="stm-add-a-car-user">
                <div class="clearfix">
                    <div class="left-info">
                        <div class="avatar">
                            <?php if (!empty($user['image'])): ?>
                                <img src="<?php echo esc_url($user['image']); ?>"/>
                            <?php else: ?>
                                <i class="stm-service-icon-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="user-info">
                            <h4><?php stm_display_user_name($user['user_id'], $user_login, $f_name, $l_name); ?></h4>
                            <div class="stm-label"><?php esc_html_e('Private Seller', 'stm_vehicles_listing'); ?></div>
                        </div>
                    </div>

                    <div class="right-info">

                        <a target="_blank"
                           href="<?php echo esc_url(add_query_arg(array('view-myself' => 1), get_author_posts_url($user_id))); ?>">
                            <i class="fa fa-external-link"></i><?php esc_html_e('Show my Public Profile', 'stm_vehicles_listing'); ?>
                        </a>

                        <div class="stm_logout">
                            <a href="#"><?php esc_html_e('Log out', 'stm_vehicles_listing'); ?></a>
                            <?php esc_html_e('to choose a different account', 'stm_vehicles_listing'); ?>
                        </div>

                    </div>

                </div>
            </div>

        <?php }
        do_action('stm_car_user_info_after_action');
    }
}

if (!function_exists('stm_logout_user')) {
    /**
     * Logout
     */
    function stm_logout_user()
    {
        $response = array();
        wp_logout();
        $response['exit'] = true;
        $response = json_encode($response);
        echo $response;
        exit;
    }

    add_action('wp_ajax_stm_logout_user', 'stm_logout_user');
    add_action('wp_ajax_nopriv_stm_logout_user', 'stm_logout_user');
}

if (!function_exists('stm_is_site_demo_mode')) {
    /**
     * Site demo mode
     * @return string
     */
    function stm_is_site_demo_mode()
    {

        $site_demo_mode = get_theme_mod('site_demo_mode', false);

        return apply_filters('stm_site_demo_mode', $site_demo_mode);
    }
}

if (!function_exists('stm_ajax_add_a_car')) {
    /**
     * Add a car
     * @return bool
     */
    function stm_ajax_add_a_car()
    {
        $response = array();
        $first_step = array(); //needed fields
        $second_step = array(); //secondary fields
        $car_features = array(); //array of features car
        $videos = array(); /*videos links*/
        $notes = esc_html__('N/A', 'stm_vehicles_listing');
        $registered = '';
        $vin = '';
        $history = array(
            'label' => '',
            'link' => ''
        );
        $location = array(
            'label' => '',
            'lat' => '',
            'lng' => '',
        );

        if (!is_user_logged_in()) {
            $response['message'] = esc_html__('Please, log in', 'stm_vehicles_listing');

            return false;
        } else {
            $user = stm_get_user_custom_fields('');
            $restrictions = stm_get_post_limits($user['user_id']);
        }


        $response['message'] = '';
        $error = false;

        $demo = stm_is_site_demo_mode();
        if ($demo) {
            $error = true;
            $response['message'] = esc_html__('Site is on demo mode', 'stm_vehicles_listing');
        }

        $update = false;
        if (!empty($_POST['stm_current_car_id'])) {
            $post_id = intval($_POST['stm_current_car_id']);
            $car_user = get_post_meta($post_id, 'stm_car_user', true);
            $update = true;

            /*Check if current user edits his car*/
            if (intval($car_user) != intval($user['user_id'])) {
                return false;
            }
        }

        /*Get if no available posts*/
        if ($restrictions['posts'] < 1 and !$update) {
            $error = true;
            $response['message'] = esc_html__('You do not have available posts', 'stm_vehicles_listing');
        }

        /*Getting second step*/
        foreach ($_POST as $second_step_key => $second_step_value) {
            if (strpos($second_step_key, 'stm_s_s_') !== false) {
                if (!empty($_POST[$second_step_key])) {
                    $original_key = str_replace('stm_s_s_', '', $second_step_key);
                    $second_step[sanitize_title($original_key)] = sanitize_text_field($_POST[$second_step_key]);
                }
            }
        }

        /*Getting car features*/
        if (!empty($_POST['stm_car_features_labels'])) {
            foreach ($_POST['stm_car_features_labels'] as $car_feature) {
                $car_features[] = sanitize_text_field($car_feature);
            }
        }

        /*Videos*/
        if (!empty($_POST['stm_video'])) {
            foreach ($_POST['stm_video'] as $video) {
                $videos[] = esc_url($video);
            }
        }

        /*Note*/
        if (!empty($_POST['stm_seller_notes'])) {
            $notes = esc_html($_POST['stm_seller_notes']);
        }

        /*Registration date*/
        if (!empty($_POST['stm_registered'])) {
            $registered = sanitize_text_field($_POST['stm_registered']);
        }

        /*Vin*/
        if (!empty($_POST['stm_vin'])) {
            $vin = sanitize_text_field($_POST['stm_vin']);
        }

        /*History*/
        if (!empty($_POST['stm_history_label'])) {
            $history['label'] = sanitize_text_field($_POST['stm_history_label']);
        }

        if (!empty($_POST['stm_history_link'])) {
            $history['link'] = esc_url($_POST['stm_history_link']);
        }

        /*Location*/
        if (!empty($_POST['stm_location_text'])) {
            $location['label'] = sanitize_text_field($_POST['stm_location_text']);
        }

        if (!empty($_POST['stm_lat'])) {
            $location['lat'] = sanitize_text_field($_POST['stm_lat']);
        }

        if (!empty($_POST['stm_lng'])) {
            $location['lng'] = sanitize_text_field($_POST['stm_lng']);
        }

        if (empty($_POST['stm_car_price'])) {
            $error = true;
            $response['message'] = esc_html__('Please add car price', 'stm_vehicles_listing');
        } else {
            $price = abs(intval($_POST['stm_car_price']));
        }

        if (!empty($_POST['car_price_form_label'])) {
            $location['car_price_form_label'] = sanitize_text_field($_POST['car_price_form_label']);
        }

        if (!empty($_POST['stm_car_sale_price'])) {
            $location['stm_car_sale_price'] = abs(sanitize_text_field($_POST['stm_car_sale_price']));
        }

        $generic_title = '';
        if (!empty($_POST['stm_car_main_title'])) {
            $generic_title = sanitize_text_field($_POST['stm_car_main_title']);
        } else {
            $error = true;
            $response['message'] = esc_html__('Enter title', 'stm_vehicles_listing');
        }

        /*Generating post*/
        if (!$error) {

            if ($restrictions['premoderation']) {
                $status = 'pending';
            } else {
                $status = 'publish';
            }

            $post_data = array(
                'post_type' => 'listings',
                'post_title' => '',
                'post_content' => '',
                'post_status' => $status,
            );

            $post_data['post_content'] = $notes;

            if (!empty($generic_title)) {
                $post_data['post_title'] = $generic_title;
            }

            if (!$update) {
                $post_id = wp_insert_post($post_data, true);
            }


            if (!is_wp_error($post_id)) {

                if ($update) {
                    $post_data_update = array(
                        'ID' => $post_id,
                        'post_content' => $post_data['post_content'],
                        'post_status' => $status,
                    );

                    if (!empty($generic_title)) {
                        $post_data_update['post_title'] = $generic_title;
                    }

                    wp_update_post($post_data_update);
                }

                update_post_meta($post_id, 'stock_number', $post_id);
                update_post_meta($post_id, 'stm_car_user', $user['user_id']);

                /*Set categories*/
                foreach ($first_step as $tax => $term) {
                    $tax_info = stm_get_all_by_slug($tax);
                    if (!empty($tax_info['numeric']) and $tax_info['numeric']) {
                        update_post_meta($post_id, $tax, abs(sanitize_title($term)));
                    } else {
                        wp_delete_object_term_relationships($post_id, $tax);
                        wp_add_object_terms($post_id, $term, $tax, true);
                        update_post_meta($post_id, $tax, sanitize_title($term));
                    }
                }

                if (!empty($second_step)) {
                    /*Set categories*/
                    foreach ($second_step as $tax => $term) {
                        if (!empty($tax) and !empty($term)) {
                            $tax_info = stm_get_all_by_slug($tax);
                            if (!empty($tax_info['numeric']) and $tax_info['numeric']) {
                                update_post_meta($post_id, $tax, sanitize_text_field($term));
                            } else {
                                wp_delete_object_term_relationships($post_id, $tax);
                                wp_add_object_terms($post_id, $term, $tax, true);
                                update_post_meta($post_id, $tax, sanitize_text_field($term));
                            }
                        }
                    }
                }

                if (!empty($videos)) {
                    update_post_meta($post_id, 'gallery_video', $videos[0]);

                    if (count($videos) > 1) {
                        array_shift($videos);
                        update_post_meta($post_id, 'gallery_videos', array_filter(array_unique($videos)));
                    }

                }

                if (!empty($vin)) {
                    update_post_meta($post_id, 'vin_number', $vin);
                }

                if (!empty($registered)) {
                    update_post_meta($post_id, 'registration_date', $registered);
                }

                if (!empty($history['label'])) {
                    update_post_meta($post_id, 'history', $history['label']);
                }

                if (!empty($history['link'])) {
                    update_post_meta($post_id, 'history_link', $history['link']);
                }

                if (!empty($location['label'])) {
                    update_post_meta($post_id, 'stm_car_location', $location['label']);
                }

                if (!empty($location['lat'])) {
                    update_post_meta($post_id, 'stm_lat_car_admin', $location['lat']);
                }

                if (!empty($location['lng'])) {
                    update_post_meta($post_id, 'stm_lng_car_admin', $location['lng']);
                }

                if (!empty($car_features)) {
                    update_post_meta($post_id, 'additional_features', implode(',', $car_features));
                }


                update_post_meta($post_id, 'price', $price);
                update_post_meta($post_id, 'stm_genuine_price', $price);

                if (!empty($location['car_price_form_label'])) {
                    update_post_meta($post_id, 'car_price_form_label', $location['car_price_form_label']);
                }

                if (!empty($location['stm_car_sale_price'])) {
                    update_post_meta($post_id, 'sale_price', $location['stm_car_sale_price']);
                    update_post_meta($post_id, 'stm_genuine_price', $location['stm_car_sale_price']);
                } else {
                    update_post_meta($post_id, 'sale_price', '');
                }

                update_post_meta($post_id, 'title', 'hide');
                update_post_meta($post_id, 'breadcrumbs', 'show');

                $response['post_id'] = $post_id;
                if (($update)) {
                    $response['message'] = esc_html__('Car Updated, uploading photos', 'stm_vehicles_listing');
                } else {
                    $response['message'] = esc_html__('Car Added, uploading photos', 'stm_vehicles_listing');
                }

            } else {
                $response['message'] = $post_id->get_error_message();
            }
        }

        $response = json_encode($response);
        echo apply_filters('stm_filter_add_a_car', $response);
        exit;
    }

    add_action('wp_ajax_stm_ajax_add_a_car', 'stm_ajax_add_a_car');
    add_action('wp_ajax_nopriv_stm_ajax_add_a_car', 'stm_ajax_add_a_car');
}

if (!function_exists('stm_ajax_add_a_car_media')) {
    /**
     * Car media
     */
    function stm_ajax_add_a_car_media()
    {
        if (stm_is_site_demo_mode()) {
            wp_send_json(array('message' => esc_html__('Site is on demo mode', 'stm_vehicles_listing')));
            exit;
        }

        $post_id = intval($_POST['post_id']);
        if (!$post_id) {
            /*No id passed from first ajax Call?*/
            wp_send_json(array('message' => esc_html__('Some error occurred, try again later', 'stm_vehicles_listing')));
            exit;
        }

        $user_id = get_current_user_id();
        $limits = stm_get_post_limits($user_id);
        $updating = !empty($_POST['stm_edit']) and $_POST['stm_edit'] == 'update';

        if (intval(get_post_meta($post_id, 'stm_car_user', true)) != intval($user_id)) {
            /*User tries to add info to another car*/
            wp_send_json(array('message' => esc_html__('You are trying to add car to another car user, or your session has expired, please sign in first', 'stm_vehicles_listing')));
            exit;
        }


        $attachments_ids = array();
        foreach ($_POST as $get_media_keys => $get_media_values) {
            if (strpos($get_media_keys, 'media_position_') !== false) {
                $attachments_ids[str_replace('media_position_', '', $get_media_keys)] = intval($get_media_values);
            }
        }

        $error = false;
        $response = array(
            'message' => '',
            'post' => $post_id,
			'errors' => array(),
        );

        $files_approved = array();

        if (!empty($_FILES)) {

            $max_file_size = apply_filters('stm_listing_media_upload_size', 1024 * 4000); /*4mb is highest media upload here*/

            $max_uploads = intval($limits['images']) - count($attachments_ids);

            if (count($_FILES['files']['name']) > $max_uploads) {
                $error = true;
                $response['message'] = sprintf(esc_html__('Sorry, you can upload only %d images per add', 'stm_vehicles_listing'), $max_uploads);
            } else {
                // Check if user is trying to upload more than the allowed number of images for the current post
                foreach ($_FILES['files']['name'] as $f => $name) {
                    if (count($files_approved) == $max_uploads) {
                        break;
                    } elseif ($_FILES['files']['error'][$f] != UPLOAD_ERR_OK) {
                        $error = true;
                    } else {
                        // Check if image size is larger than the allowed file size

                        // Check if the file being uploaded is in the allowed file types
                        $check_image = @getimagesize($_FILES['files']['tmp_name'][$f]);
                        if ($_FILES['files']['size'][$f] > $max_file_size) {
                            $response['message'] = esc_html__('Sorry, image is too large', 'stm_vehicles_listing') . ': ' . $name;
                            $error = true;
                        } elseif (empty($check_image)) {
                            $response['message'] = esc_html__('Sorry, image has invalid format', 'stm_vehicles_listing') . ': ' . $name;
                            $error = true;
                        } else {
                        	$tmp_name = $_FILES['files']['tmp_name'][ $f ];
                        	$error = $_FILES['files']['error'][ $f ];
                        	$type = $_FILES['files']['type'][ $f ];
                            $files_approved[$f] = compact('name', 'tmp_name', 'type', 'error');
                        }
                    }
                }
            }
        }

        if ($error) {
            if (!$updating) {
                wp_delete_post($post_id, true);
            }
            wp_send_json($response);
            exit;
        }

		require_once(ABSPATH . 'wp-admin/includes/image.php');

        foreach ($files_approved as $f => $file) {
        	$uploaded = wp_handle_upload($file, array('action' => 'stm_ajax_add_a_car_media'));

        	if ($uploaded['error']) {
        		$response['errors'][ $file['name'] ] = $uploaded;
        		continue;
			}

			$filetype = wp_check_filetype(basename($uploaded['file']), null);

			// Insert attachment to the database
			$attach_id = wp_insert_attachment(array(
				'guid' => $uploaded['url'],
				'post_mime_type' => $filetype['type'],
				'post_title' => preg_replace('/\.[^.]+$/', '', basename($uploaded['file'])),
				'post_content' => '',
				'post_status' => 'inherit',
			), $uploaded['file'], $post_id);

			// Generate meta data
			wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $uploaded['file']));

			$attachments_ids[$f] = $attach_id;
        }

        $current_attachments = get_posts(array(
            'fields' => 'ids',
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_parent' => $post_id,
        ));

        $delete_attachments = array_diff($current_attachments, $attachments_ids);
        foreach ($delete_attachments as $delete_attachment) {
            stm_delete_media(intval($delete_attachment));
        }

        ksort($attachments_ids);
        if (!empty($attachments_ids)) {
            update_post_meta($post_id, '_thumbnail_id', reset($attachments_ids));
            array_shift($attachments_ids);
        }

        update_post_meta($post_id, 'gallery', $attachments_ids);

        if ($updating) {
            $response['message'] = esc_html__('Car updated, redirecting to your account profile', 'stm_vehicles_listing');

            $to = get_bloginfo('admin_email');
            $subject = esc_html__('Car Updated', 'stm_vehicles_listing');
            $body = esc_html__('User Updated car. User id - ', 'stm_vehicles_listing') . $user_id . '. ';
            $body .= esc_html__('Car ID - ', 'stm_vehicles_listing') . $post_id;

        } else {
            $response['message'] = esc_html__('Car added, redirecting to your account profile', 'stm_vehicles_listing');

            $to = get_bloginfo('admin_email');
            $subject = esc_html__('Car Added', 'stm_vehicles_listing');
            $body = esc_html__('User Added car. User id - ', 'stm_vehicles_listing') . $user_id . $user_id . '. ';;
            $body .= esc_html__('Car ID - ', 'stm_vehicles_listing') . $post_id;
        }

        if(apply_filters('stm_listings_notify_updated', true)) {
            wp_mail($to, $subject, $body);
        }

        $response['success'] = true;
        $response['url'] = esc_url(get_author_posts_url($user_id));

        wp_send_json(apply_filters('stm_filter_add_car_media', $response));
        exit;
    }

    add_action('wp_ajax_stm_ajax_add_a_car_media', 'stm_ajax_add_a_car_media');
    add_action('wp_ajax_nopriv_stm_ajax_add_a_car_media', 'stm_ajax_add_a_car_media');
}

if (!function_exists('stm_media_random_affix')) {
    /**
     * Helper function for media to generate random name
     *
     * @param int $length
     *
     * @return string
     */
    function stm_media_random_affix($length = 5)
    {

        $string = '';
        $characters = "23456789ABCDEFHJKLMNPRTVWXYZabcdefghijklmnopqrstuvwxyz";

        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;

    }
}

if (!function_exists('stm_delete_media')) {
    /**
     * Delete media
     *
     * @param $media_id
     */
    function stm_delete_media($media_id)
    {
        $current_user = wp_get_current_user();
        $media_id = intval($media_id);
        if (!empty($current_user->ID)) {
            $current_user_id = $current_user->ID;

            $args = array(
                'author' => intval($current_user_id),
                'post_status' => 'any',
                'post__in' => array($media_id),
                'post_type' => 'attachment'
            );

            $query = new WP_Query($args);

            if ($query->found_posts == 1) {
                wp_delete_attachment($media_id, true);
            }
        }
    }
}

if (!function_exists('stm_user_listings_query')) {
    /**
     * Get User cars
     *
     * @param $user_id
     * @param string $status
     * @param int $per_page
     * @param bool $popular
     * @param int $offset
     * @param bool $data_desc
     *
     * @return WP_Query
     */
    function stm_user_listings_query($user_id, $status = "publish", $per_page = -1, $popular = false, $offset = 0, $data_desc = false)
    {
        $args = array(
            'post_type' => 'listings',
            'post_status' => $status,
            'posts_per_page' => $per_page,
            'offset' => $offset,
            'meta_query' => array(
                array(
                    'key' => 'stm_car_user',
                    'value' => $user_id,
                    'compare' => '='
                )
            )
        );

        if ($popular) {
            $args['order'] = 'ASC';
            $args['orderby'] = 'stm_car_views';
        }

        $query = new WP_Query($args);
        wp_reset_postdata();

        return $query;

    }
}

if (!function_exists('stm_get_author_link')) {
    /**
     * Get author link
     *
     * @param string $id
     *
     * @return mixed|void
     */
    function stm_get_author_link($id = 'register')
    {

        if ($id == 'register') {
            $login_page = get_theme_mod('login_page', 1718);
            if (function_exists('icl_object_id')) {
                $id = icl_object_id($login_page, 'page', false, ICL_LANGUAGE_CODE);
                if (is_page($id)) {
                    $login_page = $id;
                }
            }

            $link = get_permalink($login_page);
        } else {
            if (empty($id) or $id == 'myself-view') {
                $user = wp_get_current_user();
                if (!is_wp_error($user)) {
                    $link = get_author_posts_url($user->data->ID);
                    if ($id == 'myself-view') {
                        $link = add_query_arg(array('view-myself' => 1), $link);
                    }
                } else {
                    $link = '';
                }
            } else {
                $link = get_author_posts_url($id);
            }
        }

        return apply_filters('stm_filter_author_link', $link);
    }
}

if (!function_exists('stm_get_add_page_url')) {
    /**
     * Get add a car page url
     *
     * @param string $edit
     * @param string $post_id
     *
     * @return mixed|void
     */
    function stm_get_add_page_url($edit = '', $post_id = '')
    {
        $page_id = get_theme_mod('user_add_car_page', 1755);
        $page_link = '';

        if (!empty($page_id)) {
            if (function_exists('icl_object_id')) {
                $id = icl_object_id($page_id, 'page', false, ICL_LANGUAGE_CODE);
                if (is_page($id)) {
                    $page_id = $id;
                }
            }

            $page_link = get_permalink($page_id);
        }

        $return_value = '';

        if ($edit == 'edit' and !empty($post_id)) {
            $return_value = esc_url(add_query_arg(array(
                'edit_car' => '1',
                'item_id' => intval($post_id)
            ), $page_link));
        } else {
            $return_value = esc_url($page_link);
        }

        return apply_filters('stm_filter_add_car_page_url', $return_value);
    }
}

if (!function_exists('stm_edit_delete_user_car')) {
    /**
     * Delete car, added by user
     */
    function stm_edit_delete_user_car()
    {

        $demo = stm_is_site_demo_mode();
        if (!$demo) {


            if (!empty($_GET['stm_disable_user_car'])) {
                $car = intval($_GET['stm_disable_user_car']);

                $author = get_post_meta($car, 'stm_car_user', true);
                $user = wp_get_current_user();

                if (intval($author) == intval($user->ID)) {
                    $status = get_post_status($car);
                    if ($status == 'publish') {
                        $disabled_car = array(
                            'ID' => $car,
                            'post_status' => 'draft'
                        );

                        wp_update_post($disabled_car);
                    }
                }
            }

            if (!empty($_GET['stm_enable_user_car'])) {
                $car = intval($_GET['stm_enable_user_car']);

                $author = get_post_meta($car, 'stm_car_user', true);
                $user = wp_get_current_user();

                if (intval($author) == intval($user->ID)) {
                    $status = get_post_status($car);
                    if ($status == 'draft') {
                        $disabled_car = array(
                            'ID' => $car,
                            'post_status' => 'publish'
                        );

                        $can_update = true;

                        if (stm_pricing_enabled()) {

                            $user_limits = stm_get_post_limits($user->ID);
                            if (!$user_limits['posts']) {
                                $can_update = false;
                            }
                        }

                        if ($can_update) {
                            wp_update_post($disabled_car);
                        } else {
                            add_action('wp_enqueue_scripts', 'stm_user_out_of_limit');
                            function stm_user_out_of_limit()
                            {
                                $field_limit = 'jQuery(document).ready(function(){';
                                $field_limit .= 'jQuery(".stm-no-available-adds-overlay, .stm-no-available-adds").removeClass("hidden");';
                                $field_limit .= 'jQuery(".stm-no-available-adds-overlay").click(function(){';
                                $field_limit .= 'jQuery(".stm-no-available-adds-overlay, .stm-no-available-adds").addClass("hidden")';
                                $field_limit .= '});';
                                $field_limit .= '});';
                                wp_add_inline_script('stm-theme-scripts', $field_limit);
                            }
                        }
                    }
                }
            }

            if (!empty($_GET['stm_move_trash_car'])) {
                $car = intval($_GET['stm_move_trash_car']);

                $author = get_post_meta($car, 'stm_car_user', true);
                $user = wp_get_current_user();

                if (intval($author) == intval($user->ID)) {
                    if (get_post_status($car) == 'draft' or get_post_status($car) == 'pending') {

                        wp_trash_post($car, false);

                    }
                }
            }
        }
    }

    add_action('wp', 'stm_edit_delete_user_car');
}

add_action('admin_footer', 'my_admin_add_js');
function my_admin_add_js() {
	echo '<script type="text/javascript">
		jQuery(".submitdelete").on("click", function(e) {
		    var del=confirm("Do you want to delete Listing images permanently?");
			if (del==true){
			    var date = new Date(new Date().getTime() + 10 * 1000);
			    document.cookie = "deleteListingAttach=delete; path=/; expires=" + date.toUTCString();
			}
		});
</script>';
}