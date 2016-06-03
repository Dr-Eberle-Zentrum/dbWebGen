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
		if(!is_allowed($table, $_GET['mode']))
			return proc_error('You are not allowed to perform this action.');
		
		$fields = $table['fields'];
		
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
		
		if(is_allowed($table, MODE_EDIT))
			$addl_data .= "<a title='".html("Edit This {$table['item_name']}")."' href='". build_get_params(array('mode' => MODE_EDIT)) ."' class='btn btn-default '><span class='glyphicon glyphicon-edit'></span> Edit</a>";
		
		if(is_allowed($table, MODE_DELETE))
			$addl_data .= "<a title='".html("Delete This {$table['item_name']}")."' class='btn btn-default ' role='button' data-href='". build_get_params(array('mode' => MODE_DELETE)) ."' data-toggle='modal' data-target='#confirm-delete'><span class='glyphicon glyphicon-trash'></span> Delete</a>";
		
		if(is_allowed($table, MODE_NEW))
			$addl_data .= "<a title='".html("Create New {$table['item_name']}")."' href='?" . http_build_query(array('table'=>$table_name, 'mode'=>MODE_NEW)) ."' class='btn btn-default '><span class='glyphicon glyphicon-plus'></span> Create New</a>";
		
		if(is_allowed($table, MODE_LIST))
			$addl_data .= "<a title='".html("List All {$table['display_name']}")."' href='?" . http_build_query(array('table'=>$table_name, 'mode'=>MODE_LIST)) ."' class='btn btn-default '><span class='glyphicon glyphicon-list'></span> List All</a>";
	
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
			echo "<div class='btn-group'>{$addl_data}</div>\n";
		
		echo "<p><form class='form-horizontal bg-gray' role='form' data-type='view'>\n";
		
		foreach($record as $col => $val) {
			if(!isset($fields[$col]))
				continue;
			
			# display null values?
			if(!$APP['view_display_null_fields'] && $val === NULL)
				continue;
			
			echo "<div class='form-group'><label class='col-sm-3 control-label'>". get_field_label($fields[$col], $record) . "</label>\n";
			
			$val = prepare_field_display_val($table, $record, $fields[$col], $col, $val);
			
			echo "<div class='col-sm-9 column-value'>{$val}</div></div>\n";	
		}
		
		echo "</form></p>";	
		if(is_allowed($table, MODE_DELETE))
			enable_delete();

		return true;		
	}
?>