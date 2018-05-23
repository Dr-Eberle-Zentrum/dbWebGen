<?php
	//==========================================================================================
	class TextLineField extends SingleLineTextInputField
	//==========================================================================================
	{
		//--------------------------------------------------------------------------------------
		protected function /*string*/ render_internal(&$output_buf) {
		// render_settings: form_method, name_attr, id_attr
		//--------------------------------------------------------------------------------------
			$output_buf .= sprintf(
				"<input %s %s type='text' class='form-control' id='%s' name='%s' %s value='%s' %s placeholder='%s' title='%s' />\n%s",
				$this->get_disabled_attr(),
				$this->get_required_attr(),
				$this->get_control_id(),
				$this->get_control_name(),
				$this->get_maxlen_attr(),
				unquote($this->get_submitted_value('')),
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
			return true;
		}
	}
?>
