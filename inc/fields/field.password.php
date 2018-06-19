<?php
	//==========================================================================================
	class PasswordField extends SingleLineTextInputField
	//==========================================================================================
	{
		//--------------------------------------------------------------------------------------
		protected function /*string*/ render_internal(&$output_buf) {
		// render_settings: form_method, name_attr, id_attr
		//--------------------------------------------------------------------------------------
			$output_buf .= sprintf(
				"<input %s %s type='password' class='form-control' id='%s' name='%s' %s value='' %s placeholder='%s' title='%s' />\n%s",
				$this->get_disabled_attr(),
				$this->get_required_attr(),
				$this->get_control_id(),
				$this->get_control_name(),
				$this->get_maxlen_attr(),
				$this->get_focus_attr(),
				unquote($this->get_custom_placeholder('')),
				unquote($this->get_label()),
				$this->get_remaining_chars_display()
			);
			return $output_buf;
		}

		//--------------------------------------------------------------------------------------
		public function /*bool*/ is_included_in_global_search() {
		//--------------------------------------------------------------------------------------
			return false;
		}

		//--------------------------------------------------------------------------------------
		public function /*string*/ get_global_search_condition(
			$param_name,
			$search_string_transformation,
			$table_qualifier = null)
		{
			return false;
		}
	}
?>
