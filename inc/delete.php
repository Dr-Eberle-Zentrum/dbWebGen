<?
	//------------------------------------------------------------------------------------------
	function process_delete() {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		
		header('Content-Type: text/plain; charset=utf-8');		
		
		$table_name = $_GET['table'];
		if(!isset($TABLES[$table_name]))
			return proc_error('Invalid table name or table not configured.');
		
		$table = $TABLES[$table_name];
		
		if(!is_allowed($table, MODE_DELETE))
			return proc_error('You are not allowed to perform this action.');
		
		
		$pk_hash = array();
		$table_pks = $TABLES[$table_name]['primary_key']['columns'];		
		$values = array();
		$sql = 'DELETE FROM ' . db_esc($table_name) . ' WHERE ';
		
		$where = '';
		for($pk = 0; $pk < count($table_pks); $pk++) {
			if(!isset($_GET[ $table_pks[$pk] ]))
				return proc_error('Missing identification parameter ' . $table_pks[$pk]);
				
			$where .= ($pk == 0 ? ' ' : ' AND ') . db_esc($table_pks[$pk]) . ' = ?';
			$values[] = $_GET[ $table_pks[$pk] ];
			
			$pk_hash[$table_pks[$pk]] = $_GET[$table_pks[$pk]];
		}
		
		$sql .= $where;
		
		$db = db_connect();
		if($db === false)
			return proc_error('Cannot connect to DB.');
		
		$stmt = $db->prepare($sql);
		if($stmt === FALSE)
			return proc_error('Preparation of delete statement failed.', $db);
		
		$upload_locations = array();
		
		foreach($TABLES[$table_name]['fields'] as $field_name => $field_info) {
			// need to see where all uploaded files are stored
			if($field_info['type'] == T_UPLOAD && ($field_info['store'] & STORE_FOLDER))
				$upload_locations[$field_name] = $field_info['location'];
		}
		
		$row = null;
		if(count($upload_locations) > 0) {
			// need to fetch record to get file names
			if(!db_get_single_row('SELECT * FROM '. db_esc($table_name) .' WHERE ' . $where, $values, $row))		
				return false; 
		}
		
		// call before_delete hook, if any. be careful with this, because actual delete might fail
		if(isset($table['hooks']) && isset($table['hooks']['before_delete']) && trim($table['hooks']['before_delete']) != '')
			$table['hooks']['before_delete']($table_name, $table, $pk_hash);
			
		if($stmt->execute($values) === FALSE) 
			return proc_error('Delete operation failed. Most likely this is because the item you intend to delete is being referenced by some other object.', $db);
			
		if($stmt->rowCount() != 1)
			return proc_error('Record was not deleted, probably because it was already deleted. Try reloading this page.', $db);
		
		// call after_delete hook, if any
		if(isset($table['hooks']) && isset($table['hooks']['after_delete']) && trim($table['hooks']['after_delete']) != '')
			$table['hooks']['after_delete']($table_name, $table, $pk_hash);
		
		$warn = '';		
		if($row != null) {
			foreach($upload_locations as $field_name => $location) {			
				if(!@unlink($location . '/' . $row[$field_name]))
					$warn = ' However, one or more files could not be deleted from the storage folder.';
			}
		}
		
		$_SESSION['redirect'] = "?table={$table_name}&mode=" . MODE_LIST;
		
		echo 'SUCCESS'; // NOTE!!! This is required for the calling javascript to properly handle the ajax return
		
		return proc_success('Record successfully deleted.' . $warn, null, false);
	}
?>