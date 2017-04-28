<?
	//==========================================================================================
	class LookupField extends Field
	//==========================================================================================
	{
		protected $linked_items_div = '';

		//--------------------------------------------------------------------------------------
		// override parent
		public function allow_setnull_box() {
		//--------------------------------------------------------------------------------------
			return false;
		}
		//--------------------------------------------------------------------------------------
		public function get_lookup_settings() {
		//--------------------------------------------------------------------------------------
			return $this->field['lookup'];
		}
		//--------------------------------------------------------------------------------------
		public function get_lookup_table_name() {
		//--------------------------------------------------------------------------------------
			return $this->field['lookup']['table'];
		}
		//--------------------------------------------------------------------------------------
		public function get_lookup_field_name() {
		//--------------------------------------------------------------------------------------
			return $this->field['lookup']['field'];
		}
		//--------------------------------------------------------------------------------------
		public function get_lookup_display() {
		//--------------------------------------------------------------------------------------
			return $this->field['lookup']['display'];
		}
		//--------------------------------------------------------------------------------------
		public function has_lookup_default() {
		//--------------------------------------------------------------------------------------
			return isset($this->field['lookup']['default']);
		}
		//--------------------------------------------------------------------------------------
		public function get_lookup_default() {
		//--------------------------------------------------------------------------------------
			return get_default($this->field['lookup']['default']);
		}
		//--------------------------------------------------------------------------------------
		public function get_linkage_info() {
		//--------------------------------------------------------------------------------------
			return $this->field['linkage'];
		}
		//--------------------------------------------------------------------------------------
		public function is_allowed_create_new() { // default: true
		//--------------------------------------------------------------------------------------
			if(!isset($this->field['allow_create']))
				return true;

			return $this->field['allow_create'] === true;
		}
		//--------------------------------------------------------------------------------------
		public function is_lookup_async() { // default: false
		//--------------------------------------------------------------------------------------
			return isset($this->field['lookup']['async']);
		}
		//--------------------------------------------------------------------------------------
		public function is_dropdown_hidden() { // default: false
		//--------------------------------------------------------------------------------------
			return isset($this->field['lookup']['hide_dropdown']) && $this->field['lookup']['hide_dropdown'] === true;
		}
		//--------------------------------------------------------------------------------------
		public function get_create_new_label() { // default: Create New
		//--------------------------------------------------------------------------------------
			return isset($this->field['lookup']['create_new_label']) ? $this->field['lookup']['create_new_label'] : 'Create New';
		}
		//--------------------------------------------------------------------------------------
		public function get_async_min_input_len() {
		//--------------------------------------------------------------------------------------
			return $this->field['lookup']['async']['min_input_len'];
		}
		//--------------------------------------------------------------------------------------
		public function get_cardinality() {
		//--------------------------------------------------------------------------------------
			if(isset($this->render_settings['force_cardinality_single']) && $this->render_settings['force_cardinality_single'] === true)
				return CARDINALITY_SINGLE;

			return $this->field['lookup']['cardinality'];
		}
		//--------------------------------------------------------------------------------------
		public function get_async_delay() {
		//--------------------------------------------------------------------------------------
			return $this->field['lookup']['async']['delay'];
		}
		//--------------------------------------------------------------------------------------
		public function has_async_delay() {
		//--------------------------------------------------------------------------------------
			return isset($this->field['lookup']['async']['delay']);
		}
		//--------------------------------------------------------------------------------------
		public function get_form_id() {
		//--------------------------------------------------------------------------------------
			return $this->render_settings['form_id'];
		}
		//--------------------------------------------------------------------------------------
		public function /*bool*/ is_included_in_global_search() {
		//--------------------------------------------------------------------------------------
			return true;
		}

		//--------------------------------------------------------------------------------------
		protected function /*string*/ render_internal(&$output_buf) {
		// render_settings: --
		//--------------------------------------------------------------------------------------
			if($this->get_cardinality() == CARDINALITY_SINGLE)
				$this->render_cardinality_single($output_buf);
			elseif($this->get_cardinality() == CARDINALITY_MULTIPLE)
				$this->render_cardinality_multiple($output_buf);
			return $output_buf;
		}

		//------------------------------------------------------------------------------------------
		protected function get_linked_record_ids() {
		//------------------------------------------------------------------------------------------
			if(!$this->has_submitted_value())
				return array();

			$v = trim($this->get_submitted_value(''));
			if($v == '')
				return array();

			return json_decode($v);
		}

		//--------------------------------------------------------------------------------------
		public function render_create_new_button_html(/*in+out*/ &$html) {
		//--------------------------------------------------------------------------------------
			if($this->is_disabled() || !$this->is_allowed_create_new())
				return;

			$popup_url = '?' . http_build_query(array(
				'popup' 		=> $this->table_name,
				'lookup_field' 	=> $this->field_name,
				'table' 		=> $this->get_lookup_table_name(),
				'mode'			=> MODE_NEW
			));

			$popup_title = html('New ' . $this->get_label());

			$html .= sprintf(
				"<div class='col-sm-2'><button type='button' title='%s' class='btn btn-default multiple-select-add' data-create-title='%s' data-create-url='%s' id='%s_add' formnovalidate><span class='glyphicon glyphicon-plus'></span> %s</button></div>\n",
				l10n('lookup-field.create-new-tooltip'),
				$popup_title,
				$popup_url,
				$this->field_name,
				$this->get_create_new_label());
		}

		//--------------------------------------------------------------------------------------
		protected function render_cardinality_single(&$output_buf) {
		//--------------------------------------------------------------------------------------
			$output_buf .= sprintf(
				"<select %s %s class='form-control %s' id='%s_dropdown' name='%s' data-table='%s' data-fieldname='%s' data-placeholder='%s' data-thistable='%s' %s %s %s data-lookuptype='single' %s %s>\n",

				$this->get_disabled_attr(),
				$this->get_required_attr(),
				$this->is_lookup_async() ? 'lookup-async' : '',
				$this->get_control_id(),
				$this->get_control_name(),
				$this->get_lookup_table_name(),
				$this->field_name,
				l10n('lookup-field.placeholder'),
				$this->table_name,
				$this->is_lookup_async() ? sprintf("data-language='%s'", get_app_lang()) : '',
				$this->is_lookup_async() ? sprintf("data-minimum-input-length='%s'", $this->get_async_min_input_len()) : '',
				$this->is_lookup_async() && $this->has_async_delay() ? sprintf("data-asyncdelay='%s'", $this->get_async_delay()) : '',
				$this->is_required() ? '' : 'data-allow-clear=true',
				$this->get_focus_attr()
			);

			$db = db_connect();
			if($db === false)
				return proc_error(l10n('error.db-connect'));

			$where_clause = '';
			if($this->is_lookup_async() && $this->has_submitted_value() && $this->get_submitted_value() != NULL_OPTION)
				$where_clause = sprintf('where %s = ?', db_esc($this->get_lookup_field_name()));

			$sql = sprintf('select %s val, %s txt from %s t %s order by txt',
				db_esc($this->get_lookup_field_name()), resolve_display_expression($this->get_lookup_display(), 't'), $this->get_lookup_table_name(), $where_clause);

			$stmt = $db->prepare($sql);
			if(false === $stmt)
				return proc_error(l10n('error.db-prepare'), $db);

			if(false === $stmt->execute($where_clause != '' ? array($this->get_submitted_value()) : array()))
				return proc_error(l10n('error.db-execute'), $db);

			if(!$this->is_required())
				$output_buf .= sprintf("<option value='%s'>&nbsp;</option>\n", NULL_OPTION);
			else if($_GET['mode'] == MODE_NEW)
				$output_buf .= "<option value=''></option>\n";

			$selection_done = '';
			while($obj = $stmt->fetch(PDO::FETCH_OBJ)) {
				if($selection_done != 'done') {
					$sel = ($this->has_submitted_value() && $this->get_submitted_value() == $obj->val ? ' selected="selected" ' : '');
					if($sel != '')
						$selection_done = 'done';
					else if($sel == '' && $this->is_required() && $this->has_lookup_default() && $this->get_lookup_default() == $obj->val) {
						$sel = ' selected="selected" ';
						$selection_done = 'default';
					}
				}
				else
					$sel = '';

				$output_buf .= sprintf(
					"<option value='%s' %s>%s</option>\n",
					unquote($obj->val),
					$sel,
					format_lookup_item_label($obj->txt, $this->field['lookup'], $obj->val, 'html', false)
				);
			}
			$output_buf .= "</select>\n";
		}

		//--------------------------------------------------------------------------------------
		protected function render_cardinality_multiple(&$output_buf) {
		//--------------------------------------------------------------------------------------
			global $TABLES;
			$output_buf .= sprintf(
				"<input class='multiple-select-hidden' id='%s' name='%s' type='hidden' value='%s' />\n",
				$this->get_control_id(), $this->get_control_name(), trim($this->get_submitted_value(''))
			);

			$output_buf .= sprintf(
				"<select %s %s class='form-control multiple-select-dropdown %s' id='%s_dropdown' data-table='%s' data-thistable='%s' data-fieldname='%s' data-placeholder='%s' %s %s %s data-lookuptype='multiple' data-allow-clear='true' %s>\n",

				$this->get_disabled_attr(),
				$this->get_required_attr(),
				$this->is_lookup_async() ? 'lookup-async' : '',
				$this->get_control_id(),
				$this->get_lookup_table_name(),
				$this->table_name,
				$this->field_name,
				l10n('lookup-field.placeholder'),
				$this->is_lookup_async() ? sprintf("data-language='%s'", get_app_lang()) : '',
				$this->is_lookup_async() ? sprintf("data-minimum-input-length='%s'", $this->get_async_min_input_len()) : '',
				$this->is_lookup_async() && $this->has_async_delay() ? sprintf("data-asyncdelay='%s'", $this->get_async_delay()) : '',
				$this->get_focus_attr()
			);

			// we look which ones are already connected
			$linked_items = $this->get_linked_record_ids();
			$this->linked_items_div = '';

			// check whether additional fields can be set in the linkage table
			$has_additional_editable_fields = has_additional_editable_fields($this->get_linkage_info());

			$table = &$TABLES[$this->table_name];

			$db = db_connect();
			if($db === false)
				return proc_error(l10n('error.db-connect'));

			if($this->is_lookup_async()) {
				// just prepare the list of already existing linked items
				$existing_linkage = array();
				$sql = sprintf('select %s val, %s txt from %s t where %s = ?',
					db_esc($this->get_lookup_field_name()),
					resolve_display_expression($this->get_lookup_display(), 't'),
					db_esc($this->get_lookup_table_name()),
					db_esc($this->get_lookup_field_name()));

				$stmt = $db->prepare($sql);
				if($stmt === false)
					return proc_error(l10n('error.db-prepare'), $db);

				foreach($linked_items as $linked_item_val) {
					if(false === $stmt->execute(array($linked_item_val)))
						continue; // maybe deleted already somewhere else?

					if($res = $stmt->fetch(PDO::FETCH_OBJ))
						$existing_linkage[$res->val] = $res->txt;
				}
				asort($existing_linkage);
				foreach($existing_linkage as $val => $txt) {
					$this->linked_items_div .= get_linked_item_html($this->get_form_id(), $table, $this->table_name, $this->field_name, $has_additional_editable_fields,
							$val, $txt, get_the_primary_key_value_from_url($table, ''));
				}
			}
			else {
				$q = sprintf('select %s val, %s txt from %s t order by txt',
					db_esc($this->get_lookup_field_name()),
					resolve_display_expression($this->get_lookup_display(), 't'),
					db_esc($this->get_lookup_table_name()));

				$res = $db->query($q);
				// fill dropdown and linked fields list
				while($obj = $res->fetchObject()) {
					if(in_array("{$obj->val}", $linked_items)) {
						$this->linked_items_div .= get_linked_item_html($this->get_form_id(), $table, $this->table_name, $this->field_name, $has_additional_editable_fields,
							$obj->val, $obj->txt, get_the_primary_key_value_from_url($table, ''));
					}
					else {
						$output_buf .= sprintf(
							"<option value='%s' data-label='%s'>%s</option>\n",
							$obj->val,
							unquote($obj->txt),
							format_lookup_item_label($obj->txt, $this->get_lookup_settings(), $obj->val, 'html', false)
						);
					}
				}
			}
			$output_buf .= "</select>\n";
		}

		//--------------------------------------------------------------------------------------
		public function render_linked_items(&$output_buf) {
		//--------------------------------------------------------------------------------------
			$output_buf .= sprintf(
				"<div class='col-sm-offset-3 col-sm-9 multiple-select-ul' id='%s_list'>%s</div>",
				$this->field_name, $this->linked_items_div
			);
		}

		//--------------------------------------------------------------------------------------
		// expression used in sprintf(...) to fetch fields of this type. default: no transform.
		// override if needed (see e.g. T_POSTGIS_GEOM)
		public function /*string*/ sql_select_transformation() {
			if($this->get_cardinality() == CARDINALITY_SINGLE) {
				return sprintf('(SELECT %s FROM %s k WHERE %s = t.%s) %s, t.%s %s',
					resolve_display_expression($this->get_lookup_display(), 'k'),
					db_esc($this->get_lookup_table_name()), db_esc($this->get_lookup_field_name()),
					db_esc($this->field_name), db_esc($this->field_name), db_esc($this->field_name),
					db_postfix_fieldname($this->field_name, FK_FIELD_POSTFIX, true));
			}
			else {
				$linkage = $this->get_linkage_info();
				return sprintf(
					"(SELECT '[' || array_to_json(array_agg(%s)) || ',' || array_to_json(array_agg(%s)) || ']'
					 FROM %s other, %s link
					 WHERE link.%s = t.%s
					 AND other.%s = link.%s) %s",
					db_esc($this->get_lookup_field_name(), 'other'), resolve_display_expression($this->get_lookup_display(), 'other'),
					db_esc($this->get_lookup_table_name()), db_esc($linkage['table']),
					db_esc($linkage['fk_self']), db_esc($this->table['primary_key']['columns'][0]),
					db_esc($this->get_lookup_field_name()), db_esc($linkage['fk_other']), db_esc($this->field_name));
			}
		}

		//--------------------------------------------------------------------------------------
		public function /*string*/ get_global_search_condition(
			$param_name,
			$search_string_transformation,
			$table_qualifier = null)
		{
			if($this->get_cardinality() == CARDINALITY_SINGLE) {
				$s = sprintf(
					"(SELECT (%s)::text FROM %s k WHERE %s = t.%s) like '%%'||:%s||'%%'",
					sprintf($search_string_transformation, resolve_display_expression($this->get_lookup_display(), 'k')),
					db_esc($this->get_lookup_table_name()),
					db_esc($this->get_lookup_field_name()),
					db_esc($this->field_name),
					$param_name
				);
				return $s;
			}
			else {
				$linkage = $this->get_linkage_info();
				$s = sprintf(
					"(SELECT array_to_string(array_agg(%s), ' ')
					 FROM %s other, %s link
					 WHERE link.%s = t.%s
					 AND other.%s = link.%s)
					 like '%%'||:%s||'%%'",
					sprintf($search_string_transformation, resolve_display_expression($this->get_lookup_display(), 'other')),
					db_esc($this->get_lookup_table_name()), db_esc($linkage['table']),
					db_esc($linkage['fk_self']), db_esc($this->table['primary_key']['columns'][0]),
					db_esc($this->get_lookup_field_name()), db_esc($linkage['fk_other']),
					$param_name);
				return $s;
			}
		}
	}
?>
