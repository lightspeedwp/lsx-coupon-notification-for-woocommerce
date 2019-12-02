<?php
/**
 * Plugin Name: LSX Coupon Notification for WooCommserce
 * Plugin URI:  https://github.com/lightspeeddevelopment/lsx-coupon-notification-for-woocommerce
 * Description: Plugin for adding a custom WooCommerce email that sends users notifications about their coupons.
 * Author:      LightSpeed
 * Version:     0.0.1
 * Author URI:  https://www.lsdev.biz/
 * License:     GPL3
 * Text Domain: lsx-cnw
 * Domain Path: /languages/
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('LSX_CNW_PATH', plugin_dir_path(__FILE__));

/**
 *  Add a custom email to the list of emails WooCommerce should load
 *
 * @since 0.1
 * @param array $email_classes available email classes
 * @return array filtered available email classes
 */
function lsx_cnw_add_coupon_notification_email($email_classes)
{

    // include our custom email class
    require_once 'classes/class-lsx-cnw-notification-email.php';

    // add the email class to the list of email classes that WooCommerce loads
    $email_classes['CouponNotificationEmail'] = new lsx_cnw\classes\CouponNotificationEmail();

    return $email_classes;
}

add_filter('woocommerce_email_classes', 'lsx_cnw_add_coupon_notification_email');
