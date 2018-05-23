<?php
	//==========================================================================================
	abstract class dbWebGenPage {
	//==========================================================================================


		//--------------------------------------------------------------------------------------
		public function __construct() {
		//--------------------------------------------------------------------------------------
		}

		//--------------------------------------------------------------------------------------
		public function get_post($name, $default = null) {
		//--------------------------------------------------------------------------------------
			return isset($_POST[$name]) ? $_POST[$name] : $default;
		}

		//--------------------------------------------------------------------------------------
		public function get_urlparam($name, $default = null) {
		//--------------------------------------------------------------------------------------
			return isset($_GET[$name]) ? $_GET[$name] : $default;
		}

		//--------------------------------------------------------------------------------------
		public function render_select($name, $default_value, $options) {
		//--------------------------------------------------------------------------------------
			$html = "<select class='form-control' id='$name' name='$name'>\n";
			$posted_value = $this->get_post($name); // might be null, if nothing was submitted
			foreach($options as $value => $label) {
				$is_checked = (!$this->has_post_values() && $value == $default_value)
					|| ($posted_value === null && $value == $default_value) // this might be the case where new select box was added later
					|| ($posted_value !== null && $posted_value == $value);
				$checked_attr = $is_checked ? 'selected' : '';
				$html .= "\n<option value='$value' $checked_attr>$label</option>";
			}
			$html .= "\n</select>";
			return $html;
		}

		//--------------------------------------------------------------------------------------
		public function render_textarea($name, $default_value, $css = '') {
		//--------------------------------------------------------------------------------------
			$value = html($this->get_post($name, $default_value));
			return "<textarea class='form-control $css' id='$name' name='$name'>{$value}</textarea>";
		}

		//--------------------------------------------------------------------------------------
		public function render_textbox($name, $default_value, $css = '') {
		//--------------------------------------------------------------------------------------
			$value = unquote($this->get_post($name, $default_value));
			return "<input class='form-control $css' type='text' value='$value' id='$name' name='$name'></input>";
		}

		//--------------------------------------------------------------------------------------
		public function render_radio($name, $value, $checked_default = false) {
		//--------------------------------------------------------------------------------------
			$checked_attr = $this->is_checked_radio_or_checkbox($name, $value, $checked_default) ? 'checked' : '';
			return "<input type='radio' value='$value' name='$name' $checked_attr>";
		}

		//--------------------------------------------------------------------------------------
		public function render_checkbox($name, $value, $checked_default = false, $css = '') {
		//--------------------------------------------------------------------------------------
			$checked_attr = $this-> is_checked_radio_or_checkbox($name, $value, $checked_default) ? 'checked' : '';
			return "<input class='$css' type='checkbox' value='$value' id='$name' name='$name' $checked_attr>";
		}

		//--------------------------------------------------------------------------------------
		public static function has_post_values() {
		//--------------------------------------------------------------------------------------
			return count($_POST) > 0;
		}

		//--------------------------------------------------------------------------------------
		protected function is_checked_radio_or_checkbox($name, $value, $checked_default) {
		//--------------------------------------------------------------------------------------
			$posted_value = $this->get_post($name);
			return (!$this->has_post_values() && $checked_default)
				|| ($posted_value === null && $checked_default) // this might be the case where new radio group was added later
				|| ($posted_value !== null && $posted_value == $value);
		}

		//--------------------------------------------------------------------------------------
		// abstract functions
		//--------------------------------------------------------------------------------------
		abstract public function render();
	};
?>
