<?php

use ExpressionEngine\Service\Addon\Installer;

class Cartthrob_mailing_list_ext extends Installer
{
    public $module_name = 'cartthrob_mailing_lists';

    public $methods = [
        [
            'hook' => 'cartthrob_on_authorize'
        ]
    ];


    public $settings = [];
    public $description = 'Generic Description';
    public $docs_url = 'http://cartthrob.com';
    public $name = 'Cartthrob Addon';
    public $settings_exist = 'n';
    public $version;
    public $required_by = ['module'];
    public $testing = false; // either FALSE, or 2 char country code.

    private $remove_keys = [
        'name',
        'submit',
        'x',
        'y',
        'templates',
        'XID',
    ];

    /**
     * Constructor
     *
     * @param 	mixed	settings array or empty string if none exist
     */
    public function __construct($settings = '')
    {
//        $this->EE->lang->loadfile($this->module_name);
//
//        include PATH_THIRD . $this->module_name . '/config.php';
//        $this->name = $config['name'];
//        $this->version = $config['version'];
//        $this->description = lang($this->module_name . '_description');
//
//        $this->EE->load->add_package_path(PATH_THIRD . 'cartthrob/');
//        $this->EE->load->add_package_path(PATH_THIRD . $this->module_name . '/');
//
//        $this->params = [
//            'module_name' => $this->module_name,
//            ];

//        $this->EE->load->library('mbr_addon_builder');
//        $this->EE->mbr_addon_builder->initialize($this->params);

        $this->settings = ee('cartthrob:SettingsService')->settings($this->module_name);
    }

    // ----------------------------------------------------------------------

    /**
     * Activate Extension
     *
     * This function enters the extension into the exp_extensions table
     *
     * @return void
     */
    public function activate_extension()
    {
        parent::activate_extension();
    }

    public function update_extension($current = '')
    {
//        return $this->EE->mbr_addon_builder->update_extension($current);
    }

    public function settings(): array
    {
        return [];
    }
}