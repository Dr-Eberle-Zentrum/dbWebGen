<?php
	//==========================================================================================
	class TextLineField extends SingleLineTextInputField
	//==========================================================================================
	{
		//--------------------------------------------------------------------------------------
		protected function /*string*/ render_internal(&$output_buf) {
		// render_settings: form_method, name_attr, id_attr
		//--------------------------------------------------------------------------------------
			if($this->has_datetime_picker()) {
				$output_buf .= "<div class='input-group date'>\n";
			}
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
			if($this->has_datetime_picker()) {
				$html = <<<JS
					<span class="input-group-addon">
						<span class="%s"></span>
					</span>
					</div>
					<script>
						$(document).ready(function() {
							var input = $('#%s');
							input.parents('div.date').first().datetimepicker(%s).on('dp.change', function() {
								update_null_value_checkbox(input);
							});
						});
					</script>
JS;
				$output_buf .= sprintf(
					$html,
					$this->get_datetime_picker_main_icon(),
					$this->get_control_id(),
					json_encode($this->get_datetime_picker_options())
				);
			}
			return $output_buf;
		}

		//--------------------------------------------------------------------------------------
		public function /*bool*/ is_included_in_global_search() {
		//--------------------------------------------------------------------------------------
			return true;
		}
	}
?>
