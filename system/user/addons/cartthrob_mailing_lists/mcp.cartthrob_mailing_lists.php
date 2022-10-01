<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cartthrob_mailing_list_mcp
{
    private $module_name;
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
        $this->module_name = strtolower(str_replace(['_ext', '_mcp', '_upd'], '', __CLASS__));

        $this->EE = &get_instance();
        $this->EE->load->add_package_path(PATH_THIRD . $this->module_name . '/');
        include PATH_THIRD . $this->module_name . '/config' . EXT;

        $this->EE->load->add_package_path(PATH_THIRD . 'cartthrob/');
        $this->EE->load->library('cartthrob_loader');
    }

    private function initialize()
    {
        $this->params['module_name'] = $this->module_name;
        $this->params['nav'] = [
            'campaign_monitor' => [
                'campaign_monitor' => $this->EE->lang->line('nav_campaign_monitor'),
            ],
            'mailchimp' => [
                'mailchimp' => $this->EE->lang->line('nav_mailchimp'),
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

        $this->EE->load->library('mbr_addon_builder');
        $this->EE->mbr_addon_builder->initialize($this->params);
    }

    public function quick_save()
    {
        return $this->EE->mbr_addon_builder->quick_save();
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

        return $this->EE->mbr_addon_builder->load_view(__FUNCTION__, [], $structure);
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

        return $this->EE->mbr_addon_builder->load_view(__FUNCTION__, [], $structure);
    }

    public function index()
    {
        $this->initialize();
        if (isset($this->params['nav']['system_settings'])) {
            $method = 'system_settings';
        } else {
            $method = 'campaign_monitor';
        }
        $this->EE->functions->redirect(BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=' . $this->module_name . AMP . 'method=' . $method);
    }
}
