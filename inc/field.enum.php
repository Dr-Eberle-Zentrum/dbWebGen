<?
	//==========================================================================================
	class EnumField extends Field
	//==========================================================================================
	{
		//--------------------------------------------------------------------------------------
		public function get_enum_values() {
		//--------------------------------------------------------------------------------------
			return $this->field['values'];
		}
		
		//--------------------------------------------------------------------------------------
		// override parent
		public function allow_setnull_box() {
		//--------------------------------------------------------------------------------------
			return false;
		}
		
		//--------------------------------------------------------------------------------------		
		protected function /*string*/ render_internal(&$output_buf) {
		// render_settings: form_method, name_attr, id_attr, disabled, focus
		//--------------------------------------------------------------------------------------
			$output_buf .= sprintf("<select %s %s %s class='form-control' id='%s' name='%s'>\n",
				$this->get_disabled_attr(), 
				$this->get_required_attr(), 
				$this->get_focus_attr(), 
				$this->get_control_id(), 
				$this->get_control_name());
			
			if(!$this->is_required() && $this->is_null_option_allowed())
				$output_buf .= sprintf("<option value='%s'>&nbsp;</option>\n", NULL_OPTION);				
			
			$selection_done = '';				
			foreach($this->get_enum_values() as $val => $text) {				
				if($selection_done != 'done') {
					$sel = ($this->has_submitted_value() && $this->get_submitted_value() == $val ? ' selected="selected" ' : '');
					
					if($sel != '')
						$selection_done = 'done';
					elseif($sel == '' && $this->is_required() && $this->get_default_value() === strval($val)) {
						$sel = ' selected="selected" ';
						$selection_done = 'default';
					}
				}
				else
					$sel = '';
				
				$output_buf .= sprintf("<option value='%s'%s>%s</option>\n", $val, $sel, html($text));
			}
			$output_buf .= "</select>\n";			
			return $output_buf;
		}
	}
?>