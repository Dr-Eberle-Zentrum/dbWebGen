<?
	//------------------------------------------------------------------------------------------
	function render_view() {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		global $APP;

		if(isset($_SESSION['redirect']))
			return false;

		$table_name = $_GET['table'];
		if(!isset($TABLES[$table_name]))
			return proc_error('Invalid table name or table not configured.');

		$table = $TABLES[$table_name];
		if(!is_allowed($table, $_GET['mode']) && !is_own_user_record(true))
			return proc_error('You are not allowed to perform this action.');

		$pk_vals = get_primary_key_values_from_url($table);
		if($pk_vals === FALSE)
			return false;

		echo "<h1>{$table['item_name']}</h1>";
		/*$key_ids = array();
		foreach($pk_vals as $pk => $val)
			$key_ids[] = html($table['fields'][$pk]['label']) . '<span class="smsp">=</span>' . html($val);

		printf ('<h1>%s <small>%s</small></h1>', $table['item_name'], implode(', ', $key_ids));
		*/

		$query = build_query($table_name, $table, $pk_vals, MODE_VIEW, NULL, $params);

		$db = db_connect();
		if($db === FALSE)
			return proc_error('Connect to database failed.');

		$stmt = $db->prepare($query);
		if($stmt === FALSE)
			return proc_error('Query preparation failed.', $db);

		if($stmt->execute($params) === FALSE)
			return proc_error('Execution of statement failed.', $db);

		if($stmt->rowCount() != 1)
			return proc_error('This record cannot be viewed. It might have been deleted.');

		$record = $stmt->fetch(PDO::FETCH_ASSOC);

		$addl_data = '';

		if(isset($table['additional_steps'])) {
			$addl_data .= "<div class='btn-group'><button type='button' class='btn btn-default dropdown-toggle ' data-toggle='dropdown'><span class='glyphicon glyphicon-forward'></span> Add Related Data <span class='caret'></span></button><ul class='dropdown-menu' role='menu'>\n";
			foreach($table['additional_steps'] as $add_table => $add_info) {
				//TODO: adapt for composite foreign key
				$q = "?table={$add_table}&mode=".MODE_NEW.'&'.PREFILL_PREFIX . $add_info['foreign_key']."={$record[$table['primary_key']['columns'][0]]}";

				$addl_data .= "<li><a href='$q'>". html($add_info['label']) ."</a></li>\n";
			}
			$addl_data .= "</ul></div>";
		}

		if(is_allowed($table, MODE_EDIT) || is_own_user_record(true))
			$addl_data .= "<a title='".html("Edit This {$table['item_name']}")."' href='". build_get_params(array('mode' => MODE_EDIT)) ."' class='btn btn-default tabs-aware'><span class='glyphicon glyphicon-edit'></span> Edit</a>";

		if(is_allowed($table, MODE_DELETE))
			$addl_data .= "<a title='".html("Delete This {$table['item_name']}")."' class='btn btn-default ' role='button' data-href='". build_get_params(array('mode' => MODE_DELETE)) ."' data-toggle='modal' data-target='#confirm-delete'><span class='glyphicon glyphicon-trash'></span> Delete</a>";

		if(is_allowed($table, MODE_NEW))
			$addl_data .= "<a title='".html("Create New {$table['item_name']}")."' href='?" . http_build_query(array('table'=>$table_name, 'mode'=>MODE_NEW)) ."' class='btn btn-default '><span class='glyphicon glyphicon-plus'></span> Create New</a>";

		if(is_allowed($table, MODE_LIST))
			$addl_data .= "<a title='".html("List All {$table['display_name']}")."' href='?" . http_build_query(array('table'=>$table_name, 'mode'=>MODE_LIST)) ."' class='btn btn-default '><span class='glyphicon glyphicon-list'></span> List All</a>";

		if(isset($table['render_links']) && is_allowed($table, MODE_LINK)) {
			foreach($table['render_links'] as $render_link) {
				$addl_data .= "<a href='" .
					sprintf($render_link['href_format'], $record[$render_link['field']]) .
					"' title='{$render_link['title']}' class='btn btn-default'><span class='glyphicon glyphicon-{$render_link['icon']}'></span></a>";
			}
		}

		// now check for related data in other tables
		$rel_list = array();
		foreach($TABLES as $tn => $ti) {
			if($table_name == $tn
			 || !in_array(MODE_LIST, $ti['actions'])
			 || (isset($ti['show_in_related']) && $ti['show_in_related'] === false))
			{
				continue;
			}

			foreach($ti['fields'] as $fn => $fi) {
				if($fi['type'] == T_LOOKUP && $fi['lookup']['table'] == $table_name) {
					$rel_list[] = array(
						'table_name' => $tn,
						'table_label' => $ti['display_name'],
						'field_name' => $fn,
						'field_label' => $fi['label'],
						'display_label' => isset($fi['lookup']['related_label']) ? $fi['lookup']['related_label'] : null);
				}
			}
		}

		if(isset($APP['custom_related_list_proc']) && function_exists($APP['custom_related_list_proc']))
			$APP['custom_related_list_proc']($table_name, $table, $pk_vals, $rel_list);

		if(count($rel_list) > 0) {
			$addl_data .= "<div class='btn-group'><button type='button' class='btn btn-default dropdown-toggle ' data-toggle='dropdown'><span class='glyphicon glyphicon-link'></span> List Related <span class='caret'></span></button><ul class='dropdown-menu' role='menu'>\n";
			foreach($rel_list as $rel) {
				$q = http_build_query(array(
					'table' => $rel['table_name'],
					'mode' => MODE_LIST,
					SEARCH_PARAM_OPTION => SEARCH_WORD,
					SEARCH_PARAM_FIELD => $rel['field_name'],
					SEARCH_PARAM_QUERY => $record[$table['primary_key']['columns'][0]]
				));
				$label = ($rel['display_label'] !== null ?
					$rel['display_label']
					: html($rel['table_label']) ." (via ". html($rel['field_label']) .")");

				$addl_data .= "<li><a href='?$q'>$label</a></li>\n";
			}
			$addl_data .= "</ul></div>";
		}

		if($addl_data != '')
			echo "<div class='btn-group hidden-print'>{$addl_data}</div>\n";

		$table_html = '';
		$table_html .= "<p><form class='form-horizontal bg-gray' role='form' data-type='view'>\n";

		$form_tabs = new FormTabs($table);
		$table_html .= $form_tabs->begin();

		$empty_count = 0;
		/*foreach($record as $col => $val) {
			if(!isset($table['fields'][$col]))
				continue;*/
		foreach(array_keys($table['fields']) as $col) {
			if(!isset($record[$col]))
				continue;
			$val = $record[$col];
			$table_html .= $form_tabs->new_tab_if_needed($col);

			$field_label = get_field_label($table['fields'][$col], $record);

			# display null values?
			$css_null = '';
			if(!$APP['view_display_null_fields'] && $val === NULL) {
				$empty_count++;
				$css_null = 'null_field';
			}

			$table_html .= "<div class='form-group $css_null'><label class='col-sm-3 control-label'>{$field_label}</label>\n";

			$val = prepare_field_display_val($table, $record, $table['fields'][$col], $col, $val);

			$table_html .= "<div class='col-sm-9 column-value'>{$val}</div></div>\n";
		}

		if($empty_count > 0) {
			$btn_label = $empty_count == 1 ? 'Show this field' : 'Show these fields';
			$empty_fields = $empty_count == 1 ? 'one empty field' : $empty_count . ' empty fields';

			$empty_fields = <<<HTML
				<p id='show_null_fields'>
					This {$table['item_name']} has {$empty_fields}.
					<a role='button' class='btn btn-default' href='javascript:void(0)'>
						<span class='glyphicon glyphicon-eye-open'></span> {$btn_label}
					</a>
				</p>
				<script>
					$('#show_null_fields').click(function() {
						$('.null_field').toggle();
						$(this).toggle();
					});
				</script>
HTML;
			$table_html = $empty_fields . $table_html;
		}

		$table_html .= $form_tabs->close();
		$table_html .= "</form></p>";

		echo $table_html;

		if(is_allowed($table, MODE_DELETE))
			enable_delete();

		return true;
	}
?>
