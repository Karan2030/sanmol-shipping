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
        $rate_cost = 0;
        $response = wp_remote_get('https://jsonplaceholder.typicode.com/posts/1');

        if (is_wp_error($response)) {
             $rate_cost = 99;  //if there's a error in the API call, we will set a default rate
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['title'])) {
                // we re calculating the rate based on the length of th etitle
                $rate_cost = strlen($data['title']);
            } else {
                $rate_cost = 88; //if there's a error in the API call, we will set a default rate
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
