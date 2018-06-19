<?php
	//==========================================================================================
	class PostgisGeomField extends SingleLineTextInputField
	//==========================================================================================
	{
		public function has_map_picker() { return isset($this->field['map_picker']); }
		public function get_srid() { return intval($this->field['SRID']); }
		public function get_script($default = null) {
			if(!$this->has_map_picker() || !isset($this->field['map_picker']['script']))
				return $default;
			return $this->field['map_picker']['script'];
		}
		public function get_map_options($default = array()) {
			if(!$this->has_map_picker() || !isset($this->field['map_picker']['map_options']))
				return $default;
			return $this->field['map_picker']['map_options'];
		}
		public function get_draw_options($default = array()) {
			if(!$this->has_map_picker() || !isset($this->field['map_picker']['draw_options']))
				return $default;
			return $this->field['map_picker']['draw_options'];
		}

		//--------------------------------------------------------------------------------------
		public function render_map_picker_button($label, $glyphicon, $title, $readonly, $css_class, $val = null) {
		//--------------------------------------------------------------------------------------
			$url_params = array(
				'mode' => MODE_MAP_PICKER,
				'table' => $this->table_name,
				'field' => $this->field_name,
				'ctrl_id' => $this->get_control_id(),
				'readonly' => $readonly ? 'true' : 'false'
			);
			if($val !== null)
				$url_params['val'] = $val;
			return sprintf(
				"<a role='button' class='%s' data-target-ctrl='%s' data-map-url='?%s' title='%s' formnovalidate><span class='glyphicon glyphicon-%s'></span> %s</a>",
				$css_class,
				$this->get_control_id(),
				http_build_query($url_params),
				unquote($title),
				$glyphicon,
				html($label)
			);
		}

		//--------------------------------------------------------------------------------------
		protected function /*string*/ render_internal(&$output_buf) {
		// render_settings: form_method, name_attr, id_attr
		//--------------------------------------------------------------------------------------
			$map_picker = '';
			if($this->has_map_picker()) {
				$map_picker = "</div><div class='col-sm-2'>" . $this->render_map_picker_button(
					l10n('geom-field.map-picker-button-label'), 'map-marker', l10n('geom-field.map-picker-button-tooltip'), false, 'btn btn-default'
				);
			}

			$output_buf .= sprintf(
				"<input %s %s type='text' class='form-control' id='%s' name='%s' %s value='%s' %s placeholder='%s' title='%s' />%s",
				$this->get_disabled_attr(),
				$this->get_required_attr(),
				$this->get_control_id(),
				$this->get_control_name(),
				$this->get_maxlen_attr(),
				unquote($this->get_submitted_value('')),
				$this->get_focus_attr(),
				unquote($this->get_custom_placeholder(l10n('geom-field.placeholder', l10n('geom-field.map-picker-button-label')))),
				unquote($this->get_label()),
				$map_picker
			);
			return $output_buf;
		}

		//--------------------------------------------------------------------------------------
		public function /*bool*/ is_included_in_global_search() {
		//--------------------------------------------------------------------------------------
			return true;
		}

		//--------------------------------------------------------------------------------------
		public function /*string*/ sql_select_transformation() {
			return 'st_astext(%s) ' . db_esc($this->field_name);
		}

		//--------------------------------------------------------------------------------------
		public function /*string*/ get_global_search_condition(
			$param_name,
			$search_string_transformation,
			$table_qualifier = null) {
		//--------------------------------------------------------------------------------------
			return sprintf(
				"lower(st_astext(%s)) like concat('%%', ". db_cast_text(':%s') .", '%%')",
				db_esc($this->field_name, $table_qualifier),
				$param_name
			);
		}
	}
?>
