<?php
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
		public function /*bool*/ is_included_in_global_search() {
		//--------------------------------------------------------------------------------------
			return true;
		}

		//--------------------------------------------------------------------------------------
		protected function /*string*/ render_internal(&$output_buf) {
		// render_settings: form_method, name_attr, id_attr, disabled, focus
		//--------------------------------------------------------------------------------------
			$output_buf .= sprintf("<select %s %s %s class='form-control' id='%s' name='%s' %s data-placeholder='%s' title='%s'>\n",
				$this->get_disabled_attr(),
				$this->get_required_attr(),
				$this->get_focus_attr(),
				$this->get_control_id(),
				$this->get_control_name(),
				$this->is_required() ? '' : 'data-allow-clear=true',
				unquote($this->get_custom_placeholder(l10n('lookup-field.placeholder'))),
				unquote($this->get_label())
			);

			$output_buf .= "<option value=''></option>\n";

			$selection_done = '';
			foreach($this->get_enum_values() as $val => $text) {
				if($selection_done != 'done') {
					$sel = ($this->has_submitted_value() && $this->get_submitted_value() === strval($val) ? ' selected="selected" ' : '');

					if($sel != '')
						$selection_done = 'done';
					elseif($sel == '' && $this->is_required() && strval($this->get_default_value()) === strval($val)) {
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
