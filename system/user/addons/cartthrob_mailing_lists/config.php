<?php

if (!defined('CT_MAILING_LIST_VERSION')) {
    define('CT_MAILING_LIST_VERSION', '1.0.2');
}

if (defined('PATH_THEMES')) {
    if (!defined('PATH_THIRD_THEMES')) {
        define('PATH_THIRD_THEMES', PATH_THEMES . 'third_party/');
    }

    if (!defined('URL_THIRD_THEMES')) {
        define('URL_THIRD_THEMES', get_instance()->config->slash_item('theme_folder_url') . 'third_party/');
    }
}

$config['name'] = 'CartThrob Mailing List';
$config['version'] = CT_MAILING_LIST_VERSION;
$config['cartthrob_mailing_list'] = 'Integrates CartThrob with third party mailing lists.';
$config['nsm_addon_updater']['versions_xml'] = 'http://cartthrob.com/versions.xml';
