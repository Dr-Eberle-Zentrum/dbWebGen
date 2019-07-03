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
		public function is_richtext() { // default: false
		//--------------------------------------------------------------------------------------
			return isset($this->field['richtext']);
		}

		//--------------------------------------------------------------------------------------
		public function get_richtext_options_js() { // default: empty
		//--------------------------------------------------------------------------------------
			$options = isset($this->field['richtext']['init_options']) 
				? $this->field['richtext']['init_options']
				: array();
			if(DBWEBGEN_LANG !== 'en')
				$options['lang'] = DBWEBGEN_LANG;
			return json_encode($options, JSON_FORCE_OBJECT);
		}

		//--------------------------------------------------------------------------------------
		public function get_richtext_editor() {
		//--------------------------------------------------------------------------------------
			return $this->field['richtext']['editor'];
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
			if($this->is_richtext()) {
				switch($this->get_richtext_editor()) {
					case 'trumbowyg': {
						$output_buf .= sprintf(
							"<script>$(document).ready(() => $('#%s').trumbowyg(%s))</script>\n",
							$this->get_control_id(),
							$this->get_richtext_options_js()
						);
						break;
					}
				}
			}
			return $output_buf;
		}
	}
?>
