<?php

if (! defined('ABSPATH')) {
    exit;
}

class SanShip_Shipping_Method extends WC_Shipping_Method
{

    public function __construct()
    {
        $this->id                 = 'sanship';
        $this->method_title       = __('SanShip Express', 'sanship-express');
        $this->method_description = __('Custom shipping method using mock API.', 'sanship-express');

        $this->enabled            = "yes";
        $this->title              = __('SanShip Express', 'sanship-express');

        $this->init();
    }

    public function init()
    {
        // for Loading the settings API
        $this->init_form_fields();
        $this->init_settings();

        // for Saving the settings
        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title'   => __('Enable', 'sanship-express'),
                'type'    => 'checkbox',
                'label'   => __('Enable SanShip Express', 'sanship-express'),
                'default' => 'yes'
            ],
            'title' => [
                'title'       => __('Method Title', 'sanship-express'),
                'type'        => 'text',
                'description' => __('Title to display during checkout.', 'sanship-express'),
                'default'     => __('SanShip Express', 'sanship-express'),
            ]
        ];
    }

    public function calculate_shipping($package = [])
    {
        // Step 1: Admin login to get token
        $login_response = wp_remote_post('https://nci.ky/api/login/ThirdPartyLogin', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'email' => defined('NCI_API_EMAIL') ? NCI_API_EMAIL : '',
                'password' => defined('NCI_API_PASSWORD') ? NCI_API_PASSWORD : '',
            ]),
        ]);

        if (is_wp_error($login_response)) {
            $rate_cost = 99; // fallback rate
        } else {
            $login_body = wp_remote_retrieve_body($login_response);
            $login_data = json_decode($login_body, true);
            if (!empty($login_data['token'])) {
                $token = $login_data['token'];

                // Get delivery fee using token
                $pickup_region = '1';
                $dest_region = '2';
                $roundtrip = '1';
                $pkg_type = 'small';
                $service_type = 'express';

                $fee_response = wp_remote_get("https://nci.ky/api/dd/Get_DeliveryFee?pickup_region={$pickup_region}&dest_region={$dest_region}&roundtrip={$roundtrip}&pkg_type={$pkg_type}&service_type={$service_type}", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                    ],
                ]);
                if (is_wp_error($fee_response)) {
                    $rate_cost = 88; // fallback
                } else {
                    $fee_body = wp_remote_retrieve_body($fee_response);
                    $fee_data = json_decode($fee_body, true);

                    if (!empty($fee_data['data'])) {
                        $rate_cost = floatval($fee_data['data']['fee']);
                    } else {
                        $rate_cost = 88; // fallback
                    }
                }
            } else {
                $rate_cost = 77; // fallback if token not received
            }
        }

        $rate = [
            'id'       => $this->id,
            'label'    => $this->title,
            'cost'     => $rate_cost,
            'calc_tax' => 'per_order',
        ];

        $this->add_rate($rate);
    }
}
