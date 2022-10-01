<?php

if (defined('PATH_THIRD')) {
    require_once PATH_THIRD . 'cartthrob_mailing_lists/vendor/autoload.php';
}

const CARTTHROB_MAILING_LISTS_NAME = 'CartThrob Mailing Lists';
const CARTTHROB_MAILING_LISTS_VERSION = '2.0.0';

return [
    'author' => 'CartThrob',
    'author_url' => 'https://cartthrob.com',
    'name' => CARTTHROB_MAILING_LISTS_NAME,
    'description' => 'CartThrob Mailing List Integration Add-On',
    'version' => CARTTHROB_MAILING_LISTS_VERSION,
    'namespace' => 'CartThrob\MailingLists',
    'settings_exist' => true,
];
