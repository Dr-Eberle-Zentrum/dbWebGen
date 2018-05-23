<?php
	//==========================================================================================
	class NumberField extends Field
	//==========================================================================================
	{
		//--------------------------------------------------------------------------------------
		public function get_width() {
		//--------------------------------------------------------------------------------------
			return $this->has_fixed_column_width() ? $this->get_fixed_column_width() : 3;
		}
		//--------------------------------------------------------------------------------------
		public function has_minimum_value() {
		//--------------------------------------------------------------------------------------
			return isset($this->field['min']);
		}
		//--------------------------------------------------------------------------------------
		public function has_maximum_value() {
		//--------------------------------------------------------------------------------------
			return isset($this->field['max']);
		}
		//--------------------------------------------------------------------------------------
		public function has_value_step() {
		//--------------------------------------------------------------------------------------
			return isset($this->field['step']);
		}
		//--------------------------------------------------------------------------------------
		public function get_minimum_value($value_if_missing = '') {
		//--------------------------------------------------------------------------------------
			return $this->has_minimum_value() ? $this->field['min'] : $value_if_missing;
		}
		//--------------------------------------------------------------------------------------
		public function get_maximum_value($value_if_missing = '') {
		//--------------------------------------------------------------------------------------
			return $this->has_maximum_value() ? $this->field['max'] : $value_if_missing;
		}
		//--------------------------------------------------------------------------------------
		public function get_value_step($value_if_missing = '') {
		//--------------------------------------------------------------------------------------
			return $this->has_value_step() ? $this->field['step'] : $value_if_missing;
		}
		//--------------------------------------------------------------------------------------
		public function get_minimum_value_attr() {
		//--------------------------------------------------------------------------------------
			return $this->has_minimum_value() ? sprintf("min='%s'", $this->get_minimum_value()) : '';
		}
		//--------------------------------------------------------------------------------------
		public function get_maximum_value_attr() {
		//--------------------------------------------------------------------------------------
			return $this->has_maximum_value() ? sprintf("max='%s'", $this->get_maximum_value()) : '';
		}
		//--------------------------------------------------------------------------------------
		public function get_value_step_attr() {
		//--------------------------------------------------------------------------------------
			return $this->has_value_step() ? sprintf("step='%s'", $this->get_value_step()) : '';
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
				"<input %s %s %s %s %s type='number' class='form-control' id='%s' name='%s' value='%s' %s placeholder='%s' title='%s' />\n",
				$this->get_disabled_attr(),
				$this->get_required_attr(),
				$this->get_minimum_value_attr(),
				$this->get_maximum_value_attr(),
				$this->get_value_step_attr(),
				$this->get_control_id(),
				$this->get_control_name(),
				unquote($this->get_submitted_value('')),
				$this->get_focus_attr(),
				unquote($this->get_custom_placeholder('')),
				unquote($this->get_label())
			);
			return $output_buf;
		}
	}
?>
