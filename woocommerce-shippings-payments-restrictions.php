<?php
/**
 * Plugin Name: WooCommerce - Shippings Payments Restrictions
 * Description: Choose which payment gateways will be disabled when certain shipping method selected
 * Version:     1.0.0
 * Author:      Sergey I.Grachyov aka Takereal
 * Author URI:  https://takereal.com
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wcspr
 * WC requires at least: 3.4.0
 * WC tested up to: 3.5.3
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WoocommerceShippingsPaymentsRestrictions {

  private $id;

  public function __construct()
  {
    $this->id = 'wcspr';

    if( is_admin() ) {

      add_filter('woocommerce_get_settings_pages', array($this, 'loadSettings'), 19);
    }
    else {

      // check if ajax request
      if( (isset($_REQUEST['wc-ajax']) && 'update_order_review' == $_REQUEST['wc-ajax']) ) {

        add_filter( 'woocommerce_available_payment_gateways', array($this, 'available_payment_gateways'), 10, 1 );
      }

      // check if pay_for page
      if( isset( $_GET['pay_for_order'] ) &&  true == $_GET['pay_for_order'] ) {
        
        add_filter( 'woocommerce_available_payment_gateways', array( $this, 'available_payment_gateways_after_cancelation'), 10, 1 );
      }
    }
  }

  /**
  * Load admin settings
  */
  public function loadSettings()
  {
    require 'includes/admin/WCSPRSettings.php';
    return new WCSPRSettings();
  }

  /**
  * List through available payment gateways,
  * check if certain payment gateway is disabled for selected Shipping method,
  * if 'yes', unset it from $payment_gateways array
  *
  * @return array with updated list of available payment gateways
  */
  public function available_payment_gateways($payment_gateways)
  {
    $chosen_shipping_rates = WC()->session->get( 'chosen_shipping_methods' );

    if ($chosen_shipping_rates && is_array($chosen_shipping_rates)) {

      foreach ($chosen_shipping_rates as $shipping_rate) {

        $a = explode(':', $shipping_rate);

        $shipping_method_id = $a[0];

        foreach ($payment_gateways as $gateway) {

          $name = $this->id . '_' . $shipping_method_id . '_' . $gateway->id;

          if ( 'yes' == get_option($name) ) {
            
            //
            // This Payment gateway is Disabled for selected Shipping method.
            // 
            unset($payment_gateways[$gateway->id]);
          }
        }

        break;
      }
    }

    return $payment_gateways;
  }

  /**
  * List through available payment gateways,
  * if customer gets redirected to the pay_for page
  * after a payment cancellation
  *
  * @return array with updated list of available payment gateways
  */
  public function available_payment_gateways_after_cancelation( $payment_gateways )
  {
    $order_id = wc_get_order_id_by_order_key( $_GET['key'] );
    $order = new WC_Order($order_id);

    $shipping_method = $order->get_shipping_methods();
    $shipping_method_id = array_shift(array_slice($shipping_method, 0, 1));

    foreach ($payment_gateways as $gateway) {

      $name = $this->id . '_' . $shipping_method_id . '_' . $gateway->id;

      if ( 'yes' == get_option($name) ) {
        
        //
        // This Payment gateway is Disabled for selected Shipping method.
        // 
        unset($payment_gateways[$gateway->id]);
      }
    }

    return $payment_gateways;
  }
}

/**
 * Check if WooCommerce is active
 **/
if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
  require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
  // Plugin is activated
  new WoocommerceShippingsPaymentsRestrictions();
}
