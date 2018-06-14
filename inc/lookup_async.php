<?php
	//------------------------------------------------------------------------------------------
	function process_lookup_async() {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		global $APP;
		header('Content-Type: application/json');

		$result = array(
			'error_message' => null,
			'is_limited' => false,
			'items' => array()
		);

		do // just so we can easily break out
		{
			if(   !isset($_REQUEST['val'])
			   || !isset($_REQUEST['q'])
			   || !isset($_REQUEST['table'])
			   || !isset($_REQUEST['field'])
			   || !isset($TABLES[$table_name = $_REQUEST['table']])
			   || !is_array($table = $TABLES[$table_name])
			   || !isset($table['fields'][$field_name = $_REQUEST['field']])
			   || !is_array($field = $table['fields'][$field_name])
			   || !isset($field['lookup'])
			   || !isset($field['lookup']['async'])
			   || mb_strlen($q = $_REQUEST['q']) < $field['lookup']['async']['min_input_len']
			  )
			{
				$result['error_message'] = l10n('error.lookup-async.invalid-params');
				break;
			}

			if(mb_strlen($q = trim(preg_replace('/\s+/', '%', $_REQUEST['q']))) < $field['lookup']['async']['min_input_len']) {
				$result['error_message'] = l10n('error.lookup-async.query-whitespace');
				break;
			}

			$db = db_connect();
			if($db === false) {
				$result['error_message'] = l10n('error.lookup-async.connect-db');
				break;
			}

			$string_trafo = '%s';
			if(isset($APP['search_string_transformation']) && $APP['search_string_transformation'] != '') {
				$string_trafo = $APP['search_string_transformation'];
				if(substr_count($string_trafo, '%s') !== 1) // $APP[search_string_transformation] does not include a placeholder for the value, i.e. %s
					$string_trafo = '%s';
			}

			$display_expr = resolve_display_expression($field['lookup']['display'], 't');

			$limit = -1;
			if(isset($field['lookup']['async']['max_results']))
				$limit = intval($field['lookup']['async']['max_results']);

			if($field['lookup']['field'] == $field['lookup']['display']) {
				// look only in display field
				$sql = sprintf("select %s id, %s \"label\" from %s t where ($string_trafo) like concat('%%',($string_trafo),'%%') order by 2 %s",
					db_esc($field['lookup']['field']), $display_expr, db_esc($field['lookup']['table']), $display_expr, '?',
					$limit > 0 ? ('LIMIT ' . strval($limit + 1)) : '');
			}
			else {
				// look in display field and primary key field
				$sql = sprintf("select %s id, %s \"label\" from %s t where concat(($string_trafo),($string_trafo)) like concat('%%',($string_trafo),'%%') order by 2 %s",
					db_esc($field['lookup']['field']), $display_expr, db_esc($field['lookup']['table']), $display_expr, db_esc($field['lookup']['field']), '?',
					$limit > 0 ? ('LIMIT ' . strval($limit + 1)) : '');
			}

			$stmt = $db->prepare($sql);
			if($stmt === false) {
				$result['error_message'] = l10n('error.lookup-async.stmt-error');
				break;
			}

			if($stmt->execute(array($q)) === false) {
				if($debug) die('cannot execute stmt');
				$result['error_message'] = l10n('error.lookup-async.stmt-error');
				break;
			}

			if(null === ($cur_vals = json_decode($_REQUEST['val'])) || !is_array($cur_vals))
			   $cur_vals = array();

			$c = 1;
			while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
				if(in_array($row->id, $cur_vals))
					continue;
				if($limit > 0 && $c++ > $limit) {
					$result['is_limited'] = true;
					break;
				}
				$row->text = format_lookup_item_label($row->label, $field['lookup'], $row->id, 'plain');
				$result['items'][] = $row;
			}

		} while(false);

		echo json_encode($result);
	}
?>
