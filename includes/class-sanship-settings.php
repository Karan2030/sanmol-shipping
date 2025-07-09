<?php

if (! defined('ABSPATH')) {
    exit;
}

class WC_Settings_SanShip extends WC_Settings_Page
{

    public function __construct()
    {
        $this->id    = 'sanship_settings';
        $this->label = __('SanShip Express', 'sanship-express');

        parent::__construct();
    }

    // this option is used to initialize the settings page and also this options ids are added to db.
    public function get_settings()
    {
        $settings = [
            [
                'title' => __('SanShip API Settings', 'sanship-express'),
                'type'  => 'title',
                'id'    => 'sanship_api_settings'
            ],
            [
                'title'    => __('Client ID', 'sanship-express'),
                'id'       => 'sanship_client_id',
                'type'     => 'text',
                'desc'     => __('Enter your SanShip API Client ID.', 'sanship-express'),
                'default'  => '',
                'desc_tip' => true,
            ],
            [
                'title'    => __('Client Secret', 'sanship-express'),
                'id'       => 'sanship_client_secret',
                'type'     => 'password',
                'desc'     => __('Enter your SanShip API Secret.', 'sanship-express'),
                'default'  => '',
                'desc_tip' => true,
            ],
            [
                'title'    => __('Environment', 'sanship-express'),
                'id'       => 'sanship_environment',
                'type'     => 'select',
                'default'  => 'sandbox',
                'options'  => [
                    'sandbox'    => __('Sandbox', 'sanship-express'),
                    'production' => __('Production', 'sanship-express')
                ],
            ],
            [
                'type' => 'sectionend',
                'id'   => 'sanship_api_settings'
            ],
        ];

        return apply_filters('woocommerce_get_settings_' . $this->id, $settings);
    }

    public function output()
    {
        WC_Admin_Settings::output_fields($this->get_settings());
    }

    public function save()
    {
        WC_Admin_Settings::save_fields($this->get_settings());
    }
}
