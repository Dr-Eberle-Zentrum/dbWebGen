<?
	foreach(array('host' => 'localhost', 'port' => 5432, 'name' => '', 'user' => 'postgres', 'pass' => '', 'name' => '', 'schema' => 'public') as $k => $v)
		${'db_' . $k} = isset($_POST[$k]) ? $_POST[$k] : $v;
	
	$form = <<<FORM
		<h1>dbWebGen Settings Generator</h1>
		<p>This tool generates a working stub of settings.php to use for your database with dbWebGen.</p>
		<p>Enter the details of your PostgreSQL database:</p>
		<form method="post">
			<table>
				<tr>
					<th>Host</th>
					<td><input type="text" name="host" value="$db_host" /></td>
				</tr>
				<tr>
					<th>Port</th>
					<td><input type="text" name="port" value="$db_port" /></td>
				</tr>
				<tr>
					<th>Username</th>
					<td><input type="text" name="user" value="$db_user" /></td>
				</tr>
				<tr>
					<th>Password</th>
					<td><input type="password" name="pass" value="$db_pass" /></td>
				</tr>
				<tr>
					<th>Database</th>
					<td><input type="text" name="name" value="$db_name" /></td>
				</tr>
				<tr>
					<th>Schema</th>
					<td><input type="text" name="schema" value="$db_schema" /></td>
				</tr>					
			</table>
			<p><input type="submit" value="Generate Settings" /></p>
		</form>
FORM;

	if(count($_POST) == 0) {
		print $form;
		exit;
	}
	
	try {
		$db = new PDO("pgsql:dbname={$db_name};host={$db_host};port={$db_port};options='--client_encoding=UTF8'", $db_user, $db_pass);
	} 
	catch(PDOException $e) {
		echo "<h2>ERROR: Cannot connect to database</h2>";
		echo $form;
		exit;
	}
	
	function db_exec($sql, $params = null) {
		global $db;
		$stmt = $db->prepare($sql);
		if($stmt === false || $stmt->execute($params) === false)
			return false;
		return $stmt;
	}
	
	header('Content-Type: text/plain; charset=utf8');
	include 'inc/constants.php';
	include 'settings.template.php';
	
	// set schema
	db_exec('set search path to ' . $db_schema);
	
	// fetch all tables in schema
	$tables_query = <<<SQL
		select table_name from information_schema.tables
		where table_schema = ?
		and table_type = 'BASE TABLE'
		AND table_schema NOT IN ('pg_catalog', 'information_schema')
		AND table_name not in ('spatial_ref_sys')
		order by table_name
SQL;
	$res = db_exec($tables_query, array($db_schema));

	$tables = array();
	while($table_name = $res->fetchColumn())
		$tables[] = $table_name;
	
	// target var
	$TABLES = array();
	
	// loop through all tables and generate table info stub
	foreach($tables as $table_name) {
		// general table info
		$TABLES[$table_name] = array(
			'display_name' => $table_name,
			'description' => '',
			'item_name' => $table_name,
			'actions' => array(MODE_EDIT, MODE_NEW, MODE_VIEW, MODE_LIST, MODE_DELETE, MODE_LINK),			
			'fields' => array()
		);
	}
	
	// loop again and fill the stubs
	foreach($tables as $table_name) {
		// add all fields
		$columns_query = <<<SQL
			SELECT *
			FROM information_schema.columns
			WHERE table_name = ?
			AND table_schema = ?
			ORDER BY ordinal_position
SQL;
		$res = db_exec($columns_query, array($table_name, $db_schema));
		
		$column_defaults = array();
		
		while($col = $res->fetch(PDO::FETCH_ASSOC)) {
			// used later for primary key auto increment
			$column_defaults[$col['column_name']] = $col['column_default'];
			
			// put default text line fields
			$field = array(
				'label' => $col['column_name'],
				'type' => T_TEXT_LINE, // TODO identify type or value range (T_ENUM)
				'required' => $col['is_nullable'] == 'YES' ? false : true,
				'editable' => $col['is_updatable'] == 'YES' ? true : false
			);
			
			if($col['character_maximum_length'] !== null)
				$field['len'] = $col['character_maximum_length'];
			
			$TABLES[$table_name]['fields'][$col['column_name']] = $field;
		}
		
		// go through PRIMARY KEY constraints
		$primary_key = array(			
			'columns' => array()
		);		
		
		$constraints_query = <<<SQL
			SELECT tc.constraint_name,
				tc.constraint_type,				
				kcu.column_name	
				FROM information_schema.table_constraints tc				
				LEFT outer JOIN information_schema.key_column_usage kcu
				ON tc.constraint_catalog = kcu.constraint_catalog
				AND tc.constraint_schema = kcu.constraint_schema
				AND tc.constraint_name = kcu.constraint_name				
				WHERE tc.constraint_type = 'PRIMARY KEY' 
				AND tc.table_schema = ?
				AND tc.table_name = ?
SQL;

		$res = db_exec($constraints_query, array($db_schema, $table_name));
		while($cons = $res->fetch(PDO::FETCH_ASSOC)) {		
			$primary_key['columns'][] = $cons['column_name'];			
		}
		
		// go through FOREIGN KEY constraints
		
		$foreign_keys_info = array();
		
		$constraints_query = <<<SQL
			SELECT tc.constraint_name,
				tc.constraint_type,				
				kcu.column_name,
				ccu.table_name references_table,
				ccu.column_name references_field
				FROM information_schema.table_constraints tc				
				LEFT outer JOIN information_schema.key_column_usage kcu
				ON tc.constraint_catalog = kcu.constraint_catalog
				AND tc.constraint_schema = kcu.constraint_schema
				AND tc.constraint_name = kcu.constraint_name			
				LEFT outer JOIN information_schema.constraint_column_usage ccu
				ON tc.constraint_catalog = ccu.constraint_catalog
				AND tc.constraint_schema = ccu.constraint_schema
				AND tc.constraint_name = ccu.constraint_name
				WHERE tc.constraint_type = 'FOREIGN KEY' 
				AND tc.table_schema = ?
				AND tc.table_name = ?
SQL;
		
		$res = db_exec($constraints_query, array($db_schema, $table_name));
		while($cons = $res->fetch(PDO::FETCH_ASSOC)) {					
			$field = $TABLES[$table_name]['fields'][$cons['column_name']];
			
			$field['type'] = T_LOOKUP;
			$field['lookup'] = array(
				'cardinality' => CARDINALITY_SINGLE,
				'table'  => $cons['references_table'],
				'field'  => $cons['references_field'],
				'display' => $cons['references_field'] 
			);
			
			// remember the foreign keys in a hash for later
			$foreign_keys_info[$cons['column_name']] = $field; 
			
			// overwrite default field info 
			$TABLES[$table_name]['fields'][$cons['column_name']] = $field;
		}
		
		$primary_key['auto'] = false;		
		
		// check whether the primary key is determined by a sequence:		
		if(count($primary_key['columns']) == 1) {
			// and there is a default val for the columns
			if($column_defaults[$primary_key['columns'][0]] !== null) {
				// check whether it is the nextval of a sequence
				if(preg_match('/^nextval\\(\'(.+)\'::regclass\\)$/', $column_defaults[$primary_key['columns'][0]], $matches)) {
					$primary_key['auto'] = true;
					$primary_key['sequence_name'] = $matches[1];
					$TABLES[$table_name]['fields'][$primary_key['columns'][0]]['editable'] = false;
				}
			}
		}
		
		// set primary key
		$TABLES[$table_name]['primary_key'] = $primary_key;
		
		// check whether this is a N:M table (for CARDINALITY_MULTIPLE)
		// this is the case if this table has:
		// * exactly two primary key fields
		// * both are foreign keys to two different tables
		// If both conditions hold we add this table as a linkage table in CARDINALTY_MULTIPLE field in both referenced tables
		if(count($primary_key['columns']) == 2) {
			$field1 = $field2 = null;
			if(isset($foreign_keys_info[$primary_key['columns'][0]])
				&& isset($foreign_keys_info[$primary_key['columns'][1]]))
			{
				$field0 = $foreign_keys_info[$primary_key['columns'][0]];
				$field1 = $foreign_keys_info[$primary_key['columns'][1]];
				
				if($field0['lookup']['table'] != $field1['lookup']['table']) {
					// here we go, add cardinality multiple lookup to both involved tables
					$TABLES[$field0['lookup']['table']]['fields'][$table_name . '_fk'] = array(
						'label' => $table_name . ' list',
						'required' => false,
						'editable' => true,
						'type' => T_LOOKUP,
						'lookup' => array(
							'cardinality' => CARDINALITY_MULTIPLE,
							'table'  => $field1['lookup']['table'],
							'field'  => $field1['lookup']['field'],
							'display' => $field1['lookup']['display']
						),
						'linkage' => array(
							'table' => $table_name,
							'fk_self' => $primary_key['columns'][0],
							'fk_other' => $primary_key['columns'][1]
						)
					);
					
					$TABLES[$field1['lookup']['table']]['fields'][$table_name . '_fk'] = array(
						'label' => $table_name . ' list',
						'required' => false,
						'editable' => true,
						'type' => T_LOOKUP,
						'lookup' => array(
							'cardinality' => CARDINALITY_MULTIPLE,
							'table'  => $field0['lookup']['table'],
							'field'  => $field0['lookup']['field'],
							'display' => $field0['lookup']['display']
						),
						'linkage' => array(
							'table' => $table_name,
							'fk_self' => $primary_key['columns'][1],
							'fk_other' => $primary_key['columns'][0]
						)
					);
				}
			}
		}
	}

	// ================================================
	// APP
	// ================================================	
	$APP = array(
		'title' => $db_name . ' Database',
		'view_display_null_fields' => false,
		'page_size'	=> 10,
		'max_text_len' => 250,
		'pages_prevnext' => 2,
		'mainmenu_tables_autosort' => true,
		'search_lookup_resolve' => true,
		'search_string_transformation' => 'lower((%s)::text)'
	);
	echo '<?php', PHP_EOL, '$APP = ';	
	var_export($APP);
	echo ';', PHP_EOL, PHP_EOL;

	// ================================================
	// APP
	// ================================================
	$DB = array(
		'type' => DB_POSTGRESQL,
		'host' => $db_host,
		'port' => intval($db_port),
		'user' => $db_user,
		'pass' => $db_pass,
		'db'   => $db_name
	);
	echo '$DB = ';	
	var_export($DB);
	echo ';', PHP_EOL, PHP_EOL;

	// ================================================
	// LOGIN
	// ================================================
	$LOGIN = array();
	echo '$LOGIN = ';	
	var_export($LOGIN);
	echo ';', PHP_EOL, PHP_EOL;

	// ================================================
	// TABLES
	// ================================================
	echo '$TABLES = ';
	var_export($TABLES);
	echo ';', PHP_EOL, '?>';
?>