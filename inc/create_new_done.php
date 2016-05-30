<?
	//------------------------------------------------------------------------------------------
	function process_create_new_done() {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		echo "<!DOCTYPE html>\n<head></head>\n<body>\n";

		/* 
		here we come with the following GET parameters:
			- "table" where new item was created
			- "lookup_table" the source tabke for which a "create new" was clicked, to obtain the display expression
			- "lookup_field" the source field for which a "create new" was clicked, to obtain the display expression
			- "pk_value" the primary key value of the newly created record
		*/
	
		if(!isset($_GET['table']) || !isset($_GET['lookup_table']) || !isset($_GET['lookup_field']) || !isset($_GET['pk_value']))
			return proc_error('Invalid params');
		
		$table_name = $_GET['table'];
		if(!isset($TABLES[$table_name]))
			return proc_error('Invalid table');
		
		$table = $TABLES[$table_name];
		$pk_column = $table['primary_key']['columns'][0];
		
		$pk_value = $_GET['pk_value'];
		if(trim($pk_value) == '')
			return proc_error('Invalid pk value');
		
		$lookup_table_name = $_GET['lookup_table'];
		if(!isset($TABLES[$lookup_table_name]))
			return proc_error('Invalid lookup table');
		
		$lookup_table = $TABLES[$lookup_table_name];
		
		$lookup_field = $_GET['lookup_field'];
		if(!isset($lookup_table['fields'][$lookup_field]) || !isset($lookup_table['fields'][$lookup_field]['lookup']))
			return proc_error('Invalid lookup field');
		
		$display_expr = $lookup_table['fields'][$lookup_field]['lookup']['display'];
		
		$db = db_connect();
		if($db === false)
			return proc_error('DB connect failed');
		
		$sql = sprintf('select %s display from %s where %s = ?',
			resolve_display_expression($display_expr), $table_name, $pk_column);
		
		$stmt = $db->prepare($sql);
		if($stmt === false)
			return proc_error('Prep failed');
		
		$res = $stmt->execute(array($pk_value));
		if($res === false)
			return proc_error('Exec failed');
		
		$label = $stmt->fetchColumn();
		$text = "$label ($pk_column = $pk_value)";
		
		echo "<script>\n";
		echo "  var r = " . json_encode(array('lookup_field' => $lookup_field, 'value' => $pk_value, 'label' => $label, 'text' => $text)) . ";\n";
		echo "  try { window.opener.handle_create_new_result(r); } catch(e) {}\n";
		echo "  window.close();\n";
		echo "</script>\n</body>";
	}
?>