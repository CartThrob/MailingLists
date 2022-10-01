<?php

namespace CartThrob\MailingLists;

use CartThrob\Exceptions\CartThrobException;
use CartThrob\HasVariables;
use Illuminate\Support\Str;

class Module
{
    use HasVariables;

    public $module_name = 'cartthrob_mailing_lists';
    public $cartthrob;
    public $settings = [];

    public function __construct()
    {
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @throws CartThrobException
     */
    public function __call($method, $args)
    {
        ee()->load->library('cartthrob_addons');

        if (str_ends_with($method, 'action')) {
            if (!ee()->input->get_post('ACT')) {
                show_404();
            }

            $className = 'CartThrob\\MailingLists\\Actions\\' . Str::studly($method);
        } else {
            $className = 'CartThrob\\MailingLists\\Tags\\' . Str::studly($method) . 'Tag';
        }

        if (class_exists($className)) {
            return cartthrob($className)->process();
        } elseif (ee()->cartthrob_addons->method_exists($method)) {
            return ee()->cartthrob_addons->call($method);
        } else {
            throw new CartThrobException("Call to undefined method Cartthrob::$method()");
        }
    }
}
