<?php
	//------------------------------------------------------------------------------------------
	function process_delete() {
	//------------------------------------------------------------------------------------------
		global $TABLES;

		header('Content-Type: text/plain; charset=utf-8');

		$table_name = $_GET['table'];
		if(!isset($TABLES[$table_name]))
			return proc_error(l10n('error.invalid-table', $table_name));

		$table = $TABLES[$table_name];

		if(!is_allowed($table, MODE_DELETE))
			return proc_error(l10n('error.not-allowed'));


		$pk_hash = array();
		$table_pks = $TABLES[$table_name]['primary_key']['columns'];
		$values = array();
		$sql = 'DELETE FROM ' . db_esc($table_name) . ' WHERE ';

		$where = '';
		for($pk = 0; $pk < count($table_pks); $pk++) {
			if(!isset($_GET[ $table_pks[$pk] ]))
				return proc_error(l10n('error.missing-pk-value', $table_pks[$pk]));

			$where .= ($pk == 0 ? ' ' : ' AND ') . db_esc($table_pks[$pk]) . ' = ?';
			$values[] = $_GET[ $table_pks[$pk] ];

			$pk_hash[$table_pks[$pk]] = $_GET[$table_pks[$pk]];
		}

		$sql .= $where;

		$db = db_connect();
		if($db === false)
			return proc_error(l10n('error.db-connect'));

		$stmt = $db->prepare($sql);
		if($stmt === false)
			return proc_error(l10n('error.db-prepare'), $db);

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

		if($stmt->execute($values) === false)
			return proc_error(l10n('error.delete-exec'), $stmt);

		if($stmt->rowCount() != 1)
			return proc_error(l10n('error.delete-count'), $stmt);

		// call after_delete hook, if any
		if(isset($table['hooks']) && isset($table['hooks']['after_delete']) && trim($table['hooks']['after_delete']) != '')
			$table['hooks']['after_delete']($table_name, $table, $pk_hash);

		$warn = '';
		if($row != null) {
			foreach($upload_locations as $field_name => $location) {
				if(!@unlink($location . '/' . $row[$field_name]))
					$warn = ' ' . l10n('error.delete-file-warning');
			}
		}

		$_SESSION['redirect'] = "?table={$table_name}&mode=" . MODE_LIST;

		echo 'SUCCESS'; // NOTE!!! This is required for the calling javascript to properly handle the ajax return

		return proc_success(l10n('delete.success') . $warn, null, false);
	}
?>
