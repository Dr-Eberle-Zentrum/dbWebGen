<?
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
		protected function /*string*/ render_internal(&$output_buf) {
		// render_settings: form_method, name_attr, id_attr
		//--------------------------------------------------------------------------------------
			$map_picker = '';
			if($this->has_map_picker()) {
				$map_picker = sprintf(
					"</div><div class='col-sm-2'><button type='button' class='btn btn-default' data-target-ctrl='%s' data-map-url='?%s' formnovalidate><span title='Assign location from map' class='glyphicon glyphicon-map-marker'></span> Map</button>",
					$this->get_control_id(),
					http_build_query(array(
						'mode' => MODE_MAP_PICKER,
						'table' => $this->table_name,
						'field' => $this->field_name,
						'ctrl_id' => $this->get_control_id()
					))
				);
			}

			$output_buf .= sprintf(
				"<input %s %s type='text' class='form-control' id='%s' name='%s' %s value='%s' %s />%s",
				$this->get_disabled_attr(),
				$this->get_required_attr(),
				$this->get_control_id(),
				$this->get_control_name(),
				$this->get_maxlen_attr(),
				unquote($this->get_submitted_value('')),
				$this->get_focus_attr(),
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
		//--------------------------------------------------------------------------------------
		public function /*string*/ get_global_search_condition(
			$param_name,
			$search_string_transformation,
			$table_qualifier = null)
		{
			return sprintf(
				"lower(st_astext(%s)) like '%%' || :%s || '%%'",
				db_esc($this->field_name, $table_qualifier),
				$param_name
			);
		}
	}
?>
