<?
	foreach(array('host' => 'localhost', 'port' => 5432, 'name' => '', 'user' => 'postgres', 'pass' => '', 'name' => '', 'schema' => 'public') as $k => $v)
		${'db_' . $k} = isset($_POST[$k]) ? $_POST[$k] : $v;
	
	$form = <<<FORM
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
		echo "<p>ERROR: Cannot connect to database</p>";
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
	
	// set schema
	db_exec('set search path to ' . $db_schema);
	
	// fetch all tables in schema
	$res = db_exec('select table_name from information_schema.tables where table_schema = ? order by table_name', array($db_schema));
	$tables = array();
	while($table_name = $res->fetchColumn())
		$tables[$table_name] = array();
	
	print_r($tables);
	
	foreach($tables as $table_name) {
		
	}
?>