<?php
	//------------------------------------------------------------------------------------------
	function render_view() {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		global $APP;

		if(isset($_SESSION['redirect']))
			return false;

		$table_name = $_GET['table'];
		if(!isset($TABLES[$table_name]))
			return proc_error(l10n('error.invalid-table', $table_name));

		$table = $TABLES[$table_name];
		if(!is_allowed($table, $_GET['mode']) && !is_own_user_record(true))
		return proc_error(l10n('error.not-allowed'));

		$pk_vals = get_primary_key_values_from_url($table);
		if($pk_vals === false)
			return false;

		echo "<h1>{$table['item_name']}</h1>";
		/*$key_ids = array();
		foreach($pk_vals as $pk => $val)
			$key_ids[] = html($table['fields'][$pk]['label']) . '<span class="smsp">=</span>' . html($val);

		printf ('<h1>%s <small>%s</small></h1>', $table['item_name'], implode(', ', $key_ids));
		*/

		$query = build_query($table_name, $table, $pk_vals, MODE_VIEW, null, $params);
		if(!db_prep_exec($query, $params, $stmt))
			return false;
		if($stmt->rowCount() != 1)
			return proc_error(l10n('view.invalid-record'));

		$record = $stmt->fetch(PDO::FETCH_ASSOC);
		$addl_data = '';
		if(isset($table['additional_steps'])) {
			$addl_data .= "<div class='btn-group'><button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown'><span class='glyphicon glyphicon-forward'></span> ".l10n('view.add-related-data-button')." <span class='caret'></span></button><ul class='dropdown-menu' role='menu'>\n";
			foreach($table['additional_steps'] as $add_table => $add_info) {
				//TODO: adapt for composite foreign key
				$q = "?table={$add_table}&mode=".MODE_NEW.'&'.PREFILL_PREFIX . $add_info['foreign_key']."={$record[$table['primary_key']['columns'][0]]}";
				$addl_data .= "<li><a href='$q'>". html($add_info['label']) ."</a></li>\n";
			}
			$addl_data .= "</ul></div>";
		}

		if(is_allowed($table, MODE_EDIT) || is_own_user_record(true))
			$addl_data .= sprintf(
				"<a title='%s' href='%s' class='btn btn-default tabs-aware'><span class='glyphicon glyphicon-edit'></span> %s</a>",
				unquote(l10n('view.edit-icon', $table['item_name'])),
				build_get_params(array('mode' => MODE_EDIT)),
				l10n('view.edit-button')
			);

		if(is_allowed($table, MODE_DELETE))
			$addl_data .= sprintf(
				"<a title='%s' class='btn btn-default' role='button' data-href='%s' data-toggle='modal' data-target='#confirm-delete'><span class='glyphicon glyphicon-trash'></span> %s</a>",
				unquote(l10n('view.delete-icon', $table['item_name'])),
				build_get_params(array('mode' => MODE_DELETE)),
				l10n('view.delete-button')
			);

		if(is_allowed($table, MODE_NEW))
			$addl_data .= sprintf(
				"<a title='%s' href='?%s' class='btn btn-default '><span class='glyphicon glyphicon-plus'></span> %s</a>",
				unquote(l10n('view.new-icon', $table['item_name'])),
				http_build_query(array('table' => $table_name, 'mode' => MODE_NEW)),
				l10n('view.new-button')
			);

		if(is_allowed($table, MODE_LIST))
			$addl_data .= sprintf(
				"<a title='%s' href='?%s' class='btn btn-default '><span class='glyphicon glyphicon-list'></span> %s</a>",
				unquote(l10n('view.list-icon', $table['display_name'])),
				http_build_query(array('table' => $table_name, 'mode' => MODE_LIST)),
				l10n('view.list-button')
			);

		if(is_allowed($table, MODE_MERGE)) {
			get_session_var('merge', $merge_info);
			$display_merge_btn = true;
			if(is_array($merge_info)) {
				$display_merge_btn = false;
				foreach($merge_info[0]['keys'] as $key => $val) {
					if(!isset($pk_vals[$key]) || $pk_vals[$key] != $val) {
						$display_merge_btn = true;
						break;
					}
				}
			}
			if($display_merge_btn) {
				require_once ENGINE_PATH_HTTP . 'inc/merge.php';
				$get_params = array('table' => $table_name, 'mode' => MODE_MERGE, 'action' => 'push');
				foreach($pk_vals as $pk => $val)
					$get_params['key_' . $pk] = $val;
				$addl_data .= sprintf(
					"<a title='%s' class='btn btn-default' role='button' data-merge-push='%s'><span class='glyphicon glyphicon-transfer'></span> %s</a>%s",
					unquote(l10n('view.merge-icon', $table['item_name'])),
					build_get_params($get_params),
					l10n('view.merge-button'),
					MergeRecordsPage::get_merge_button_js()
				);
			}
		}

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
			 || (isset($ti['show_in_related']) && $ti['show_in_related'] === false)
			 || (isset($ti['list_in_related']) && $ti['list_in_related'] === false) // in settings.template.php this was wrongly listed, so we keep it working
			 )
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
						'display_label' => isset($fi['lookup']['related_label']) ? $fi['lookup']['related_label'] : null,
						'search_type' => $fi['lookup']['cardinality'] == CARDINALITY_MULTIPLE ? SEARCH_WORD : SEARCH_EXACT,
						'raw_fk' => $fi['lookup']['cardinality'] == CARDINALITY_SINGLE ? 1 : 0
					);
				}
			}
		}

		if(isset($APP['custom_related_list_proc']) && function_exists($APP['custom_related_list_proc']))
			$APP['custom_related_list_proc']($table_name, $table, $pk_vals, $rel_list);

		if(count($rel_list) > 0) {
			$addl_data .= sprintf(
				"<div class='btn-group'><button type='button' title='%s' class='btn btn-default dropdown-toggle' data-toggle='dropdown'><span class='glyphicon glyphicon-link'></span> %s <span class='caret'></span></button><ul class='dropdown-menu' role='menu'>\n",
				l10n('view.related-icon'), l10n('view.related-button')
			);
			foreach($rel_list as $rel) {
				$q = http_build_query(array(
					'table' => $rel['table_name'],
					'mode' => MODE_LIST,
					SEARCH_PARAM_OPTION => $rel['search_type'],
					SEARCH_PARAM_FIELD => $rel['field_name'],
					SEARCH_PARAM_QUERY => $record[$table['primary_key']['columns'][0]],
					'raw_fk' => $rel['raw_fk'] // speeds up search since we know
				));
				$label = ($rel['display_label'] !== null ?
					$rel['display_label']
					: html(l10n('view.related-menu-item', $rel['table_label'], $rel['field_label'])));

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
			/*if(!isset($record[$col]))
				continue;*/
			$val = $record[$col];
			$table_html .= $form_tabs->new_tab_if_needed($col);

			$field_label = get_field_label($table['fields'][$col], $record);

			# display null values?
			$css_null = '';
			if(!$APP['view_display_null_fields'] && $val === null) {
				$empty_count++;
				$css_null = 'null_field';
			}

			$table_html .= "<div class='form-group $css_null'><label class='col-sm-3 control-label'>{$field_label}</label>\n";

			$val = prepare_field_display_val($table, $record, $table['fields'][$col], $col, $val);

			$style = '';
			if(isset($table['fields'][$col]['view_css']))
				$style = sprintf(' style="%s"', $table['fields'][$col]['view_css']);

			$table_html .= "<div class='col-sm-9 column-value'$style>{$val}</div></div>\n";
		}

		if($empty_count > 0) {
			$btn_label = l10n($empty_count == 1 ? 'view.show-hidden-field-1' : 'view.show-hidden-field-N');
			$empty_fields = l10n($empty_count == 1 ? 'view.hidden-fields-hint-1' : 'view.hidden-fields-hint-N', $table['item_name'], $empty_count);

			$empty_fields = <<<HTML
				<p id='show_null_fields'>
					$empty_fields
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
