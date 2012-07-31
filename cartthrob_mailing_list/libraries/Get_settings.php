<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (! class_exists('Get_settings'))
{
class Get_settings
{
	
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	// looks for $namespace.default_settings config array
	// looks in db for $namespace._settings
	// looks in third_party/$namespace/config/config.php
	public function settings($namespace, $saved_settings=FALSE)
	{
		$settings = array(); 
		if (!$saved_settings)
		{
			if (isset($this->EE->session->cache[$namespace]['settings'][$this->EE->config->item('site_id')]))
			{	
				return $this->EE->session->cache[$namespace]['settings'][$this->EE->config->item('site_id')];
			}
				@include  PATH_THIRD.$namespace.'/config'.EXT; 
					
				$this->EE->config->load(PATH_THIRD.$namespace.'/config/config'.EXT, FALSE, TRUE);

			$settings = $this->EE->config->item($namespace.'_default_settings');
		}
		
		if ($this->EE->db->table_exists($namespace.'_settings'))
		{
 			foreach ($this->EE->db->where('site_id', $this->EE->config->item('site_id'))->get($namespace.'_settings')->result() as $row)
			{
 				if ($row->serialized)
				{
					$row->value = unserialize($row->value);
				}
			
				$settings[$row->key] = $row->value;
			}
 			$this->EE->session->cache[$namespace]['settings'][$this->EE->config->item('site_id')] = $settings;
		}
	return $settings;
	}
	
}
}