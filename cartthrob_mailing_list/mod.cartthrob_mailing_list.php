<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cartthrob_mailing_list
{
 	public $return_data; 

	function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->library('cartthrob_loader');
		$this->EE->load->library('number');
		$this->EE->load->library('form_builder');
		$this->EE->load->library('cartthrob_variables');
		$this->EE->load->library('template_helper'); 
		
	}
 
}