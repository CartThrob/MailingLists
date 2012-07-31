<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Price field Changer for CartThrob Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Chris Newton (MIghtyBigRobot)
 * @link		http://cartthrob.com
 */

class Cartthrob_mailing_list_ext {
	
	public $settings 		= array();
	public $description		= "Generic Description";
	public $docs_url		= 'http://cartthrob.com';
	public $name			= "Cartthrob Addon";
	public $settings_exist	= 'n';
	public $version; 
	public $required_by 	= array('module');
	public $testing 		= FALSE; // either FALSE, or 2 char country code.  
 	private $module_name; 
	private $remove_keys = array(
		'name',
		'submit',
		'x',
		'y',
		'templates',
		'XID',
	);
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->module_name = strtolower(str_replace(array('_ext', '_mcp', '_upd'), "", __CLASS__));
		
		$this->EE->lang->loadfile($this->module_name);
		
		include PATH_THIRD.$this->module_name.'/config'.EXT;
		$this->name= $config['name']; 
		$this->version = $config['version'];
		$this->description = lang($this->module_name. "_description"); 
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->add_package_path(PATH_THIRD.$this->module_name."/"); 

		$this->params = array(
			'module_name'	=> $this->module_name,
			); 
 		$this->EE->load->library('mbr_addon_builder');
		$this->EE->mbr_addon_builder->initialize($this->params);
		
		$this->EE->load->library('get_settings');
		$this->settings = $this->EE->get_settings->settings($this->module_name);
		
 		
	}
	

	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		return $this->EE->mbr_addon_builder->activate_extension(); 
	}	
 
	public function update_extension($current='')
	{
		return $this->EE->mbr_addon_builder->update_extension($current); 
	}
	public function disable_extension()
	{
		return $this->EE->mbr_addon_builder->disable_extension(); 
	}
	public function settings()
	{
		return array(); 
	}
	
	public function cartthrob_on_authorize()
	{
		$custom_data = $this->EE->cartthrob->cart->order('custom_data'); 
		$last_name = $this->EE->cartthrob->cart->order('last_name'); 
		$first_name = $this->EE->cartthrob->cart->order('first_name'); 
		$email_address = $this->EE->cartthrob->cart->order('email_address'); 
		
 		if (isset($custom_data['campaign_monitor']))
		{
 			if (! is_array($custom_data['campaign_monitor']))
			{
				$this->contact_campaign_monitor($email_address, $first_name . " " . $last_name, $custom_data['campaign_monitor'], (string) $this->settings['api_key']); 
			}
			else
			{
				foreach ($custom_data['campaign_monitor'] as $list)
				{
					$this->contact_campaign_monitor($email_address, $first_name. " " . $last_name, $list, (string) $this->settings['api_key']); 
				}
			}
		}
 
 		if (isset($custom_data['mailchimp']))
		{
 			if (! is_array($custom_data['mailchimp']))
			{
				$this->mailchimp_subscribe($email_address, $first_name . " " . $last_name, $custom_data['mailchimp'], (string) $this->settings['mailchimp_api_key']); 
			}
			else
			{
				foreach ($custom_data['mailchimp'] as $list)
				{
					$this->mailchimp_subscribe($email_address, $first_name. " " . $last_name, $list, (string) $this->settings['mailchimp_api_key']); 
				}
			}
		}
	}
	public function cartthrob_pre_process()
	{
 	}
	public function mailchimp_subscribe($email, $name, $list_id, $api_key)
	{
		require_once PATH_THIRD.$this->module_name.'/libraries/MCAPI.class.php';
		$mailchimp_api = new MCAPI($api_key);
 		
		if ($this->settings['mailchimp_require_double_opt_in'] == "no")
		{
			$double_opt_in = FALSE; 
		}
		else
		{
			$double_opt_in = TRUE; 
		}
		
		$result = $mailchimp_api->listSubscribe($list_id, $email, array("FNAME" => $this->EE->cartthrob->cart->order('first_name'), "LNAME" => $this->EE->cartthrob->cart->order("last_name")), $email_type="html",$double_opt_in);
		
	}
 	public function contact_campaign_monitor($email, $name, $list_id, $api_key)
	{
		$params =  array(
			'ListID' => $list_id,
			'Email' => $email,
			'Name' => $name
		); 
  		$url = "http://api.createsend.com/api/api.asmx"; 
 
		
		$data = "ApiKey={$api_key}";
		$url .= "/Subscriber.Add";
		
 		foreach ($params  as $key => $value )
		{
				$data .= '&' . $key . '=' .rawurlencode(utf8_encode($value));
		}
 
		$url .= '?' . $data;
  		$headers['header'][] = 'User-Agent: CT1';
		
		$ch = curl_init();
		
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers['header']);
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		
 		$response  = curl_exec( $ch );
 		curl_close( $ch );
		
		if ($response)
		{
			return true; 
		}
		else
		{
			return false; 
			
		}
	}
 	// END
}

/* End of file ext.price_field_changer_for_cartthrob.php */
/* Location: /system/expressionengine/third_party/price_field_changer_for_cartthrob/ext.price_field_changer_for_cartthrob.php */