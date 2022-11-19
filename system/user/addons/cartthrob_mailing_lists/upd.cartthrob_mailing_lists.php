<?php

use ExpressionEngine\Service\Addon\Installer;

class Cartthrob_mailing_lists_upd extends Installer
{
    public string $module_name = 'cartthrob_mailing_lists';

    public $actions = [];

    public $methods = [];

    public $has_cp_backend = 'y';
    public $has_publish_fields = 'n';

    public $version;
    public $current;

    private array $tables = [
        'cartthrob_mailing_list_settings' => [
            'site_id' => [
                'type' => 'int',
                'constraint' => 4,
                'default' => '1',
            ],
            '`key`' => [
                'type' => 'varchar',
                'constraint' => 255,
            ],
            'value' => [
                'type' => 'text',
                'null' => true,
            ],
            'serialized' => [
                'type' => 'int',
                'constraint' => 1,
                'null' => true,
            ],
        ],
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function install(): bool
    {
        ee()->load->add_package_path(PATH_THIRD . 'cartthrob/');

        ee()->load->model('table_model');
        ee()->table_model->update_tables($this->tables);

        ee()->load->remove_package_path(PATH_THIRD . 'cartthrob/');

        return parent::install();
    }

    /**
     * @param $current
     */
    public function update($current = ''): bool
    {
//        if (version_compare($current, '2.0', '=')) {
//
//        }

        return true;
    }

    public function uninstall(): bool
    {
        foreach ($this->tables as $table => $definition) {
            ee()->load->dbforge();

            if (ee()->db->table_exists($table)) {
                ee()->dbforge->drop_table($table);
            }
        }

        return parent::uninstall();
    }
}
