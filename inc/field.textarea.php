<?php
	//==========================================================================================
	class TextAreaField extends TextFieldBase
	//==========================================================================================
	{
		//--------------------------------------------------------------------------------------
		public function get_num_rows() { // default: 5
		//--------------------------------------------------------------------------------------
			return isset($this->field['height_rows']) ? $this->field['height_rows'] : 5;
		}

		//--------------------------------------------------------------------------------------
		public function get_resize_classname() {
		//--------------------------------------------------------------------------------------
			return !isset($this->field['resizeable']) || $this->field['resizeable'] === true ? 'vresize' : 'noresize';
		}

		//--------------------------------------------------------------------------------------
		public function /*bool*/ is_included_in_global_search() {
		//--------------------------------------------------------------------------------------
			return true;
		}

		//--------------------------------------------------------------------------------------
		protected function /*string*/ render_internal(&$output_buf) {
		// render_settings: form_method, name_attr, id_attr
		//--------------------------------------------------------------------------------------
			$output_buf .= sprintf(
				"<textarea %s %s class='form-control %s' id='%s' name='%s' %s rows='%s' %s placeholder='%s' title='%s'>%s</textarea>\n%s",
				$this->get_disabled_attr(),
				$this->get_required_attr(),
				$this->get_resize_classname(),
				$this->get_control_id(),
				$this->get_control_name(),
				$this->get_maxlen_attr(),
				$this->get_num_rows(),
				$this->get_focus_attr(),
				unquote($this->get_custom_placeholder('')),
				unquote($this->get_label()),
				html($this->get_submitted_value('')),
				$this->get_remaining_chars_display()
			);
			return $output_buf;
		}
	}
?>
