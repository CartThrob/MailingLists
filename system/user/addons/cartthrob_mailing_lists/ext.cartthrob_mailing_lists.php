<?php

use CartThrob\Exceptions\CartThrobException;
use ExpressionEngine\Service\Addon\Installer;
use Illuminate\Support\Str;

class Cartthrob_mailing_lists_ext extends Installer
{
    private $module_name = 'cartthrob_mailing_lists';

    public $methods = [
        [
            'hook' => 'cartthrob_on_authorize',
        ],
        [
            'hook' => 'cp_menu_array',
        ],
    ];

    /**
     * @param $settings
     */
    public function __construct($settings = [])
    {
        parent::__construct($settings);
        ee()->lang->loadfile($this->module_name);
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @throws CartThrobException
     */
    public function __call($method, $args)
    {
        $className = 'CartThrob\\MailingLists\\Hooks\\' . Str::studly($method) . 'Hook';

        if (class_exists($className)) {
            return cartthrob($className)->process();
        } else {
            throw new CartThrobException("Call to undefined method Cartthrob::$method()");
        }
    }

    public function activate_extension()
    {
        parent::activate_extension();
    }

    public function update_extension($current = '')
    {
        return $this->EE->mbr_addon_builder->update_extension($current);
    }

    public function settings(): array
    {
        return [];
    }
}
