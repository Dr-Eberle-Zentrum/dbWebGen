<?
	//------------------------------------------------------------------------------------------
	function process_lookup_async() {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		global $APP;
		header('Content-Type: application/json');
		
		$result = array();
		
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
				break;
			}
			
			$q = mb_strtolower($q);
			if($q[0] != '%') $q = "%$q";
			if(mb_substr($q, -1) != '%') $q .= '%';			
			
			$db = db_connect();
			if($db === false) {
				die('cannot connect to db');
				break;
			}
			
			$string_trafo = '%s';
			if(isset($APP['search_string_transformation']) && $APP['search_string_transformation'] != '') {			
				$string_trafo = $APP['search_string_transformation'];
				if(strstr($string_trafo, '%s') === false)
					$string_trafo = '%s'; // $APP[search_string_transformation] does not include a placeholder for the value, i.e. %s
			}
			
			$display_expr = resolve_display_expression($field['lookup']['display']);
			
			$sql = sprintf("select %s id, %s \"label\" from %s where lower(%s) like ? order by 2", 
				db_esc($field['lookup']['field']), $display_expr, $field['lookup']['table'], $display_expr);

			$stmt = $db->prepare($sql);
			if($stmt === false) {
				die('cannot prepare stmt');
				break;
			}
			
			if($stmt->execute(array($q)) === false) {
				die('cannot execute stmt');
				break;
			}
			
			if(null === ($cur_vals = json_decode($_REQUEST['val'])) || !is_array($cur_vals))
			   $cur_vals = array();
			
			while($row = $stmt->fetch(PDO::FETCH_OBJ)) {				
				if(in_array($row->id, $cur_vals))
					continue;
				
				$row->text = format_lookup_item_label($row->label, $field['lookup']['table'], $field['lookup']['field'], $row->id);				
				$result[] = $row;
			}
			
		} while(false);
		
		echo json_encode($result);
	}
?>