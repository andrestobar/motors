<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$stock_number = get_post_meta(get_the_id(), 'stock_number', true);
$car_brochure = get_post_meta(get_the_ID(), 'car_brochure', true);

$certified_logo_1 = get_post_meta(get_the_ID(), 'certified_logo_1', true);
$history_link_1 = get_post_meta(get_the_ID(), 'history_link', true);

$certified_logo_2 = get_post_meta(get_the_ID(), 'certified_logo_2', true);
$certified_logo_2_link = get_post_meta(get_the_ID(), 'certified_logo_2_link', true);

//Show car actions
$show_stock = get_theme_mod('show_listing_stock', true);
$show_test_drive = get_theme_mod('show_listing_test_drive', false);
$show_share = get_theme_mod('show_listing_share', false);
$show_pdf = get_theme_mod('show_listing_pdf', false);
$show_certified_logo_1 = get_theme_mod('show_listing_certified_logo_1', true);
$show_certified_logo_2 = get_theme_mod('show_listing_certified_logo_2', true);

?>

<div class="single-car-actions">
    <ul class="list-unstyled clearfix">

        <!--Stock num-->
        <?php if (!empty($stock_number) and !empty($show_stock) and $show_stock): ?>
            <li>
                <div class="stock-num heading-font"><span><?php esc_html_e('stock', 'stm_vehicles_listing'); ?>
                        # </span><?php echo esc_attr($stock_number); ?></div>
            </li>
        <?php endif; ?>

        <!--Schedule-->
        <?php if (!empty($show_test_drive) and $show_test_drive): ?>
            <li>
                <a href="#" class="car-action-unit stm-schedule" data-toggle="modal" data-target="#test-drive"
                   onclick="stm_test_drive_car_title(<?php echo esc_js(get_the_ID()); ?>, '<?php echo esc_js(get_the_title(get_the_ID())) ?>')">
                    <i class="stm-icon-steering_wheel"></i>
                    <?php esc_html_e('Schedule Test Drive', 'stm_vehicles_listing'); ?>
                </a>
            </li>
        <?php endif; ?>

        <!--PDF-->
        <?php if (!empty($show_pdf) and $show_pdf): ?>
            <?php if (!empty($car_brochure)): ?>
                <li>
                    <a
                        href="<?php echo esc_url(wp_get_attachment_url($car_brochure)); ?>"
                        class="car-action-unit stm-brochure"
                        title="<?php esc_html_e('Download brochure', 'stm_vehicles_listing'); ?>"
                        download>
                        <i class="stm-icon-brochure"></i>
                        <?php esc_html_e('Car brochure', 'stm_vehicles_listing'); ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>


        <!--Share-->
        <?php if (!empty($show_share) and $show_share): ?>
            <li class="stm-shareble">
                <span class="st_sharethis_large" displaytext=""></span>
                <script type="text/javascript">var switchTo5x = true;</script>
                <script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
                <script type="text/javascript">if (typeof stLight != undefined) { stLight.options({doNotHash: false, doNotCopy: false, hashAddressBar: false, onhover: false}); }</script>
                <a
                    href="#"
                    class="car-action-unit stm-share"
                    data-url="<?php echo get_the_permalink(get_the_ID()); ?>"
                    title="<?php esc_html_e('Share this', 'stm_vehicles_listing'); ?>"
                    download>
                    <i class="stm-icon-share"></i>
                    <?php esc_html_e('Share this', 'stm_vehicles_listing'); ?>
                </a>
            </li>
        <?php endif; ?>

        <!--Certified Logo 1-->
        <?php if (!empty($certified_logo_1) and !empty($show_certified_logo_1) and $show_certified_logo_1):
            if ($certified_logo_1 == 'automanager_default') {
                $certified_logo_1 = array();
                $certified_logo_1[0] = get_template_directory_uri() . '/assets/images/carfax.png';
            } else {
                $certified_logo_1 = wp_get_attachment_image_src($certified_logo_1, 'stm-img-255-135');
            }
            if (!empty($certified_logo_1[0])) {
                $certified_logo_1 = $certified_logo_1[0];

                ?>

                <li class="certified-logo-1">
                    <?php if (!empty($history_link_1)): ?>
                    <a href="<?php echo esc_url($history_link_1); ?>" target="_blank">
                        <?php endif; ?>
                        <img src="<?php echo esc_url($certified_logo_1); ?>"
                             alt="<?php esc_html_e('Logo 1', 'stm_vehicles_listing'); ?>"/>
                        <?php if (!empty($history_link_1)): ?>
                    </a>
                <?php endif; ?>
                </li>


            <?php } ?>
        <?php endif; ?>

        <!--Certified Logo 2-->
        <?php if (!empty($certified_logo_2) and !empty($show_certified_logo_2) and $show_certified_logo_2): ?>
            <?php
            $certified_logo_2 = wp_get_attachment_image_src($certified_logo_2, 'stm-img-255-135');
            if (!empty($certified_logo_2[0])) {
                $certified_logo_2 = $certified_logo_2[0]; ?>


                <li class="certified-logo-2">
                    <?php if (!empty($certified_logo_2_link)): ?>
                    <a href="<?php echo esc_url($certified_logo_2_link); ?>" target="_blank">
                        <?php endif; ?>
                        <img src="<?php echo esc_url($certified_logo_2); ?>"
                             alt="<?php esc_html_e('Logo 2', 'stm_vehicles_listing'); ?>"/>
                        <?php if (!empty($certified_logo_2_link)): ?>
                    </a>
                <?php endif; ?>
                </li>

            <?php } ?>
        <?php endif; ?>

    </ul>
</div>