<?php
	include 'grocery_crud.php';
	class Grocery_crud_with_view extends grocery_CRUD {
		
		private $view_fields_checked	= false;
		protected $unset_view_fields	= null;
		protected $unset_view			= false;
		protected $js_config_files			= array();
//		protected $state_code 			= null;
		
		public function __construct()
		{
			$this->states[18] = 'view';	
		}
		
		/**
		 * Unsets the edit operation from the list
		 *
		 * @return	void
		 */
		public function unset_view()
		{
			$this->unset_view = true;
		
			return $this;
		}
		
		/**
		 * Unsets all the operations from the list
		 *
		 * @return	void
		 */
		public function unset_operations()
		{
			parent::unset_operations();
			$this->unset_view 	= true;
			return $this;
		}
		
		public function get_field_types() {
			$types = parent::get_field_types();
			if($types == null) {
				return $this->field_types;
			}
			
			if(!empty($this->view_fields))
				foreach($this->view_fields as $field_object)
				{
					$field_name = isset($field_object->field_name) ? $field_object->field_name : $field_object;
			
					if(!isset($types[$field_name]))//Doesn't exist in the database? Create it for the CRUD
					{
						$extras = false;
						if($this->change_field_type !== null && isset($this->change_field_type[$field_name]))
						{
							$field_type = $this->change_field_type[$field_name];
							$extras 	=  $field_type->extras;
						}
			
						$field_info = (object)array(
								'name' => $field_name,
								'crud_type' => $this->change_field_type !== null && isset($this->change_field_type[$field_name]) ?
								$this->change_field_type[$field_name]->type :
								'string',
								'display_as' => isset($this->display_as[$field_name]) ?
								$this->display_as[$field_name] :
								ucfirst(str_replace("_"," ",$field_name)),
								'required'	=> in_array($field_name,$this->required_fields) ? true : false,
								'extras'	=> $extras
						);
			
						$types[$field_name] = $field_info;
					}
				}
			$this->field_types = $types;
			return $this->field_types;
		}

		public function unset_fields()
		{
			$args = func_get_args();
		
			if(isset($args[0]) && is_array($args[0]))
			{
				$args = $args[0];
			}
		
			$this->unset_add_fields = $args;
			$this->unset_edit_fields = $args;
			$this->unset_view_fields = $args;
		
			return $this;
		}
		
		public function unset_view_fields()
		{
			$args = func_get_args();
		
			if(isset($args[0]) && is_array($args[0]))
			{
				$args = $args[0];
			}
		
			$this->unset_view_fields = $args;
		
			return $this;
		}
		
		protected function getViewUrl($primary_key = null)
		{
			if($primary_key === null)
				return $this->state_url('view');
			else
				return $this->state_url('view/'.$primary_key);
		}
		
		
		protected function showList($ajax = false, $state_info = null)
		{
			$data = $this->get_common_data();
		
			$data->order_by 	= $this->order_by;
		
			$data->types 		= $this->get_field_types();
		
			$data->list = $this->get_list();
			$data->list = $this->change_list($data->list , $data->types);
			$data->list = $this->change_list_add_actions($data->list);
		
			$data->total_results = $this->get_total_results();
		
			$data->columns 				= $this->get_columns();
		
			$data->success_message		= $this->get_success_message_at_list($state_info);
		
			$data->primary_key 			= $this->get_primary_key();
			$data->add_url				= $this->getAddUrl();
			$data->view_url				= $this->getViewUrl();
			$data->edit_url				= $this->getEditUrl();
			$data->delete_url			= $this->getDeleteUrl();
			$data->ajax_list_url		= $this->getAjaxListUrl();
			$data->ajax_list_info_url	= $this->getAjaxListInfoUrl();
			$data->export_url			= $this->getExportToExcelUrl();
			$data->print_url			= $this->getPrintUrl();
			$data->actions				= $this->actions;
			$data->unique_hash			= $this->get_method_hash();
			$data->order_by				= $this->order_by;
		
			$data->unset_add			= $this->unset_add;
			$data->unset_edit			= $this->unset_edit;
			$data->unset_view			= $this->unset_view;
			$data->unset_delete			= $this->unset_delete;
			$data->unset_export			= $this->unset_export;
			$data->unset_print			= $this->unset_print;
		
			$default_per_page = $this->config->default_per_page;
			$data->paging_options = array('10','25','50','100');
			$data->default_per_page		= is_numeric($default_per_page) && $default_per_page >1 && in_array($default_per_page,$data->paging_options)? $default_per_page : 25;
		
			if($data->list === false)
			{
				throw new Exception('It is impossible to get data. Please check your model and try again.', 13);
				$data->list = array();
			}
		
			foreach($data->list as $num_row => $row)
			{
				$data->list[$num_row]->edit_url = $data->edit_url.'/'.$row->{$data->primary_key};
				$data->list[$num_row]->view_url = $data->view_url.'/'.$row->{$data->primary_key};
				$data->list[$num_row]->delete_url = $data->delete_url.'/'.$row->{$data->primary_key};
			}
		
			if(!$ajax)
			{
				//$this->_add_js_vars(array('dialog_forms' => $this->config->dialog_forms));
		
				$data->list_view = $this->_theme_view('list_with_view.php',$data,true);
				$this->_theme_view('list_template.php',$data);
			}
			else
			{
				$this->set_echo_and_die();
				$this->_theme_view('list_with_view.php',$data);
			}
		}
		
		protected function showViewPage($state_info)
		{
			//$this->set_js_lib($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);
			$this->set_js($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);
		
			$data 				= $this->get_common_data();
			//$data->types 		= $this->get_field_types();
		
			$data->field_values = $this->get_edit_values($state_info->primary_key);
		
			$data->list_url 	= $this->getListUrl();
			$data->view_url 	= $this->getViewUrl();
			$data->delete_url	= $this->getDeleteUrl($state_info);
			$data->add_url		= $this->getAddUrl();
			$data->input_fields = $this->get_view_input_fields($data->field_values);
			$data->unique_hash	= $this->get_method_hash();
		
			$data->fields 		= $this->get_view_fields();
			//$data->is_ajax 			= $this->_is_ajax();
		
			$this->_theme_view('view.php',$data);
			$this->_inline_js("var js_date_format = '".$this->js_date_format."';");
		
			//$this->_get_ajax_results();
		}		
		/**
		 *
		 * Or else ... make it work! The web application takes decision of what to do and show it to the final user.
		 * Without this function nothing works. Here is the core of grocery CRUD project.
		 *
		 * @access	public
		 */
		public function render()
		{
			$this->pre_render();
		
			if( $this->state_code != 0 )
			{
				$this->state_info = $this->getStateInfo();
			}
			else
			{
				throw new Exception('The state is unknown , I don\'t know what I will do with your data!', 4);
				die();
			}
		
			switch ($this->state_code) {
				case 15://success
				case 1://list
					if($this->unset_list)
					{
						throw new Exception('You don\'t have permissions for this operation', 14);
						die();
					}
		
					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();
		
					$this->set_basic_Layout();
		
					$state_info = $this->getStateInfo();
		
					$this->showList(false,$state_info);
		
					break;
		
				case 2://add
					if($this->unset_add)
					{
						throw new Exception('You don\'t have permissions for this operation', 14);
						die();
					}
		
					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();
		
					$this->set_basic_Layout();
		
					$this->showAddForm();
		
					break;
		
				case 3://edit
					if($this->unset_edit)
					{
						throw new Exception('You don\'t have permissions for this operation', 14);
						die();
					}
		
					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();
		
					$this->set_basic_Layout();
		
					$state_info = $this->getStateInfo();
		
					$this->showEditForm($state_info);
		
					break;
		
				case 4://delete
					if($this->unset_delete)
					{
						throw new Exception('This user is not allowed to do this operation', 14);
						die();
					}
		
					$state_info = $this->getStateInfo();
					$delete_result = $this->db_delete($state_info);
		
					$this->delete_layout( $delete_result );
					break;
		
				case 5://insert
					if($this->unset_add)
					{
						throw new Exception('This user is not allowed to do this operation', 14);
						die();
					}
		
					$state_info = $this->getStateInfo();
					$insert_result = $this->db_insert($state_info);
		
					$this->insert_layout($insert_result);
					break;
		
				case 6://update
					if($this->unset_edit)
					{
						throw new Exception('This user is not allowed to do this operation', 14);
						die();
					}
		
					$state_info = $this->getStateInfo();
					$update_result = $this->db_update($state_info);
		
					$this->update_layout( $update_result,$state_info);
					break;
		
				case 7://ajax_list
		
					if($this->unset_list)
					{
						throw new Exception('You don\'t have permissions for this operation', 14);
						die();
					}
		
					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();
		
					$this->set_basic_Layout();
		
					$state_info = $this->getStateInfo();
					$this->set_ajax_list_queries($state_info);
		
					$this->showList(true);
		
					break;
		
				case 8://ajax_list_info
		
					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();
		
					$this->set_basic_Layout();
		
					$state_info = $this->getStateInfo();
					$this->set_ajax_list_queries($state_info);
		
					$this->showListInfo();
					break;
		
				case 9://insert_validation
		
					$validation_result = $this->db_insert_validation();
		
					$this->validation_layout($validation_result);
					break;
		
				case 10://update_validation
		
					$validation_result = $this->db_update_validation();
		
					$this->validation_layout($validation_result);
					break;
		
				case 11://upload_file
		
					$state_info = $this->getStateInfo();
		
					$upload_result = $this->upload_file($state_info);
		
					$this->upload_layout($upload_result, $state_info->field_name);
					break;
		
				case 12://delete_file
					$state_info = $this->getStateInfo();
		
					$delete_file_result = $this->delete_file($state_info);
		
					$this->delete_file_layout($delete_file_result);
					break;
					/*
					 case 13: //ajax_relation
					$state_info = $this->getStateInfo();
		
					$ajax_relation_result = $this->ajax_relation($state_info);
		
					$ajax_relation_result[""] = "";
		
					echo json_encode($ajax_relation_result);
					die();
					break;
		
					case 14: //ajax_relation_n_n
					echo json_encode(array("34" => 'Johnny' , "78" => "Test"));
					die();
					break;
					*/
				case 16: //export to excel
					//a big number just to ensure that the table characters will not be cutted.
					$this->character_limiter = 1000000;
		
					if($this->unset_export)
					{
						throw new Exception('You don\'t have permissions for this operation', 15);
						die();
					}
		
					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();
		
					$this->set_basic_Layout();
		
					$state_info = $this->getStateInfo();
					$this->set_ajax_list_queries($state_info);
					$this->exportToExcel($state_info);
					break;
		
				case 17: //print
					//a big number just to ensure that the table characters will not be cutted.
					$this->character_limiter = 1000000;
		
					if($this->unset_print)
					{
						throw new Exception('You don\'t have permissions for this operation', 15);
						die();
					}
		
					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();
		
					$this->set_basic_Layout();
		
					$state_info = $this->getStateInfo();
					$this->set_ajax_list_queries($state_info);
					$this->print_webpage($state_info);
					break;
				case 18://view
					if($this->unset_view)
					{
						throw new Exception('You don\'t have permissions for this operation', 14);
						die();
					}
		
					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();
		
					$this->set_basic_Layout();
		
					$state_info = $this->getStateInfo();
					
					$this->showViewPage($state_info);
		
					break;
			}
		
			return $this->get_layout();
		}		
		
		public function getStateInfo() {
			$state_code = $this->getStateCode();
			if($state_code == 18) {
				$state_code = $this->getStateCode();
				$segment_object = $this->get_state_info_from_url();

				$first_parameter = $segment_object->first_parameter;
				$second_parameter = $segment_object->second_parameter;			
				if($state_code == 18) {
					if($first_parameter !== null)
					{
						$state_info = (object)array('primary_key' => $first_parameter);
					}
					else
					{
						throw new Exception('On the state "view" the Primary key cannot be null', 6);
						die();
					}
				}			
			} else {
				$state_info = parent::getStateInfo();
			}
			return $state_info;
		}
		
		/**
		 *
		 * Enter description here ...
		 */
		protected function get_view_fields()
		{
			if($this->view_fields_checked === false)
			{
				$field_types = $this->get_field_types();
				if(!empty($this->view_fields))
				{
					foreach($this->view_fields as $field_num => $field)
					{
						if(isset($this->display_as[$field]))
							$this->view_fields[$field_num] = (object)array('field_name' => $field, 'display_as' => $this->display_as[$field]);
						else
							$this->view_fields[$field_num] = (object)array('field_name' => $field, 'display_as' => $field_types[$field]->display_as);
					}
				}
				else
				{
					$this->view_fields = array();
					foreach($field_types as $field)
					{
						//Check if an unset_view_field is initialize for this field name
						if($this->unset_view_fields !== null && is_array($this->unset_view_fields) && in_array($field->name,$this->unset_view_fields))
							continue;
		
						if(!isset($field->db_extra) || $field->db_extra != 'auto_increment')
						{
							if(isset($this->display_as[$field->name]))
								$this->view_fields[] = (object)array('field_name' => $field->name, 'display_as' => $this->display_as[$field->name]);
							else
								$this->view_fields[] = (object)array('field_name' => $field->name, 'display_as' => $field->display_as);
						}
					}
				}
		
				$this->view_fields_checked = true;
			}
			return $this->view_fields;
		}

		/**
		 *
		 * Enter description here ...
		 * @param string $field
		 * @param mixed $callback
		 */
		public function callback_view_field($field, $callback = null)
		{
			$this->callback_view_field[$field] = $callback;
		
			return $this;
		}
		
		protected function get_view_input_fields($field_values = null)
		{
			$fields = $this->get_view_fields();
			$types 	= $this->get_field_types();
		
			$input_fields = array();
		
			foreach($fields as $field_num => $field)
			{
				$field_info = $types[$field->field_name];
		
				$field_value = !empty($field_values) && isset($field_values->{$field->field_name}) ? $field_values->{$field->field_name} : null;
				if(!isset($this->callback_view_field[$field->field_name]))
				{
					$field_input = $this->get_field_view($field_info, $field_value);
				}
				else
				{
					$primary_key = $this->getStateInfo()->primary_key;
					$field_input = $field_info;
					$field_input->input = call_user_func($this->callback_view_field[$field->field_name], $field_value, $primary_key, $field_info, $field_values);
				}
		
				switch ($field_info->crud_type) {
					case 'invisible':
						unset($this->view_fields[$field_num]);
						unset($fields[$field_num]);
						continue;
						break;
					case 'hidden':
						$this->edit_hidden_fields[] = $field_input;
						unset($this->view_fields[$field_num]);
						unset($fields[$field_num]);
						continue;
						break;
				}
		
				$input_fields[$field->field_name] = $field_input;
			}
		
			return $input_fields;
		}
		
		/**
		 * Load Javascripts
		 **/
		protected function load_js_fancybox()
		{
			$this->set_css($this->default_css_path.'/jquery_plugins/fancybox/jquery.fancybox.css');

			$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.fancybox-1.3.4.js');
			$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.easing-1.3.pack.js');
		}
		
		public function set_js_config($js_file)
		{
			$this->js_config_files[sha1($js_file)] = base_url().$js_file;
			$this->js_files[sha1($js_file)] = base_url().$js_file;
		}
		
		
		protected function get_field_view($field_info, $value = null)
		{
			$field_info->input = $value;
			$real_type = $field_info->crud_type;
		
			if($real_type == 'date' || $real_type == 'datetime') {
				$date = $value;
				if(trim($date) == "") {
					$field_info->input = "";
				} else {
					if(strpos($date, " ") > 1) {
						list($dt, $time) = explode(" ", $date);
						list($year, $month, $day) = explode("-", $dt);
						return 	"$month-$day-$year $time";
					} else {
					list($year, $month, $day) = explode("-", $date);
					}
					$field_info->input = "$month-$day-$year";
				}
			}

			if($real_type == 'upload_file') {
				//Fancybox
				$unique = uniqid();
				$input = '';
				$this->load_js_fancybox();
							
				$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.fancybox.config.js');
				$file_display_none  	= empty($value) ?  "display:none;" : "";
							
				$is_image = !empty($value) &&
				( substr($value,-4) == '.jpg'
						|| substr($value,-4) == '.png'
						|| substr($value,-5) == '.jpeg'
								|| substr($value,-4) == '.gif'
										|| substr($value,-5) == '.tiff')
								? true : false;
											
				$image_class = $is_image ? 'image-thumbnail' : '';
				$file_url = base_url().$field_info->extras->upload_path.'/'.$value;
				$input .= "<a href='".$file_url."' id='file_$unique' class='open-file";
				$input .= $is_image ? " $image_class'><img src='".$file_url."' height='50px'>" : "' target='_blank'>$value";
				$input .= "</a> ";
				$field_info->input = $input;
			}
		
			if($real_type == 'relation') {
				$options_array = $this->get_relation_array($field_info->extras);
				foreach($options_array as $option_value => $option)
				{
					if(!empty($value) && $value == $option_value) {
						$field_info->input = $option;
						break;
					}
				}
			}
		
			if($real_type == 'text') {
					$field_info->input = nl2br(htmlspecialchars($value));
			}
		
			if($real_type == 'true_false') {
				$true_string = is_array($field_info->extras) && array_key_exists(1,$field_info->extras) ? $field_info->extras[1] : $this->default_true_false_text[1];
				$false_string =  is_array($field_info->extras) && array_key_exists(0,$field_info->extras) ? $field_info->extras[0] : $this->default_true_false_text[0];
				if($value == 1) {
					$field_info->input = $true_string;
				} else if($value == 0) {
					$field_info->input = $false_string;
				} else {
				$field_info->input = "";
				}
			}

			return $field_info;
		}
		
		public function get_primary_key()
		{
				return $this->basic_model->get_primary_key();
		}
		
	}