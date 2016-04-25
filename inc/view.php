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
		
		echo "<h1>{$table['item_name']}</h1>\n";
		
		$pk_vals = get_primary_key_values_from_url($table);
		if($pk_vals === FALSE)
			return false;
		
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
			$addl_data .= "<button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown'><span class='glyphicon glyphicon-forward'></span> Add Related Data <span class='caret'></span></button>\n<ul class='dropdown-menu' role='menu'>\n";
			foreach($table['additional_steps'] as $add_table => $add_info) {
				//TODO: adapt for composite foreign key
				$q = "?table={$add_table}&mode=".MODE_NEW.'&'.PREFILL_PREFIX . $add_info['foreign_key']."={$record[$table['primary_key']['columns'][0]]}";
				
				$addl_data .= "<li><a href='$q'>". html($add_info['label']) ."</a></li>\n";
			}
			$addl_data .= "</ul>\n";
		}
		
		if(is_allowed($table, MODE_EDIT))
			$addl_data .= "<a href='". build_get_params(array('mode' => MODE_EDIT)) ."' class='btn btn-default space-left'><span class='glyphicon glyphicon-edit'></span> Edit this {$table['item_name']}</a>";
		
		if(is_allowed($table, MODE_DELETE))
			$addl_data .= "<a class='btn btn-default space-left' role='button' data-href='". build_get_params(array('mode' => MODE_DELETE)) ."' data-toggle='modal' data-target='#confirm-delete'><span title='Delete this record' class='glyphicon glyphicon-trash'></span> Delete this {$table['item_name']}</a>\n";
		
		if(is_allowed($table, MODE_NEW))
			$addl_data .= "<a href='?" . http_build_query(array('table'=>$table_name, 'mode'=>MODE_NEW)) ."' class='btn btn-default space-left'><span class='glyphicon glyphicon-plus'></span> New {$table['item_name']}</a></p>\n";
		
		if($addl_data != '')
			echo "<div class='dropdown'>{$addl_data}</div>\n";
		
		echo "<form class='form-horizontal' role='form' data-type='view'>\n";
		
		foreach($record as $col => $val) {
			if(!isset($fields[$col]))
				continue;
			
			# display null values?
			if(!$APP['view_display_null_fields'] && $val === NULL)
				continue;
			
			echo "<div class='form-group'><label class='col-sm-3 control-label'>". html($fields[$col]['label']) . ":</label>\n";
			
			
			if($fields[$col]['type'] == T_ENUM && $val !== NULL)
				$val = html($fields[$col]['values'][$val]);
			
			else if($fields[$col]['type'] == T_PASSWORD)
				$val = '&bull;&bull;&bull;&bull;&bull;';
			
			else if($fields[$col]['type'] == T_UPLOAD)
				$val = "<a href='". get_file_url($val, $fields[$col]) ."'>$val</a>";

			else if($fields[$col]['type'] == T_LOOKUP && $fields[$col]['lookup']['cardinality'] == CARDINALITY_SINGLE) {
				$href = http_build_query(array(
					'table' => $fields[$col]['lookup']['table'],
					'mode' => MODE_VIEW,
					$fields[$col]['lookup']['field'] => isset($record[db_postfix_fieldname($col, '_raw', false)]) ? $record[db_postfix_fieldname($col, '_raw', false)] : $val
				));
				
				$val = "<a href=\"?{$href}\">". html($val) ."</a>";
			}
			
			else
				$val = html($val);
			
			echo "<div class='col-sm-9 column-value'>{$val}</div></div>\n";	
		}
		
		echo "</form>";	
		if(is_allowed($table, MODE_DELETE))
			enable_delete();

		return true;		
	}
?>