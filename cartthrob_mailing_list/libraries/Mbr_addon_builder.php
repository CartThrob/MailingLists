<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

///////// REQUIREMENTS /////////////////
// LIBRARIES get_settings, security, form, string, data_formatting, locales
// MODELS field_model, channel_model, generic_model, template_model, crud_model, 
// HELPERS  security, form, string, data_formatting, file

///////// OPTIONAL ////////////////
// MODELS table_model

if (! class_exists('Mbr_addon_builder'))
{
	class Mbr_addon_builder
	{
		public $module_name = NULL; 
		public $settings = array(); 
		public $module_enabled = NULL; 
		public $extension_enabled = NULL; 
		public $no_form = NULL; 
		public $no_nav = array(); 
		public $version = 1; 
		public $nav = array();

		private $remove_keys = array(
			'name',
			'submit',
			'x',
			'y',
			'templates',
			'XID',
		);		
		
		public $tables = array(
		); 
		public $mod_actions = array(
		);
		public $mcp_actions = array(
		);
		public $fieldtypes = array(
		);
		public $hooks = array(
	 	);
		public $notification_events = array(
		);
		public $cartthrob, $store, $cart;
		
		public function __construct()
		{
			$this->EE =& get_instance();
 		}
		public function initialize($params = array())
		{
			if (empty($params)) $params = array(); 
			
			foreach ($params as $key=>$value)
			{
				$this->$key = $value; 
			}
			if (!empty($params['module_name']))
			{
				$this->module_name = $params['module_name'];
				unset($params['module_name']); 
			}
			else
			{
				$trace = debug_backtrace();
				$caller = array_shift($trace);
				if (isset($caller['class']))
				{
					$this->module_name = $caller['class']; 
				}
			}
 
			$this->EE->load->library('get_settings');
			$this->EE->load->library('table'); 
			$this->EE->load->library('locales');
			
			$this->settings = $this->EE->get_settings->settings($this->module_name);
			$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/'); 
			$this->EE->load->add_package_path(PATH_THIRD.$this->module_name."/"); 

			$this->EE->load->helper(array('security',  'form',  'string', 'data_formatting'));
			$this->EE->load->model(array('field_model', 'channel_model', 'generic_model', 'template_model'));

			$this->EE->lang->loadfile($this->module_name, $this->module_name);

			$this->module_enabled = TRUE; 
			$this->extension_enabled = TRUE; 

			if (empty($params['skip_module']))
			{
				$this->module_enabled = (bool) $this->EE->db->where('module_name', ucwords($this->module_name))->count_all_results('modules');
			}
			if (empty($params['skip_extension']))
			{
				$this->extension_enabled = (bool) $this->EE->db->where(array('class' => ucwords($this->module_name).'_ext', 'enabled' => 'y'))->count_all_results('extensions');
			}
		}
	
		//////////////////////////////////////////
		//////// MCP Functions
		//////////////////////////////////////////
		public function  load_view($current_nav, $more = array(), $structure = array())
		{
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line($this->module_name.'_module_name').' - '.$this->EE->lang->line('nav_head_'.$current_nav));

			$vars = array();

	 		$nav = $this->nav;

			$settings_views = array();

			$view_paths = array();

			if (is_array($settings_views) && count($settings_views))
			{
				foreach ($settings_views as $key => $value)
				{
					if (is_array($value))
					{
						if (isset($value['path']))
						{
							$view_paths[$key] = $value['path'];
						}

						if (isset($value['title']))
						{
							$nav['more_settings'][$key] = $value['title'];
						}
					}
					else
					{
						$nav['more_settings'][$key] = $value;
					}
				}
			}

			$sections = array();

			foreach ($nav as $top_nav => $subnav)
			{
				if ($top_nav != $current_nav)
				{
					continue;
				}

				foreach ($subnav as $url_title => $section)
				{
					if ( ! preg_match('/^http/', $url_title))
					{
						$sections[] = $url_title;
					}
				}
			}
			// we need to get CartThrob's settings for this too. DON'T CHANGE THIS. It's not a mistake. 
	 		$settings =  array_merge((array) $this->settings,$this->get_cartthrob_settings()); 

			$channels = $this->EE->channel_model->get_channels()->result_array();

			$fields = array();
			$channel_titles = array();
			$statuses = array(); 
			$product_channel_titles = array();
			$product_channel_fields = array(); 

			$product_channels = $this->EE->cartthrob->store->config('product_channels'); 

			foreach ($channels as $channel)
			{
				// get product channels and fields
				if (in_array($channel['channel_id'], $product_channels))
				{
					$product_channel_titles[$channel['channel_id']] = $channel['channel_title'];
					$channel_fields = $this->EE->field_model->get_fields($channel['field_group'])->result_array(); 
					foreach ($channel_fields as $key => &$data)
					{
						$product_channel_fields[$channel['channel_id']][$key] = array_intersect_key($data, array_fill_keys(array('field_id', 'site_id', 'group_id', 'field_name', 'field_type', 'field_label'), TRUE));
					}
				}
				// get all channels and fields

				$channel_titles[$channel['channel_id']] = $channel['channel_title'];
				$channel_fields = $this->EE->field_model->get_fields($channel['field_group'])->result_array(); 
				foreach ($channel_fields as $key => &$data)
				{
					$fields[$channel['channel_id']][$key] = array_intersect_key($data, array_fill_keys(array('field_id', 'site_id', 'group_id', 'field_name', 'field_type', 'field_label'), TRUE));
				}
					
				$statuses[$channel['channel_id']] = $this->EE->channel_model->get_channel_statuses($channel['status_group'])->result_array();
			}
			
			$status_titles = array(); 
			foreach ($statuses as $status)
			{
				foreach ($status as $item)
				{
					$status_titles[$item['status']] = $item['status']; 
				}
			}
			
			$data = array(
				'structure'	=> $structure, 
				'nav' => $nav,
				'channels' => $channels,
				'channel_titles' => $channel_titles,
				'fields' => $fields,
				'statuses' => $statuses,
				'status_titles' => $status_titles,
				'product_channel_titles'	=> $product_channel_titles,
				'product_channel_fields'	=> $product_channel_fields,
				'settings_mcp' => $this,
				'sections' => $sections,
				'view_paths' => $view_paths,
				'module_name'	=> $this->module_name,
				$this->module_name.'_tab' => (isset($this->settings[$this->module_name.'_tab'])) ? $this->settings[$this->module_name.'_tab'] : 'system_settings',
				$this->module_name.'_mcp' => $this,
				'form_open' => form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method=quick_save'.AMP.'return='.$this->EE->input->get('method', "index")),
				'extension_enabled' => $this->extension_enabled,
				'module_enabled' => $this->module_enabled,
				'settings' => $settings,
				'no_form' => (in_array($current_nav, $this->no_form)),
				'no_nav'	=>  $this->no_nav, 
				'states_and_countries' => array_merge(array(""=> '---', 'global' => 'Global', 'non-continental_us'=> 'Non-Continental US' , 'europe'=> 'Europe', 'european_union' => 'European Union'), $this->EE->locales->states(), array('0' => '---'), $this->EE->locales->all_countries()),
				'states' => $this->EE->locales->states(),
				'countries' => array_merge(array(""=> '---','global' => 'Global', 'non-continental_us'=> 'Non-Continental US' , 'europe'=> 'Europe', 'european_union' => 'European Union'), $this->EE->locales->all_countries()),
				'templates' =>  $this->get_templates(),
				
			);

			if (!empty($structure))
			{
				$data['html'] = $this->view_settings_template($data);
			}

	 		$data = array_merge($data, $more);

			$self = $data;

			$data['data'] = $self;

			unset($self);

			$this->EE->cp->add_js_script('ui', 'accordion');

			$this->EE->cp->add_to_head($this->view_settings_form_head($data) );

			return $this->view_settings_form($data); 
		}
		
		public function form_update()
		{
			$table = $this->module_name. "_options"; 
			$model = new Generic_model($table);


			if ( $this->EE->input->post('delete'))
			{
				$model->delete($this->EE->input->post('id')); 
				$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s', lang($this->module_name.'_deleted')));
			}
			else
			{
				foreach (array_keys($_POST) as $key)
				{
					if ( ! in_array($key, $this->remove_keys) && ! preg_match('/^('.ucwords($this->module_name).'_.*?_settings)_.*/', $key))
					{
						$data[$key] = $this->EE->input->post($key, TRUE);
					}
				}

				if (isset($data["sub_settings"]["data"]))
				{
					$data['data'] = serialize($data["sub_settings"]["data"]); 
				}

				#var_dump($data); exit; 
	 			if (!$this->EE->input->post('id'))
				{
	 				$model->create($data);
				}
				else
				{
					if ( $this->EE->input->post('id') && !empty($data))
					{
						$model->update($this->EE->input->post('id'), $data);
					}

				}
				$this->EE->session->set_flashdata($this->module_name.'_system_message', sprintf('%s',lang($this->module_name.'_updated')));
			}
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.$this->EE->input->get('return', TRUE));
		} // END form_update
		public function pagination_config($method, $total_rows, $per_page=50)
		{
			$config['base_url'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.AMP.'method='.$method;
			$config['total_rows'] = $total_rows;
			$config['per_page'] = $per_page;
			$config['page_query_string'] = TRUE;
			$config['query_string_segment'] = 'rownum';
			$config['full_tag_open'] = '<p id="paginationLinks">';
			$config['full_tag_close'] = '</p>';
			$config['prev_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="<" />';
			$config['next_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt=">" />';
			$config['first_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="< <" />';
			$config['last_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="> >" />';

			return $config;
		}
		
		public function get_pagination($table, $limit, $offset = NULL)
		{
			if ( ! $offset )
			{		
				$offset = $this->EE->input->get_post('rownum');
			}
			$this->EE->load->library('pagination');
			$total = $this->EE->db->count_all($table);
			if ($total == 0)
			{
				return FALSE; 
			}
			$this->EE->pagination->initialize( $this->pagination_config($this->module_name, $total, $limit) );

			return $this->EE->pagination->create_links();
		}
		
		public function quick_save()
		{
			$this->EE->load->library('get_settings');

			$db_settings = $this->EE->get_settings->settings($this->module_name,$saved_settings = TRUE);	

			$data = array();

			foreach (array_keys($_POST) as $key)
			{
				if ( ! in_array($key, $this->remove_keys) && ! preg_match('/^('.ucwords($this->module_name).'_.*?_settings)_.*/', $key))
				{
					$data[$key] = $this->EE->input->post($key, TRUE);
				}
			}
	 		foreach ($data as $key => $value)
			{
				$where = array(
					'site_id' => $this->EE->config->item('site_id'),
					'`key`' => $key
				);

				if (is_array($value))
				{
					$row['serialized'] = 1;
					$row['value'] = serialize($value);
				}
				else
				{
					$row['serialized'] = 0;
					$row['value'] = $value;
				}
				if (isset($db_settings[$key]))
				{
					if ($value !== $db_settings[$key])
					{
						$this->EE->db->update($this->module_name.'_settings', $row, $where);
					}
				}
				else
				{
	 				$this->EE->db->insert($this->module_name.'_settings', array_merge($row, $where));
	 			}
			}

			$this->EE->session->set_flashdata('message_success', sprintf('%s', lang('settings_saved')));

			$return = ($this->EE->input->get('return')) ? AMP.'method='.$this->EE->input->get('return', TRUE) : '';

			if ($this->EE->input->post($this->module_name.'_tab'))
			{
				$return .= '#'.$this->EE->input->post($this->module_name.'_tab', TRUE);
			}

			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name.$return);
		}
		
		public function get_cartthrob_settings()
		{
			$this->EE->load->library('get_settings');
			if (class_exists('cartthrob'))
			{
				return $this->EE->get_settings->settings("cartthrob");

			}
			return array(); 

		}
		// @TODO deprecate
		public function channel_dropdown_js()
		{
			
			
			$this->EE->load->model(array('channel_model', 'field_model'));
			
			$this->EE->load->library('javascript');
			
			$data = array(
				'channels' => array(),
				'fields' => array(),
				'statuses' => array(),
			);
			
			$statuses = array();
			
			$query = $this->EE->channel_model->get_channels();
			
			foreach ($query->result() as $channel)
			{
				$data['channels'][$channel->channel_id] = $channel->channel_title;
				
				// $fields[$channel['channel_id']] = $this->EE->field_model->get_fields($channel['field_group'])->result_array();
				// only want to capture a subset of data, because we're using this for JSON and we were getting too much data previously
				$field_query = $this->EE->field_model->get_fields($channel->field_group);
				
				$data['fields'][$channel->channel_id] = array();
				
				foreach ($field_query->result_array() as $fields)
				{
					$data['fields'][$channel->channel_id][] = array_intersect_key($fields, array_fill_keys(array('field_id', 'site_id', 'group_id', 'field_name', 'field_type', 'field_label'), TRUE));
				}
				
				$statuses[$channel->channel_id] = $this->EE->channel_model->get_channel_statuses($channel->status_group)->result_array();
			}
			
			foreach ($statuses as $status)
			{
				foreach ($status as $item)
				{
					$data['statuses'][$item['status']] = $item['status']; 
				}
			}
			
			$this->EE->cp->add_to_head('
			<script type="text/javascript">
			(function() {
				window.channelDropdown = '.$this->EE->javascript->generate_json($data).';
				channelDropdown.updateSelect = function(select, options) {
					var val = $(select).val();
					var attrs = {};
					for (i=0;i<select.attributes.length;i++) {
						if (select.attributes[i].name == "value") {
							val = select.attributes[i].value;
						} else {
							attrs[select.attributes[i].name] = select.attributes[i].value;
						}
					}
					$(select).replaceWith(channelDropdown.createSelect(attrs, options, val));
				};
				channelDropdown.createSelect = function(attributes, options, selected) {
					var select = "<select ";
					for (i in attributes) {
						select += i+\'="\'+attributes[i]+\'" \';
					}
					select += ">";
					for (i in options) {
						select += \'<option value="\'+i+\'"\';
						if (selected != undefined && selected == i) {
							select += \' selected="selected"\';
						}
						select += ">"+options[i]+"</option>";
					}
					select += "</select>";
					return select;
				};
			})();
			</script>');
			
			$this->EE->javascript->output('
			$("select.statuses").each(function(){
				channelDropdown.updateSelect(this, channelDropdown.statuses);
			});
			$("select.statuses_blank").each(function(){
				var statuses = {"" : "---", "ANY" : "ANY"};
				$.extend(statuses, channelDropdown.statuses);
				channelDropdown.updateSelect(this, statuses);
			});
			$("select.all_fields").each(function(){
				var fields = {"":"---"};
				for (i in channelDropdown.fields) {
					for (j in channelDropdown.fields[i]) {
						fields["field_id_"+channelDropdown.fields[i][j].field_id] = channelDropdown.fields[i][j].field_label;
					}
				}
				channelDropdown.updateSelect(this, fields);
			});
			$("select.channels").each(function(){
				channelDropdown.updateSelect(this, channelDropdown.channels);
			});
			$("select.channels").bind("change", function(e){
				var channel_id = Number($(e.target).val());
				var section = $(e.target).attr("id").replace("select_", "");
				$("select.field_"+section).children().filter(function(){ return this.value; }).remove();
				$("select.status_"+section).children().filter(function(){ return this.value; }).remove();
				if ($(this).val() != "")
				{
					for (i in channelDropdown.fields[channel_id])
					{
						$("select.field_"+section).append(\'<option value="\'+channelDropdown.fields[channel_id][i].field_id+\'">\'+channelDropdown.fields[channel_id][i].field_label+"</option>");
					}
					for (i in channelDropdown.statuses[channel_id])
					{
						$("select.status_"+section).append(\'<option value="\'+channelDropdown.statuses[channel_id][i].status_id+\'">\'+channelDropdown.statuses[channel_id][i].status+"</option>");
					}
				}
			});
			');
		}
		
		public function get_templates()
		{
			static $templates;

			if (is_null($templates))
			{
				$templates = array();

				$query = $this->EE->template_model->get_templates();

				foreach ($query->result() as $row)
				{
					$templates[$row->group_name.'/'.$row->template_name] = $row->group_name.'/'.$row->template_name;
				}
			}

			return $templates;
		}
		function get_shipping_plugins()
		{
			return $this->get_plugins('shipping');
		}

		function get_tax_plugins()
		{
			return $this->get_plugins('tax');
		}
		
		// --------------------------------
		//  Get Shipping Plugins
		// --------------------------------
		/**
		 * Loads shipping plugin files
		 *
		 * @access private
		 * @param NULL
		 * @return array $plugins Array containing settings and information about the plugin
		 * @since 1.0.0
		 * @author Rob Sanchez
		 */
		function get_plugins($type)
		{
			$this->EE->load->helper(array('file', 'data_formatting'));

			$plugins = array();

			$paths[] = CARTTHROB_PATH.'plugins/'.$type.'/';

			if ($this->EE->config->item('cartthrob_third_party_path'))
			{
				$paths[] = rtrim($this->EE->config->item('cartthrob_third_party_path'), '/').'/'.$type.'_plugins/';
			}
			else
			{
				$paths[] = PATH_THIRD.'cartthrob/third_party/'.$type.'_plugins/';
			}

			require_once CARTTHROB_PATH.'core/Cartthrob_'.$type.EXT;

			foreach ($paths as $path)
			{
				if ( ! is_dir($path))
				{
					continue;
				}

				foreach (get_filenames($path, TRUE) as $file)
				{
					if ( ! preg_match('/^Cartthrob_/', basename($file, EXT)))
					{
						continue;
					}

					require_once $file;

					$class = basename($file, EXT);

					$language = set($this->EE->session->userdata('language'), $this->EE->input->cookie('language'), $this->EE->config->item('deft_lang'), 'english');			

					if (file_exists(PATH_THIRD.'cartthrob/language/'.$language.'/'.strtolower($class).'_lang.php'))
					{
						$this->EE->lang->loadfile(strtolower($class));
					}
					else if (file_exists($path.'../language/'.$language.'/'.strtolower($class).'_lang.php'))
					{
						$this->EE->lang->load(strtolower($class), $language, FALSE, TRUE, $path.'../');
					}

					$plugin_info = get_class_vars($class);

					$plugin_info['classname'] = $class;

					$plugins[$plugin_info['classname']] = $plugin_info['title'];
				}
			}
	 		return $plugins;
		}
		public function get_member_info($member_id)
		{
			return $this->EE->db->select("*")->where('member_id', $member_id)
	 											->limit(1)
												->get("members")
												->row_array();

		}
		
		
		protected function html($content, $tag = 'p', $attributes = '')
		{
			if (is_array($attributes))
			{
				$attributes = _parse_attributes($attributes);
			}

			return '<'.$tag.$attributes.'>'.$content.'</'.$tag.'>';
		}
		
		// --------------------------------
		//  Plugin Settings
		// --------------------------------
		/**
		 * Creates setting controls
		 * 
		 * @access private
		 * @param string $type text|textarea|radio The type of control that is being output
		 * @param string $name input name of the control option
		 * @param string $current_value the current value stored for this input option
		 * @param array|bool $options array of options that will be output (for radio, else ignored) 
		 * @return string the control's HTML 
		 * @since 1.0.0
		 * @author Rob Sanchez
		 */
		public function plugin_setting($type, $name, $current_value, $options = array(), $attributes = array())
		{
			$output = '';

			if ( ! is_array($options))
			{
				$options = array();
			}
			else
			{
				foreach ($options as $key => $value)
				{
					$options[$key] = lang($value);
				}
			}

			if ( ! is_array($attributes))
			{
				$attributes = array();
			}
	 		switch ($type)
			{
				case 'select':
					if (empty($options)) $attributes['value'] = $current_value;
					$output = form_dropdown($name, $options, $current_value, _attributes_to_string($attributes));
					break;
				case 'multiselect':
					$output = form_multiselect($name."[]", $options, $current_value, _attributes_to_string($attributes));
					break;
				case 'checkbox':
					$output = form_label(form_checkbox($name, 1, ! empty($current_value), isset($options['extra']) ? $options['extra'] : '').'&nbsp;'.(!empty($options['label'])? $options['label'] : $this->EE->lang->line('yes') ), $name);
					break;
				case 'text':
					$attributes['name'] = $name;
					$attributes['value'] = $current_value;
					$output =  form_input($attributes);
					break;
				case 'textarea':
					$attributes['name'] = $name;
					$attributes['value'] = $current_value;
					$output =  form_textarea($attributes);
					break;
				case 'radio':
					if (empty($options))
					{
						$output .= form_label(form_radio($name, 1, (bool) $current_value).'&nbsp;'. $this->EE->lang->line('yes'), $name, array('class' => 'radio'));
						$output .= form_label(form_radio($name, 0, (bool) ! $current_value).'&nbsp;'. $this->EE->lang->line('no'), $name, array('class' => 'radio'));
					}
					else
					{
						//if is index array
						if (array_values($options) === $options)
						{
							foreach($options as $option)
							{
								$output .= form_label(form_radio($name, $option, ($current_value === $option)).'&nbsp;'. $option, $name, array('class' => 'radio'));
							}
						}
						//if associative array
						else
						{
							foreach($options as $option => $option_name)
							{
								$output .= form_label(form_radio($name, $option, ($current_value === $option)).'&nbsp;'. lang($option_name), $name, array('class' => 'radio'));
							}
						}
					}
					break;
				default:
			}
			return $output;
		}
		
		
		////////////////////////
		// EXTENSION FUNCTIONS
		////////////////////////
		public function settings_form()
		{
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name);
		}
		/**
		 * Activates Extension
		 *
		 * @access public
		 * @param NULL
		 * @return void
		 * @since 1.0.0
		 * @author Rob Sanchez
		 */
		public function activate_extension($hooks = array(), $class=NULL)
		{
			if (!$class)
			{
				$class=$this->module_name."_ext";
			}
			
			foreach ($hooks as $row)
			{
				$this->EE->db->insert(
					'extensions',
					array(
						'class' => $class,
						'method' => $row[0],
						'hook' => ( ! isset($row[1])) ? $row[0] : $row[1],
						'settings' => ( ! isset($row[2])) ? '' : $row[2],
						'priority' => ( ! isset($row[3])) ? 10 : $row[3],
						'version' => $this->version,
						'enabled' => 'y',
					)
				);
			}

			return TRUE;
		}
		// --------------------------------
		//  Update Extension
		// --------------------------------  
		/**
		 * Updates Extension
		 *
		 * @access public
		 * @param string
		 * @return void|BOOLEAN False if the extension is current
		 * @since 1.0.0
		 * @author Rob Sanchez
		 */
		public function update_extension($current='')
		{
			if ($current == '' OR $current == $this->version)
			{
				return FALSE;
			}

			$this->EE->db->update('extensions', array('version' => $this->version), array('class' => $this->module_name));

			return TRUE;
		}
		
		// --------------------------------
		//  Disable Extension
		// --------------------------------
		/**
		 * Disables Extension
		 * 
		 * Deletes mention of this extension from the exp_extensions database table
		 *
		 * @access public
		 * @param NULL
		 * @return void
		 * @since 1.0.0
		 * @author Rob Sanchez
		 */
		public function disable_extension()
		{
			$this->EE->db->delete('extensions', array('class' => $this->module_name));
		}
		
		/////////////////////////////////////
		/// UPDATE FUNCTIONS
		/////////////////////////////////////
		public function mbr_install($has_cp_backend="y", $has_publish_fields = "n", $current= FALSE)
		{
			// updates
			if ( $current!== FALSE )
			{
				$this->current = $current;
				if ($this->current == $this->version)
				{
					return FALSE;
				}
			}
			else
			// installs
			{
				//install module to exp_modules
				$data = array(
					'module_name' => ucwords($this->module_name),
					'module_version' => $this->version,
					'has_cp_backend' => $has_cp_backend,
					'has_publish_fields' => $has_publish_fields
				);

				$this->EE->db->insert('modules', $data);
				
				////////////// FIELD TYPES

				if (!empty($this->fieldtypes))
				{
					//install the fieldtypes
					require_once APPPATH.'fieldtypes/EE_Fieldtype'.EXT;

					foreach ($this->fieldtypes as $fieldtype)
					{
						require_once PATH_THIRD.$fieldtype.'/ft.'.$fieldtype.EXT;

						$ft = get_class_vars(ucwords($fieldtype.'_ft'));

						$this->EE->db->insert('fieldtypes', array(
							'name' => $fieldtype,
							'version' => $ft['info']['version'],
							'settings' => base64_encode(serialize(array())),
							'has_global_settings' => method_exists($fieldtype, 'display_global_settings') ? 'y' : 'n'
						));
					}
				}
			}
			///////////////// TABLES /////////////////////////
			$this->EE->load->dbforge();

			// only do this if we actually have tables. 
			if (!empty($this->tables))
			{
				foreach ($this->tables as $key=>$value)
				{
					if ($key =="generic_settings")
					{
						unset($this->tables['generic_settings']);
						$this->tables[$this->module_name."_settings"] = $value;
						break;
					}
				}
				$this->EE->load->model('table_model');
				$this->EE->table_model->update_tables($this->tables);
			}

			/////////////// NOTIFICICATIONS /////////////////////////
			if (!empty($this->notification_events))
			{
				$existing_notifications = array();

				if ($this->EE->db->table_exists('cartthrob_notification_events'))
				{
					$this->EE->db->select('notification_event')
							->from('cartthrob_notification_events')
							->like('application', ucwords($this->module_name), 'after');

					if ($this->EE->db->get()->result())
					{
						foreach ($this->EE->db->get()->result() as $row)
						{
							$existing_notifications[] = $row->notification_event;
						}					
					}

				foreach ($this->notification_events as $event)
				{
					if (!empty($event))
					{
						if ( ! in_array($event, $existing_notifications))
						{
							$this->EE->db->insert(
								'cartthrob_notification_events',
								array(
									'application' => ucwords($this->module_name),
									'notification_event' => $event,
								)
							);
						}
					}
				}
			}
			}



			/////////////// EXTENSIONS /////////////////////////

			if (!empty($this->hooks))
			{
				if ( $current !== FALSE )
				{
					$this->EE->db->update('extensions', array('version' => $this->version), array('class' => ucwords($this->module_name).'_ext'));
				}
				$this->EE->db->select('method')
						->from('extensions')
						->like('class', ucwords($this->module_name), 'after');

				$existing_extensions = array();

				foreach ($this->EE->db->get()->result() as $row)
				{
					$existing_extensions[] = $row->method;
				}

				foreach ($this->hooks as $row)
				{
					if (!empty($row))
					{
						if ( ! in_array($row[0], $existing_extensions))
						{
							$this->EE->db->insert(
								'extensions',
								array(
									'class' => ucwords($this->module_name).'_ext',
									'method' => $row[0],
									'hook' => ( ! isset($row[1])) ? $row[0] : $row[1],
									'settings' => ( ! isset($row[2])) ? '' : $row[2],
									'priority' => ( ! isset($row[3])) ? 10 : $row[3],
									'version' => $this->version,
									'enabled' => 'y',
								)
							);
						}
					}
				}
			}
			////////////////////////// MODULE AND MCP ACTIONS /////////////////////

			//check for Addon actions in the database
			//so we don't get duplicates
			$this->EE->db->select('method')
					->from('actions')
					->like('class', ucwords($this->module_name), 'after');

			$existing_methods = array();

			foreach ($this->EE->db->get()->result() as $row)
			{
				$existing_methods[] = $row->method;
			}
			//////////// MODULE ACTIONS
			if (!empty($this->mod_actions))
			{
				//install the module actions from $this->mod_actions
				foreach ($this->mod_actions as $method)
				{
					if ( ! in_array($method, $existing_methods))
					{
						$this->EE->db->insert('actions', array('class' => ucwords($this->module_name), 'method' => $method));
					}
				}

			}
			///////////// MCP ACTIONS
			if (!empty($this->mcp_actions))
			{
				//install the module actions from $this->mcp_actions
				foreach ($this->mcp_actions as $method)
				{
					if ( ! in_array($method, $existing_methods))
					{
						$this->EE->db->insert('actions', array('class' => ucwords($this->module_name).'_mcp', 'method' => $method));
					}
				}
			}

			return TRUE;
 		}
		public function install($has_cp_backend = "y", $has_publish_fields = "n")
		{
			return $this->mbr_install($has_cp_backend, $has_publish_fields); 
		}
		public function update($current = '')
		{
			return $this->mbr_install(NULL, NULL, $current); 
		}
		public function uninstall()
		{
			$this->EE->db->delete('modules', array('module_name' => ucwords($this->module_name)));

			$this->EE->db->like('class', ucwords($this->module_name), 'after')->delete('actions');

			$this->EE->db->delete('extensions', array('class' => ucwords($this->module_name).'_ext'));

			if ($this->EE->db->table_exists('cartthrob_notification_events'))
			{
				$this->EE->db->delete('cartthrob_notification_events', array('application' => ucwords($this->module_name)));
			}

			//should we do this?
			//nah, do it yourself if you really want to
			/*
			$this->EE->load->dbforge();

			foreach (array_keys($this->tables) as $table)
			{
				$this->EE->dbforge->drop_table($table);
			}
			*/

			return TRUE;
			
		}
		////////////////////////// VIEW CONTENT /////////////////////////////
		
		public function view_settings_template($data)
		{
			extract($data); 
			////////////////////////////// Main Heading /////////////////////// 
			$tmpl = array (
				'table_open'          => '<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">',

				'heading_cell_start'  => '<th colspan="2">',
				'heading_cell_end'    => '</th>',

				'table_close'         => '</table>'
			);

			if (!empty($structure['caption']))
			{
				$this->EE->table->set_caption(lang($structure['caption']));
			}

			if (!empty($structure['description']))
			{
				$this->EE->table->set_heading(array(
					'<strong>'.lang($structure['title']) .'</strong><br />'.$structure['description']
					));
			}
			else
			{
				$this->EE->table->set_heading(array(
					'<strong>'.lang($structure['title']) .'</strong>'
					));

			}

			$this->EE->table->set_template($tmpl);
			$content = $this->EE->table->generate(); 
			$this->EE->table->clear();

		 	///////////////////////////////////////////////////////////////////


			if (is_array($structure['settings'])) 
			{
				foreach ($structure['settings'] as $setting) 
				{
					if ($setting['type'] == 'matrix') 
					{
						//retrieve the current set value of the field
					    $current_values = (isset($settings[ $setting['short_name']]) ) ? $settings[ $setting['short_name']] : FALSE;

					    //set the value to the default value if there is no set value and the default value is defined
					    $current_values = ($current_values === FALSE && isset($setting['default'])) ? 
							$setting['default'] : $current_values;

						$content .='<div class="matrix">';
						$content .='<table cellpadding="0" cellspacing="0" border="0" class="mainTable padTable">'; 

						$header = array(""); 
						foreach ($setting['settings'] as $count => $matrix_setting) 
						{

							$style=""; 
						    $setting['settings'][$count]['style'] = $style;
							$line = "<strong>". lang($matrix_setting['name']). "</strong>"; 

							isset($matrix_setting['note']) ? $line .='<br />'.lang($matrix_setting['note']) : '';
							$header[] = $line; 
						}
						$header[] = ""; 
						$content .="<thead>"; 
						$content .="<tr>"; 
						foreach ($header as $th)
						{
							$content .="<th>";
							$content .=$th; 
							$content .="</th>"; 
						}
						$content .="</tr>"; 
						$content .='</thead>'; 
						$content .='<tbody>'; 


						if ($current_values === FALSE || ! count($current_values))
						{
							$current_values = array(array());
							foreach ($setting['settings'] as $matrix_setting)
							{
								$current_values[0][$matrix_setting['short_name']] = isset($matrix_setting['default']) ? $matrix_setting['default'] : '';
							}
						}

						foreach ($current_values as $count => $current_value)
						{
							$content .='<tr class="'.$setting['short_name'].'_setting"';
							$content .=	'rel ="'.$setting['short_name'].'"'; 
							$content .=	'id	="'.$setting['short_name'].'_setting_'.$count.'">';

								$content .='<td><img border="0" ';
								$content .='src="'. $this->EE->config->item('theme_folder_url').'third_party/cartthrob/images/ct_drag_handle.gif" width="10" height="17" /></td>';
									foreach ($setting['settings'] as $matrix_setting) 
									{
										$content .='<td style="'.$matrix_setting['style'].'" rel="'.$matrix_setting['short_name'].'"';
										$content .='class="'.$matrix_setting['short_name'].'_setting_option" >'; 
										$content .= $settings_mcp->plugin_setting($matrix_setting['type'], $setting['short_name'].'['.$count.']['.$matrix_setting['short_name'].']', @$current_value[$matrix_setting['short_name']], @$matrix_setting['options'], @$matrix_setting['attributes']);
										$content .='</td>'; 
									}
								$content .='<td>'; 
								$content .=' <a href="#" class="remove_matrix_row">
											<img border="0" src="'.$this->EE->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png" />
										</a>';
								$content .='</td>'; 
							$content .='</tr>';
						}

						$content.='	</tbody>
						</table>
					</div>';

						$content .='
						<fieldset class="plugin_add_new_setting" >
							<a href="#" class="ct_add_matrix_row" rel="settings_template" id="add_new_'.$setting['short_name'].'">
								'.lang('add_another_row').'
							</a>
						</fieldset>';

						$content .='
						<table style="display: none;" class="'.$structure['class'].'">
							<tr id="'.$setting['short_name'].'_blank" class="'.$setting['short_name'].'">
								<td ><img border="0" src="'.$this->EE->config->item('theme_folder_url').'third_party/cartthrob/images/ct_drag_handle.gif" width="10" height="17" /></td>';

								foreach ($setting['settings'] as $matrix_setting)
								{
									$content .='<td style="'.$matrix_setting['style'].'"  rel="'.$matrix_setting['short_name'].'"  class="'.$matrix_setting['short_name'].'_setting_option'.'">'.$settings_mcp->plugin_setting($matrix_setting['type'], '', (isset($matrix_setting['default'])) ? $matrix_setting['default'] : '', @$matrix_setting['options'], @$matrix_setting['attributes']).'</td>';							
								}

								$content .='
								<td>
									<a href="#" class="remove_matrix_row"><img border="0" src="'.$this->EE->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png" /></a>
								</td>
							</tr>
						</table>
						';
					}
					 elseif ($setting['type'] == 'header')
					{
						$content .='<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
								<thead class="">
									<tr>
										<th colspan="2">
											<strong>'.$setting['name'].'</strong><br />
										</th>
									</tr>
								</thead>
							</table>';
					}
					elseif ($setting['type'] == "html")
					{
 						$content .='
							<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
							<tbody>
								<tr class="even">
									<td>
										'.$setting['html'].'
									</td>
								</tr>
							</tbody>
							</table>';
 					}
					else
					{
						//retrieve the current set value of the field
						$current_value = (isset($settings[ $setting['short_name']])) ? $settings[ $setting['short_name']] : FALSE;
						//set the value to the default value if there is no set value and the default value is defined
						$current_value = ($current_value === FALSE && isset($setting['default'])) ? $setting['default'] : $current_value;

						$content .='
							<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
							<tbody>
								<tr class="even">
									<td>
										<label>'.lang($setting['name']).'</label><br><span class="subtext">'.(isset($setting['note']) ? lang($setting['note']) : NULL).'</span>
										</td>
									<td style="width:50%;">
										'.$settings_mcp->plugin_setting($setting['type'], $setting['short_name'], $current_value, @$setting['options'], @$setting['attributes']).'
									</td>
								</tr>
							</tbody>
							</table>';
					}		
				}
			}
			
			return $content; 
		}
		public function view_settings_form($data)
		{
			extract($data); 
			
			$tab = $module_name. "_tab"; 

			$content = "<!-- begin right column -->";

			$content .='
			<div class="ct_top_nav">
				<div class="ct_nav" >';
				foreach (array_keys($nav) as $method) 
				{
					if (!in_array($method, $no_nav))
					{
						$content .='<span class="button"><a class="nav_button';
						if ( ! $this->EE->input->get('method') || $this->EE->input->get('method') == $method)
						{
							$content .=' current'; 
						}
						$content.='"'; 
						
						// if there's no lang itme for this, we'll just convert the method name. 
						$nav_lang = lang('nav_head_'.$method); 
						if ($nav_lang == 'nav_head_'.$method)
						{
 							$nav_lang = ucwords(str_replace("_", " ",$method)); 
						}
						
						$content .= ' href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$module_name.AMP.'method='.$method.'">'.$nav_lang.'</a></span>'; 
					}
				} 
			$content .='			
					<div class="clear_both"></div>	
				</div>	
			</div>';

			$content .='
			<div class="clear_left shun"></div>';

			if ($this->EE->session->flashdata($module_name.'_system_error'))
			{
				$content .='<div id="ct_system_error"><h4>';
				$content .= $this->session->flashdata($module_name.'_system_error');
				$content .='</h4></div>';

			}

			if ($this->EE->session->flashdata($module_name.'_system_message'))
			{
				$content .='<div id="ct_system_error"><h4>';
				$content .= $this->EE->session->flashdata($module_name.'_system_message');
				$content .='</h4></div>';
			}

			if ($this->extension_enabled===FALSE) 
			{
				$content .='<div id="ct_system_error"><h4>';
				$content .= lang('extension_not_installed'); 
				$content .='</h4>';
				$content .= lang('please').' <a href="'.BASE.AMP.'C=addons_extensions'.'">'.lang('enable').'</a> '.lang('before_proceeding').'</div>';
			}

			if ($this->module_enabled=== FALSE)
			{
				$content .='<div id="ct_system_error"><h4>';
				$content .= lang('module_not_installed'); 
				$content .='</h4>';
				$content .= lang('please').' <a href="'.BASE.AMP.'C=addons_modules'.'">'.lang('install').'</a> '.lang('before_proceeding').'</div>';
			}

			if (! $no_form)
			{
				$content .= $form_open; 
			}
			$content .='<div id="'.$module_name.'_settings_content">
				<input type="hidden" name="'.$module_name.'_tab" value="'.$tab.'" id="'.$module_name.'_tab" />'; 

 
			foreach ($sections as $section)
			{
					$view_path = (isset($view_paths[$section])) ? $view_paths[$section] : PATH_THIRD.$this->module_name.'/views/';
 					$view_html = NULL; 

					if (file_exists($view_path.$section)) {
						$view_html = $this->EE->load->view($section, $data, TRUE); 
					}
					else
					{
						if (!is_array($data))
						{
							$view_html = $data;
						}
						elseif (!empty($data['html']))
						{
							$view_html = $data['html']; 
						}
						else
						{
							// this will probably throw an error,but that's probably what we want
							$view_html = $this->EE->load->view($section, $data, TRUE); 
						}
 					}
 					
 					$section_lang = lang($section."_header"); 
					if ($section_lang == $section."_header")
					{
						$section_lang = ucwords(str_replace("_", " ", $section)); 
					}

					$content .='<h3 class="accordion" data-hash="'.$section.'">'.$section_lang.'</h3>
					<div style="padding: 5px 1px;">
						'.$view_html.'
					</div>'; 
			}	

			if (! $no_form)
			{
				$content .='<p><input type="submit" name="submit" value="'.lang('submit').'" class="submit" /></p>
				</form>'; 
			}
			
			return $content; 
			
		}
		public function default_css()
		{
			$css = "
			<style type='text/css'>
			#ct_errors {
			    border: #CCC9A4 1px solid;
			    border-top-width: 0px;
				background: #fff2cb url(../images/ct_subnav_bg.gif) repeat-x;
				padding: 8px 10px 8px 10px; 
				margin: 0px 0 10px 0;
			}
			#ct_system_error{
				border:1px solid #bf0012;
				background:#ffbc9f url(../images/ct_not_logged_heart.png) no-repeat right top;
				padding:15px 45px 15px 15px;
			    font-family: 'Lucida Grande', Arial, Helvetica, sans-serif;
				color:#18362D;
				font-size:14px;
				margin:0 0 10px 0;
			}
			#ct_system_error a{
				color:#a10a0a;
			}
			#ct_system_error h4{
			    font-family: 'Lucida Grande', Arial, Helvetica, sans-serif;
				color:#18362D;
				font-size:18px;
				font-weight:bold;
				display:inline;
				margin:0 8px 0 0;
			}


			.ct_nav {
				//padding:			0 25px;
				float: left;
			}

			.ct_nav span.button {
				float:				  left;
				margin-bottom:  10px;
			    margin-right: 6px;
			}
			 .ct_nav .nav_button {
				font-weight:	normal;
				background:		#d0d9e1;
				color:			#34424b;
			}

			.ct_nav span.button a.nav_button{
				color: #34424b;
			    font: 12px/12px Arial, 'Hevlvetica Neue', sans-serif;
			    -moz-border-radius: 3px;
			    -webkit-border-radius: 3px;
			}
			.ct_nav span.button a.nav_button:hover {
				background-color:#e11842;
				text-decoration:none;
				color: #fff;
			}
			.ct_nav span.button a.current {
				background-color:#e11842;
				text-decoration:none;
				color: #fff;
			}
			.clear_both{
				clear: both;
			}
			label.radio{
				display: block;
				margin: 0;
				padding:  0;
				padding-bottom: 5;
			}

			.plugin_add_new_setting{
				 margin-top:10px;
				 margin-bottom:10px;	
			}
			</style>
			"; 
			return $css; 
		}
		public function view_settings_form_head($data)
		{
			extract($data); 
			
			if (@file_exists($this->EE->config->item('theme_folder_url').'third_party/'.$module_name.'/css/'.$module_name.'.css'))
			{
				$css = '<link href="'.$this->EE->config->item('theme_folder_url').'third_party/'.$module_name.'/css/'.$module_name.'.css" rel="stylesheet" type="text/css" media="screen" />'; 
			}
			else
			{
				$css =  $this->default_css(); 
			}
			
			// slightly different structures for extensions and MCP settings. 
			// this accounts for adding new lines for Extensions vs. MCP
			if ($this->EE->input->get('M') == "extension_settings")
			{
				$add_new_setting_js = '$("fieldset.plugin_add_new_setting a").bind("click", function(){
					var name = $(this).attr("id").replace("add_new_", "");
					var count = ($("tr."+name+"_setting:last").length > 0) ? Number($("tr."+name+"_setting:last").attr("id").replace(name+"_setting_","")) + 1 : 0;
					var plugin_classname = $("#"+name+"_blank").parent().parent().attr("class");

					var element = $("#"+name+"_blank").attr("class").split(" ");
					var setting_short_name = element[0];

					var clone = $("#"+name+"_blank").clone();
					clone.attr({"id":name+"_setting_"+count});
					clone.attr({"class":name+"_setting"});
					clone.attr({"rel": plugin_classname+"_settings["+setting_short_name+"]"});
					clone.find(":input").each(function(){
						var matrix_setting_short_name = $(this).parent().attr("class");
						$(this).parent().attr("rel", matrix_setting_short_name);

						$(this).attr("name", plugin_classname+"_settings["+setting_short_name+"]["+count+"]["+matrix_setting_short_name+"]");	
					});
		 			clone.children("td").attr("class","");
					$(this).parent().prev().find("tbody").append(clone);
					return false;
				});';
			}
			else
			{
				$add_new_setting_js = '$("fieldset.plugin_add_new_setting a").bind("click", function(){
					var name = $(this).attr("id").replace("add_new_", "");
					
					if (count ==0)
					{
						count = ($("tr."+name+"_setting:last").length > 0) ? Number($("tr."+name+"_setting:last").attr("id").replace(name+"_setting_","")) + 1 : 0;
					}
					else
					{
						count ++; 
					}
					var plugin_classname = $("#"+name+"_blank").parent().parent().attr("class");

					var element = $("#"+name+"_blank").attr("class").split(" ");
					var setting_short_name = element[0];
					var clone = $("#"+name+"_blank").clone();
					clone.attr({"id":name+"_setting_"+count});
					clone.attr({"class":name});
					clone.find(":input").each(function(){
						var matrix_setting_short_name = $(this).parent().attr("rel");
						$(this).parent().attr("rel", matrix_setting_short_name);

						$(this).attr("name", setting_short_name + "["+count+"]["+matrix_setting_short_name+"]");	
					});
		 			clone.children("td").attr("class","");
					$(this).parent().prev().find("tbody").append(clone);
					return false;
				});';
			}
			$content = $css; 
			$content .='

			<script type="text/javascript">

				jQuery.'.$module_name.'CP = {
					currentSection: function() {
						if (window.location.hash && window.location.hash != "#") {
							return window.location.hash.substring(1);
						} else {
							return $("#'.$module_name.'_settings_content h3:first").attr("data-hash");
						}
					},
 					'.(isset($channel_titles) ? "channels: ".$this->EE->javascript->generate_json($channel_titles).',' : NULL).'
 					'.(isset($product_channel_titles) ? "product_channels: ".$this->EE->javascript->generate_json($product_channel_titles).',' : NULL).'
					'.(isset($fields) ? "fields: ".$this->EE->javascript->generate_json($fields).',' : NULL).'
					'.(isset($product_channel_fields) ? "product_channel_fields: ".$this->EE->javascript->generate_json($product_channel_fields).',' : NULL).'
 					'.(isset($statuses) ? "statuses: ".$this->EE->javascript->generate_json($statuses).',' : NULL).'
					'.(isset($templates) ? "templates: ".$this->EE->javascript->generate_json($templates).',' : NULL).'
					'.(isset($states) ? "states: ".$this->EE->javascript->generate_json($states).',' : NULL).'
				 	'.(isset($countries) ? "countries: ".$this->EE->javascript->generate_json($countries).',': NULL).'
				 	'.(isset($states_and_countries) ? "statesAndCountries: ".$this->EE->javascript->generate_json($states_and_countries).',' : NULL).'
					checkSelectedChannel: function (selector, section) {
						if ($(selector).val() !="") {
							$(section).css("display","inline");
						} else {
							$(section).css("display","none");
						}
					},
					updateSelect: function(select, options) {
						var val = $(select).val();
						var attrs = {};
						for (i=0;i<select.attributes.length;i++) {
							if (select.attributes[i].name == "value") {
								val = select.attributes[i].value;
							} else {
								attrs[select.attributes[i].name] = select.attributes[i].value;
							}
						}
						$(select).replaceWith($.'.$module_name.'CP.createSelect(attrs, options, val));
					},
					createSelect: function(attributes, options, selected) {
						var select = "<select ";
						for (i in attributes) {
							select += i+ "=\""+attributes[i]+ "\"";
						}
						select += ">";
						for (i in options) {
							select += "<option value=\""+i+"\" ";
							if (selected != undefined && selected == i) {
								select += " selected=\"selected\"";
							}
							select += ">"+options[i]+"</option>";
						}
						select += "</select>";
						return select;
					}
				}

				jQuery(document).ready(function($){

					$("select.states").each(function(){
						$.'.$module_name.'CP.updateSelect(this, $.'.$module_name.'CP.states);
					});
					$("select.states_blank").each(function(){
						var states = {"" : "---"};
						$.extend(states, $.'.$module_name.'CP.states);
						$.'.$module_name.'CP.updateSelect(this, states);
					});
					$("select.templates").each(function(){
						$.'.$module_name.'CP.updateSelect(this, $.'.$module_name.'CP.templates);
					});
					$("select.templates_blank").each(function(){
						var templates = {"" : "---"};
						$.extend(templates, $.'.$module_name.'CP.templates);
						$.'.$module_name.'CP.updateSelect(this, templates);
					});
					$("select.statuses").each(function(){
						$.'.$module_name.'CP.updateSelect(this, $.'.$module_name.'CP.statuses);
					});
					$("select.statuses_blank").each(function(){
						var statuses = {"" : "---", "ANY" : "ANY"};
						$.extend(statuses, $.'.$module_name.'CP.statuses);
						$.'.$module_name.'CP.updateSelect(this, statuses);
					});

					$("select.countries").each(function(){
						$.'.$module_name.'CP.updateSelect(this, $.'.$module_name.'CP.countries);
					});
					$("select.countries_blank").each(function(){
						var countries = {"" : "---"};
						$.extend(countries, $.'.$module_name.'CP.countries);
						$.'.$module_name.'CP.updateSelect(this, countries);
					});
					$("select.states_and_countries").each(function(){
						$.'.$module_name.'CP.updateSelect(this, $.'.$module_name.'CP.statesAndCountries);
					});
					$("select.all_fields").each(function(){
						var fields = {"":"---"};
						for (i in $.'.$module_name.'CP.fields) {
							for (j in $.'.$module_name.'CP.fields[i]) {
								fields["field_id_"+$.'.$module_name.'CP.fields[i][j].field_id] = $.'.$module_name.'CP.fields[i][j].field_label;
							}
						}
						$.'.$module_name.'CP.updateSelect(this, fields);
					});
					$("select.product_channel_fields").each(function(){
						var product_channel_fields = {"":"---"};
						for (i in $.'.$module_name.'CP.product_channel_fields) {
							for (j in $.'.$module_name.'CP.product_channel_fields[i]) {
								product_channel_fields["field_id_"+$.'.$module_name.'CP.product_channel_fields[i][j].field_id] = $.'.$module_name.'CP.product_channel_fields[i][j].field_label;
							}
						}
						$.'.$module_name.'CP.updateSelect(this, product_channel_fields);
					});
					$("select.channels").each(function(){
						$.'.$module_name.'CP.updateSelect(this, $.'.$module_name.'CP.channels);
					});
					$("select.product_channels").each(function(){
						$.'.$module_name.'CP.updateSelect(this, $.'.$module_name.'CP.product_channels);
					});
					
					$.'.$module_name.'CP.checkSelectedChannel("#select_orders", ".requires_orders_channel"); 
					
					
					
					$("#select_orders").bind("change", function(){
						$.'.$module_name.'CP.checkSelectedChannel("#select_orders", ".requires_orders_channel"); 
					});
					
					
					$("select.product_channels").bind("change", function(){
						var channel_id = Number($(this).val());
						var section = $(this).attr("id").replace("select_", "");
						$("select.field_"+section).children().not(".blank").remove();
			 			if ($(this).val() != "")
						{
							for (i in $.'.$module_name.'CP.product_channel_fields[channel_id])
							{
								$("select.field_"+section).append("<option value=\""+$.'.$module_name.'CP.product_channel_fields[channel_id][i].field_id+"\">"+$.'.$module_name.'CP.product_channel_fields[channel_id][i].field_label+"</option>");
							}

						}
					});

					$("#'.$module_name.'_tab").val($.'.$module_name.'CP.currentSection() );

					var count = 0; 
					'.$add_new_setting_js.'

					$("a.remove_matrix_row").live("click", function(){
						if (confirm("Are you sure you want to delete this row?"))
						{
							if ($(this).parent().get(0).tagName.toLowerCase() == "td")
							{
								$(this).parent().parent().remove();
							}
							else
							{
								$(this).parent().remove();
							}
						}
						return false;
					}).live("mouseover", function(){
						$(this).find("img").animate({opacity:1});
						console.log("in");
					}).live("mouseout", function(){
						console.log("out");
						$(this).find("img").animate({opacity:.2});
					}).find("img").css({opacity:.2});


					$(".add_matrix_row").bind("click", function(){
						var name = $(this).attr("id").replace("_button", "");
						var index = ($("."+name+"_row:last").length > 0) ? Number($("."+name+"_row:last").attr("id").replace(name+"_row_","")) + 1 : 0;
						var clone = $("#"+name+"_row_blank").clone(); 
						clone.attr("id", name+"_row_"+index).addClass(name+"_row").show();
						clone.find(":input").bind("each", function(){
							$(this).attr("name", $(this).attr("data-hash").replace("INDEX", index));
						});
						$(this).parent().before(clone);
						return false;
					});

					// Return a helper with preserved width of cells
					var fixHelper = function(e, ui) {
						ui.children().each(function() {
							$(this).width($(this).width());
						});
						return ui;
					};

					$("div.matrix table tbody").sortable({
						helper: fixHelper,
						stop: function(event, ui) { 
							var count=0; 
							$("div.matrix table tbody tr").each(function(){
								$(this).find(":input").each(function(){
			 						$(this).attr("name", $(this).parents("tr").attr("rel")+"["+count+"]["+$(this).parent().attr("rel")+"]");	
								}); 
								count +=1; 
							});
						}
					});


				});
 
			 	';

 			$content.='</script>';
			
			return $content; 
		}
		public function view_plugin_settings($data)
		{
			extract($data); 
			foreach ($plugins as $plugin)
			{
				$content =""; 
				$content .='<div class="'.$plugin_type.'_settings" id="'.$plugin['classname'].'">

					<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
						<thead class="">
							<tr>
								<th colspan="2">
									<strong>'.lang($plugin['title']).' '.lang('settings').'</strong><br />
								</th>
							</tr>
						</thead>
						<tbody>';

							if (!empty($plugin['note']))
							{
								$content .='
								<tr class="'.alternator('odd', 'even').'">
									<td colspan="2">
										<div class="subtext note">'.lang('gateway_settings_note_title').'</div>
										'.lang($plugin['note']).'
									</td>
								</tr>';
							} 

							if (!empty($plugin['overview']))
							{
								$content .='
								<tr class="'.alternator('odd', 'even').'">
									<td colspan="2">
				 						<div class="ct_overview">
											'.lang($plugin['overview']).'
										</div>
									</td>
								</tr>
								';
							}

							if (!empty($plugin['affiliate']))
							{
								$content .='
								<tr class="'.alternator('odd', 'even').'">
									<td>
										<div class="subtext">'.lang('gateway_settings_affiliate_title').'</div>
									</td>
									<td style="width:50%;">
										'.lang($plugin['affiliate']).'
									</td>
								</tr>';
							}
						$content .='	
						</tbody>
					</table>';

					if (is_array($plugin['settings']))
					{
						foreach ($plugin['settings'] as $setting)
						{
							if ($setting['type'] == 'matrix')
							{
								    //retrieve the current set value of the field
								    $current_values = (isset($settings[$plugin['classname'].'_settings'][$setting['short_name']])) ?
								 		$settings[$plugin['classname'].'_settings'][$setting['short_name']] : FALSE;

								    //set the value to the default value if there is no set value and the default value is defined
								    $current_values = ($current_values === FALSE && isset($setting['default'])) ? 
										$setting['default'] : $current_values;

								$content .='
								<div class="matrix">
									<table cellpadding="0" cellspacing="0" border="0" class="mainTable padTable">
										<thead>
										    <tr>
												<th></th>';

												foreach ($setting['settings'] as $count => $matrix_setting) 
												{
													$style=""; 
												    $setting['settings'][$count]['style'] = $style;

													$content .='
				 									<th>
														<strong>'.lang($matrix_setting['name']).'</strong>'.(isset($matrix_setting['note'])) ? '<br />'.lang($matrix_setting['note']) : ''.'
													</th>';
												}

												$content .='<th style="width:20px;"></th>
										    </tr>
										</thead>
										<tbody>';

									if ($current_values === FALSE || ! count($current_values))
									{
										$current_values = array(array());
										foreach ($setting['settings'] as $matrix_setting)
										{
											$current_values[0][$matrix_setting['short_name']] = isset($matrix_setting['default']) ? $matrix_setting['default'] : '';
										}
									}
			 						foreach ($current_values as $count => $current_value) 
									{
										$content .='
										<tr class="'.$plugin['classname'].'_'.$setting['short_name'].'_setting" 
											rel = "'.$plugin['classname'].'_settings['.$setting['short_name'].']'.'" 		
											id="'.$plugin['classname'].'_'.$setting['short_name'].'_setting_'.$count.'">
											<td><img border="0" src="'.$this->config->item('theme_folder_url').'third_party/cartthrob/images/ct_drag_handle.gif" width="10" height="17" /></td>';
											foreach ($setting['settings'] as $matrix_setting) 
											{
												$content .='<td  style="'.$matrix_setting['style'].'" rel="'.$matrix_setting['short_name'].'">'.$cartthrob_mcp->plugin_setting($matrix_setting['type'], $plugin['classname'].'_settings['.$setting['short_name'].']['.$count.']['.$matrix_setting['short_name'].']', @$current_value[$matrix_setting['short_name']], @$matrix_setting['options'], @$matrix_setting['attributes']).'</td>';									
											}

											$content .='

											<td>
												<a href="#" class="remove_matrix_row">
													<img border="0" src="'.$this->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png" />
												</a>
											</td>
										</tr>	';						
									}

									$content.='</tbody>
									</table>
								</div>

								<fieldset class="plugin_add_new_setting">
									<a href="#" class="ct_add_matrix_row" id="add_new_'.$plugin['classname'].'_'.$setting['short_name'].'">
										'.lang('add_another_row').'
									</a>
								</fieldset>

								<table style="display: none;" class="'.$plugin['classname'].'">
									<tr id="'.$plugin['classname'].'_'.$setting['short_name'].'_blank" class="'.$setting['short_name'].'">
										<td  ><img border="0" src="'.$this->config->item('theme_folder_url').'third_party/cartthrob/images/ct_drag_handle.gif" width="10" height="17" /></td>';

										foreach ($setting['settings'] as $matrix_setting)
										{
											$content .='<td  class="'.$matrix_setting['short_name'].'" style="'.$matrix_setting['style'].'">'.$cartthrob_mcp->plugin_setting($matrix_setting['type'], '', (isset($matrix_setting['default'])) ? $matrix_setting['default'] : '', @$matrix_setting['options'], @$matrix_setting['attributes']).'</td>';							
										}

										$content.='
										<td>
											<a href="#" class="remove_matrix_row"><img border="0" src="'.$this->config->item('theme_folder_url').'cp_themes/default/images/content_custom_tab_delete.png" /></a>
										</td>
									</tr>
								</table>';
							}
							elseif ($setting['type'] == 'header')
							{
								$content .='
									<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
										<thead class="">
											<tr>
												<th colspan="2">
													<strong>'.$setting['name'].'</strong><br />
												</th>
											</tr>
										</thead>
									</table>';
							}
							else 
							{
								//retrieve the current set value of the field
								$current_value = (isset($settings[$plugin['classname'].'_settings'][$setting['short_name']])) ? $settings[$plugin['classname'].'_settings'][$setting['short_name']] : FALSE;
								//set the value to the default value if there is no set value and the default value is defined
								$current_value = ($current_value === FALSE && isset($setting['default'])) ? $setting['default'] : $current_value;

								$content .='
									<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
									<tbody>
										<tr class="even">
											<td>
												<label>'.lang($setting['name']).'</label><br><span class="subtext">'.(isset($setting['note'])) ? lang($setting['note']) : ''.'</span>
			 								</td>
											<td style="width:50%;">
												'.$cartthrob_mcp->plugin_setting($setting['type'], $plugin['classname'].'_settings['.$setting['short_name'].']', $current_value, @$setting['options'], @$setting['attributes']).'
											</td>
										</tr>
									</tbody>
									</table>';
							}
						}
					}

				$content .='</div>';
			}
			return $content; 
		}
	} // END CLASS

}