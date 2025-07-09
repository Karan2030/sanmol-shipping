<?php

/**
 * Plugin Name: SanShip Express Shipping
 * Description: Adds the SanShip Express shipping method to WooCommerce with mock API integration.
 * Version: 1.0.0
 * Author: Karan
 * Text Domain: sanship-express
 */

if (! defined('ABSPATH')) {
    exit;
}

// if woocommerce is active tehn only run this code
add_action('woocommerce_shipping_init', 'sanship_shipping_method_init');

function sanship_shipping_method_init()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-sanship-shipping-method.php';
}

// for adding the shipping method to WooCommerce menu bar
add_filter('woocommerce_shipping_methods', 'add_sanship_shipping_method_function');

function add_sanship_shipping_method_function($methods)
{
    $methods['sanship'] = 'SanShip_Shipping_Method';
    return $methods;
}

// for adding the sanship settings page to WooCommerce settings
add_filter('woocommerce_get_settings_pages', 'sanship_add_settings_page');

function sanship_add_settings_page($settings)
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-sanship-settings.php';
    $settings[] = new WC_Settings_SanShip();
    return $settings;
}

// when an order is completed, we will call the mock API to get the tracking number
add_action('woocommerce_order_status_completed', 'after_complete_order', 10, 1);

function after_complete_order($order_id)
{
    $order = wc_get_order($order_id);

    if (! $order) {
        return;
    }

    if (get_post_meta($order_id, '_sanship_tracking_number', true)) {
        return;
    }

    $data = [
        'name'      => $order->get_formatted_billing_full_name(),
        'email'     => $order->get_billing_email(),
        'order_id'  => $order_id,
        'amount'    => $order->get_total(),
    ];

    // at present login creds are not required, there is a free api key for testing purposes

    $response = wp_remote_post('https://reqres.in/api/users', [
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key' => 'reqres-free-v1',
        ],
        'body' => wp_json_encode($data),
        'timeout' => 15, //caching the response for 15 seconds
    ]);

    if (is_wp_error($response)) {
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $json = json_decode($body, true);

    if (isset($json['id'])) {
        $tracking_number = 'SAN-' . strtoupper($json['id']);

        update_post_meta($order_id, '_sanship_tracking_number', $tracking_number);
        update_post_meta($order_id, '_sanship_tracking_link', 'https://track.example.com/' . $tracking_number);
    }
}

// this will be shown on the My Account page under Order Details
add_action('woocommerce_order_details_after_order_table', 'sanship_display_tracking_on_account_page_order', 10, 1);

function sanship_display_tracking_on_account_page_order($order)
{
    $order_id = $order->get_id();
    $tracking_number = get_post_meta($order_id, '_sanship_tracking_number', true);
    $tracking_link = get_post_meta($order_id, '_sanship_tracking_link', true);

    if (! empty($tracking_number)) {
        echo '<p><strong>' . __('SanShip Tracking Number:', 'sanship-express') . '</strong> ';
        echo '<a href="' . esc_url($tracking_link) . '" target="_blank">' . esc_html($tracking_number) . '</a></p>';
    }
}

// this will be shown in the customer order email
add_action('woocommerce_email_after_order_table', 'sanship_display_tracking_in_email', 20, 4);

function sanship_display_tracking_in_email($order, $sent_to_admin, $plain_text, $email)
{
    $order_id = $order->get_id();
    $tracking_number = get_post_meta($order_id, '_sanship_tracking_number', true);
    $tracking_link = get_post_meta($order_id, '_sanship_tracking_link', true);

    if (! empty($tracking_number)) {
        $output = '<p><strong>' . __('SanShip Tracking Number:', 'sanship-express') . '</strong> ';
        $output .= '<a href="' . esc_url($tracking_link) . '">' . esc_html($tracking_number) . '</a></p>';

        echo wp_kses_post($output);
    }
}

// this is used to show thw tracing no and link in the order detail page.
add_action('woocommerce_admin_order_data_after_shipping_address', 'sanship_showing_admin_tracking_info', 10, 1);

function sanship_showing_admin_tracking_info($order)
{
    $order_id = $order->get_id();
    $tracking_number = get_post_meta($order_id, '_sanship_tracking_number', true);
    $tracking_link = get_post_meta($order_id, '_sanship_tracking_link', true);

    if (!empty($tracking_number)) {
        echo '<p><strong>' . __('SanShip Tracking #:', 'sanship-express') . '</strong> ';
        echo '<a href="' . esc_url($tracking_link) . '" target="_blank">' . esc_html($tracking_number) . '</a></p>';

        echo '<p><a class="button" target="_blank" href="' . esc_url(plugin_dir_url(__FILE__) . 'assets/dummy-label.pdf') . '">';
        echo __('Download Shipping Label', 'sanship-express');
        echo '</a></p>';
    }
}
