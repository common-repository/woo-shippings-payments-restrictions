<?php

/**
 * Admin settings in WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class WCSPRSettings extends WC_Settings_Page {

  protected $id;

  public function __construct()
  {
    $this->id = 'wcspr';

    $this->label = __( 'WCSPR', 'wcspr' );

    add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 30 );

    add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );

    add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );

    add_action('woocommerce_settings_tabs_' . $this->id, array($this, 'addSectionToTab'));

    add_action('woocommerce_update_options_' . $this->id, array($this, 'updateOptions'));
  }

  /**
  * Output the settings
  */
  public function output() {

    global $current_section;

    $settings = $this->get_settings( $current_section );
    WC_Admin_Settings::output_fields( $settings );
  }

  /**
  * Create input field for every available (shpping method, payment gateway) combination.
  *
  * @return $fields array
  */
  public function createFields()
  {

    $available_shipping_methods = WC()->shipping->get_shipping_methods();

    $available_payment_gateways = WC()->payment_gateways->payment_gateways();

    $fields = array();

    foreach($available_shipping_methods as $method) {

      foreach($available_payment_gateways as $gateway) {

         $fields[] = array(
           'title'   => 'Disable ' . ($gateway->method_title ? $gateway->method_title : $gateway->id) . ' for ' . ($method->method_title ? $method->method_title : $method->id),
           'type'    => 'checkbox',
           'id'      => $this->id . '_' . $method->id . '_' . $gateway->id,
         );

      }
    }

    return $fields;
  }


  /**
  * Create section and include input fields in section
  *
  * @return array
  */
  public function createTabSection()
  {
    $section = array();

    $section[] = array(
      'title' => __( 'Shippings Payments Restrictions', 'wcspr' ),
      'desc'  => __( 'Disable Payment gateways for certian Shipping methods', 'wcspr' ),
      'type'  => 'title',
      'id'    => $this->id,
    );

    $section = array_merge($section, $this->createFields());

    $section[] = array( 'type' => 'sectionend', 'id' => $this->id);

    return $section;
  }


  /**
  * Add section to tab
  */
  public function addSectionToTab()
  {
    woocommerce_admin_fields($this->createTabSection());
  }

  /**
  *  Update setting fields
  */
  public function updateOptions()
  {
    woocommerce_update_options($this->createFields());
  }
}
