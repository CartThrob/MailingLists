<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cartthrob_mailing_list_mcp {

	private $module_name;
	public $required_settings = array();
	public $template_errors = array();
	public $templates_installed = array();
	public $extension_enabled = 0;
	public $module_enabled = 0;
	public $required_by 	= array('extension');
	
	public $version;
	
	private $initialized = FALSE;
	
	public $nav = array(
	);
	
	public $no_nav = array(
	); 
 	private $remove_keys = array(
		'name',
		'submit',
		'x',
		'y',
		'templates',
		'XID',
	);
	public $params; 
	
	public $cartthrob, $store, $cart;
	
	
    function __construct()
    {
		$this->module_name = strtolower(str_replace(array('_ext', '_mcp', '_upd'), "", __CLASS__));
	
        $this->EE =& get_instance();
		$this->EE->load->add_package_path(PATH_THIRD.$this->module_name.'/');
		include PATH_THIRD.$this->module_name.'/config'.EXT;
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/'); 
		$this->EE->load->library('cartthrob_loader'); 
    }

	private function initialize()
	{
		$this->params['module_name']	= $this->module_name; 
 	 	$this->params['nav'] = array(
 			'campaign_monitor' => array(
				'campaign_monitor' => $this->EE->lang->line('nav_campaign_monitor'),
			),
			'mailchimp' => array(
				'mailchimp' => $this->EE->lang->line('nav_mailchimp'),
			),
 			//  need to add reset method for processing transactions. 
			
		); 
		
 		$this->params['no_form'] = array(
			'edit',
			'delete',
			'add',
			'view',
  		);
		$this->params['no_nav'] = array(
			'edit',
			'delete'
		);
		
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
		
		$structure['class']	= 'campaign_monitor'; 
		$structure['description']	='You must use your main Campaign Monitor account API key, and your client\'s API Client ID, and the List ID for each list you wish to send to. For more information on where to find this information in your Campaign Monitor settings <a href="http://www.campaignmonitor.com/api/getting-started/">click here &raquo;</a>. In your checkout form, you must add an input field called "custom_data[campaign_monitor]" with the list ID that you want to send to.'; 
		$structure['caption']	=''; 
		$structure['title']	= "campaign_monitor"; 
	 	$structure['settings'] = array(
			array(
				'name' => 'api_key',
				'short_name' => 'api_key',
				'type' => 'text',
			),
			array(
				'name' => 'require_double_opt_in',
				'short_name' => 'campaign_monitor_require_double_opt_in',
				'type' => 'select',
				'options'=> array("no", "yes"),
				'default'	=> 'no',
			),
	 	);
 	 	return $this->EE->mbr_addon_builder->load_view(__FUNCTION__, array(), $structure);
	}
	public function mailchimp()
	{
		$this->initialize();
		
		$structure['class']	= 'mailchimp'; 
		$structure['description']	='In your checkout form, you must add an input field called "custom_data[mailchimp]" with the list ID that you want to send to.'; 
		$structure['caption']	=''; 
		$structure['title']	= "mailchimp"; 
	 	$structure['settings'] = array(
			array(
				'name' => 'api_key',
				'short_name' => 'mailchimp_api_key',
				'type' => 'text',
			),
			array(
				'name' => 'require_double_opt_in',
				'short_name' => 'mailchimp_require_double_opt_in',
				'type' => 'select',
				'options'=> array("no", "yes"),
				'default'	=> 'no',
			),
 	 	);
	 	return $this->EE->mbr_addon_builder->load_view(__FUNCTION__, array(), $structure);
	}
 
	
	public function index()
	{
		$this->initialize();
		if (isset($this->params['nav']['system_settings']))
		{
			$method = "system_settings"; 
 			
		}
		else
		{
 			$method = "campaign_monitor"; 
		}
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.$method);
	}
 
 
}