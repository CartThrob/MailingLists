<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cartthrob_mailing_list_upd
{
	public $module_name;
	private $mod_actions = array(
		#'tracker_action',
	);
	private $mcp_actions = array(
	);
	private $fieldtypes = array(
	);
	private $hooks = array(
		array('cartthrob_on_authorize'),
		array('cartthrob_pre_process'),
  	);
	public $version;
	public $current;
	public $notification_events =  array(  );
	
	private $tables = array(
   		'cartthrob_mailing_list_settings' => array(
			'site_id' => array(
				'type' => 'int',
				'constraint' => 4,
				'default' => '1',
			),
			'`key`' => array(
				'type' => 'varchar',
				'constraint' => 255,
			),
			'value' => array(
				'type' => 'text',
				'null' => TRUE,
			),
			'serialized' => array(
				'type' => 'int',
				'constraint' => 1,
				'null' => TRUE,
			),
		),
 
	);
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->module_name = strtolower(str_replace(array('_ext', '_mcp', '_upd'), "", __CLASS__));
		
		include PATH_THIRD.$this->module_name.'/config'.EXT;
		$this->version = $config['version'];

		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/'); 
		$this->EE->load->add_package_path(PATH_THIRD.$this->module_name.'/');
		
		$this->params = array(
			'module_name'	=> $this->module_name, 
			'current'       => $this->current ,
			'version'       => $this->version ,
			'hooks'         => $this->hooks ,
			'fieldtypes'    => $this->fieldtypes ,
			'mcp_actions'   => $this->mcp_actions ,
			'mod_actions'   => $this->mod_actions ,
			'tables'		=> $this->tables,
			'notification_events'=> $this->notification_events,
			); 
			
  		$this->EE->load->library('mbr_addon_builder');
		$this->EE->mbr_addon_builder->initialize($this->params);
		
	}
 	public function install()
	{
		return $this->EE->mbr_addon_builder->install($has_cp_backend = "y", $has_publish_fields = "n"); 
	}
	function update($current = '')
	{
		return $this->EE->mbr_addon_builder->update($current); 
	}
	public function uninstall()
	{
		return $this->EE->mbr_addon_builder->uninstall(); 
	}
}