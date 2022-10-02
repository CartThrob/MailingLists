<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cartthrob_mailing_lists_mcp
{
    private $module_name = "cartthrob_mailing_lists";
    public $required_settings = [];
    public $template_errors = [];
    public $templates_installed = [];
    public $extension_enabled = 0;
    public $module_enabled = 0;
    public $required_by = ['extension'];

    public $version;

    private $initialized = false;

    public $nav = [
    ];

    public $no_nav = [
    ];
    private $remove_keys = [
        'name',
        'submit',
        'x',
        'y',
        'templates',
        'XID',
    ];
    public $params;

    public $cartthrob;

    public $store;

    public $cart;

    public function __construct()
    {
        ee()->load->add_package_path(PATH_THIRD . $this->module_name . '/');
        include PATH_THIRD . $this->module_name . '/config.php';

        ee()->load->add_package_path(PATH_THIRD . 'cartthrob/');
        ee()->load->library('cartthrob_loader');
    }

    private function initialize()
    {
        $this->params['module_name'] = $this->module_name;
        $this->params['nav'] = [
            'campaign_monitor' => [
                'campaign_monitor' => ee()->lang->line('nav_campaign_monitor'),
            ],
            'mailchimp' => [
                'mailchimp' => ee()->lang->line('nav_mailchimp'),
            ],
            //  need to add reset method for processing transactions.
        ];

        $this->params['no_form'] = [
            'edit',
            'delete',
            'add',
            'view',
        ];
        $this->params['no_nav'] = [
            'edit',
            'delete',
        ];

        ee()->load->library('mbr_addon_builder');
        ee()->mbr_addon_builder->initialize($this->params);
    }

    public function quick_save()
    {
        return ee()->mbr_addon_builder->quick_save();
    }

    public function campaign_monitor()
    {
        $this->initialize();

        $structure['class'] = 'campaign_monitor';
        $structure['description'] = 'campaign_monitor_instructions';
        $structure['caption'] = '';
        $structure['title'] = 'campaign_monitor';
        $structure['settings'] = [
            [
                'name' => 'api_key',
                'short_name' => 'api_key',
                'type' => 'text',
            ],
            [
                'name' => 'require_double_opt_in',
                'short_name' => 'campaign_monitor_require_double_opt_in',
                'type' => 'select',
                'options' => ['no', 'yes'],
                'default' => 'no',
            ],
        ];

        return ee()->mbr_addon_builder->load_view(__FUNCTION__, [], $structure);
    }

    public function mailchimp()
    {
        $this->initialize();

        $structure['class'] = 'mailchimp';
        $structure['description'] = 'mailchimp_instructions';
        $structure['caption'] = '';
        $structure['title'] = 'mailchimp';
        $structure['settings'] = [
            [
                'name' => 'api_key',
                'short_name' => 'mailchimp_api_key',
                'type' => 'text',
            ],
            [
                'name' => 'require_double_opt_in',
                'short_name' => 'mailchimp_require_double_opt_in',
                'type' => 'select',
                'options' => ['no', 'yes'],
                'default' => 'no',
            ],
        ];

        return ee()->mbr_addon_builder->load_view(__FUNCTION__, [], $structure);
    }

    public function index()
    {
        $this->initialize();
        if (isset($this->params['nav']['system_settings'])) {
            $method = 'system_settings';
        } else {
            $method = 'campaign_monitor';
        }
        ee()->functions->redirect(BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=' . $this->module_name . AMP . 'method=' . $method);
    }
}
