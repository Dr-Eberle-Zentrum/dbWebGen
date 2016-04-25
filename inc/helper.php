<?
	//------------------------------------------------------------------------------------------
	function process_redirect($flush_ob = false) {
	//------------------------------------------------------------------------------------------
		if(isset($_SESSION['redirect'])) {
			header("Location: {$_SESSION['redirect']}");
			unset($_SESSION['redirect']);
			
			if($flush_ob) {
				ob_flush();
				ob_end_clean();
			}
			
			return true;			
		}
		
		if($flush_ob) {
			ob_flush();
			ob_end_clean();
		}

		return false;
	}
	
	//------------------------------------------------------------------------------------------
	function is_popup() {
	//------------------------------------------------------------------------------------------
		return isset($_GET['popup']);
	}
		
	//------------------------------------------------------------------------------------------
	function __arr_str(&$a, $indent = 0) {
	//------------------------------------------------------------------------------------------
		$s = str_repeat(' ', $indent) . "[\n";
		foreach($a as $k => $v) {
			
			$s .= str_repeat(' ', $indent + 2) . "'{$k}' => ";
			if(is_array($v))
				$s .= "\n" . __arr_str($v, $indent + 2) . ",\n";
			else if(is_string($v))
				$s .= "'$v',\n";
			else
				$s .= "$v,\n";			
		}	
		$s .= str_repeat(' ', $indent) . "]\n";
		return $s;
	}
	
	//------------------------------------------------------------------------------------------
	function arr_str(&$a) {
	//------------------------------------------------------------------------------------------
		$s = "<pre>\n";
		$s .= __arr_str($a, 0);
		$s .= "</pre>\n";
		return $s;
	}
	
	//------------------------------------------------------------------------------------------
	function build_search_term($table, $table_alias) {
	//------------------------------------------------------------------------------------------
		global $APP;
		global $TABLES;
		
		$term = array('sql' => '', 'params' => array());		
		$fields = $table['fields'];		
		$search_field = null;
		$search_query = null;
		$search_option = SEARCH_ANY;		
		
		foreach($_GET as $p => $v) {
			switch($p) {
				case SEARCH_PARAM_OPTION:
					$search_option = $v; break;
					
				case SEARCH_PARAM_QUERY:
					$search_query = strtolower($v); break;
					
				case SEARCH_PARAM_FIELD:
					$search_field = $v; break;
					
				case SEARCH_PARAM_LOOKUP:
					$search_lookup = $v; break;
				
				default:
					break;
			}
		}
		
		if($search_query === null || $search_query == '')
			return null;
		
		if($search_field === null || !isset($fields[$search_field]))
			return null;
		
		switch($search_option) {
			case SEARCH_EXACT:
				$term['params'][] = $search_query;
				$op = '=';
				break;
			
			case SEARCH_ANY:
				$term['params'][] = '%' . $search_query . '%';
				$op = 'like';				
				break;
				
			case SEARCH_START:
				$term['params'][] = $search_query . '%';
				$op = 'like';				
				break;
			
			case SEARCH_END:
				$term['params'][] = '%' . $search_query;
				$op = 'like';				
				break;
			
			default:
				return null;
		}
		
		$string_trafo = '%s';
		if(isset($APP['search_string_transformation']) && $APP['search_string_transformation'] != '') {			
			$string_trafo = $APP['search_string_transformation'];
			if(strstr($string_trafo, '%s') === false)
				proc_error('$APP[search_string_transformation] does not include a placeholder for the value, i.e. %s');
		}
		
		$query_trafo = str_replace('%s', '?', $string_trafo);
		
		if($APP['search_lookup_resolve'] && $fields[$search_field]['type'] == T_LOOKUP && $fields[$search_field]['lookup']['cardinality'] == CARDINALITY_SINGLE) {
			$lookup = $fields[$search_field]['lookup'];
		
			$field_trafo = str_replace('%s', '%s::text', $string_trafo);
			
			$term['sql'] = sprintf("$field_trafo %s $query_trafo or (select $field_trafo from %s other where other.%s = %s.%s) %s $query_trafo", 
				db_esc($search_field), $op,
				resolve_display_expression($lookup['display']),
				db_esc($lookup['table']), db_esc($lookup['field']), db_esc($table_alias), db_esc($search_field), $op);
				
			$term['params'][]= $term['params'][count($term['params'])-1];
		}
		else if($APP['search_lookup_resolve'] && $fields[$search_field]['type'] == T_LOOKUP && $fields[$search_field]['lookup']['cardinality'] == CARDINALITY_MULTIPLE) {
			$field = $fields[$search_field];
			
			$field_trafo = str_replace('%s', "array_to_string(array_agg(%s), ' ')", $string_trafo);
			
			$term['sql'] = sprintf("(select $field_trafo FROM %s other, %s link WHERE link.%s = %s.%s AND other.%s = link.%s) %s $query_trafo", 
				resolve_display_expression($field['lookup']['display'], 'other'),
				db_esc($field['lookup']['table']), db_esc($field['linkage']['table']),
				db_esc($field['linkage']['fk_self']), $table_alias, db_esc($table['primary_key']['columns'][0]),
				db_esc($field['lookup']['field']), db_esc($field['linkage']['fk_other']), $op);
		}
		else {
			if($fields[$search_field]['type'] == T_POSTGIS_GEOM)
				$field_trafo = str_replace('%s', 'ST_AsText(%s)', $string_trafo);
			else
				$field_trafo = str_replace('%s', '%s::text', $string_trafo);
			
			$term['sql'] = sprintf("$field_trafo %s $query_trafo", db_esc($search_field), $op);		
		}
		
		#debug_log(arr_str($term));
		
		return $term;
	}
	
	//------------------------------------------------------------------------------------------
	function debug_log($msg) {
	//------------------------------------------------------------------------------------------
		$_SESSION['msg'][] = "<div class='alert alert-info'>$msg</div>";
	}
	
	//------------------------------------------------------------------------------------------
	function build_main_menu(&$menu) {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		global $APP;
		
		$menu = array();
		
		$menu[0] = array('name' => 'New', 'items' => array());
		if($APP['mainmenu_tables_autosort'])
			uasort($TABLES, 'sort_tables_new');
		foreach($TABLES as $table_name => $info)
			if(in_array(MODE_NEW, $info['actions']))
				$menu[0]['items'][] = array('label' => $info['item_name'], 'href' => "?table={$table_name}&mode=" . MODE_NEW);
		
		$menu[1] = array('name' => 'Browse & Edit', 'items' => array());
		if($APP['mainmenu_tables_autosort'])
			uasort($TABLES, 'sort_tables_list');
		foreach($TABLES as $table_name => $info)
			if(in_array(MODE_LIST, $info['actions']))
				$menu[1]['items'][] = array('label' => $info['display_name'], 'href' => "?table={$table_name}&mode=" . MODE_LIST);
			
		if(isset($APP['menu_complete_proc']) && trim($APP['menu_complete_proc']) != '')
			/*call_user_func*/ $APP['menu_complete_proc']($menu);
	}
	
	//------------------------------------------------------------------------------------------
	function is_allowed(&$table, $mode) {
	//------------------------------------------------------------------------------------------
		 return !isset($table['actions']) || in_array($mode, $table['actions']);
	}
	
	//------------------------------------------------------------------------------------------
	function db_connect() {
	//------------------------------------------------------------------------------------------
		global $DB;
		
		try {
			return new PDO(
				"pgsql:dbname={$DB['db']};host={$DB['host']};port={$DB['port']};options='--client_encoding=UTF8'", 
				$DB['user'], 
				$DB['pass']);
		} 
		catch(PDOException $e) {
			return FALSE;
		}
	}
	
	//------------------------------------------------------------------------------------------
	function get_default($def) {
	//------------------------------------------------------------------------------------------
		$def = str_replace(REPLACE_DYNAMIC_SESSION_USER, $_SESSION['user_id'], $def);
		return $def;
	}
		
	//------------------------------------------------------------------------------------------
	function safehash(&$hash, $key, $default = null) {
	//------------------------------------------------------------------------------------------
		return isset($hash[$key]) ? $hash[$key] : $default;
	}
	
	//------------------------------------------------------------------------------------------
	function sort_tables_new($a, $b) {
	//------------------------------------------------------------------------------------------
		return strcmp($a['item_name'], $b['item_name']);
	}
	
	//------------------------------------------------------------------------------------------
	function sort_tables_list($a, $b) {
	//------------------------------------------------------------------------------------------
		return strcmp($a['display_name'], $b['display_name']);
	}
	
	//------------------------------------------------------------------------------------------
	function html($text, $max_chars = 0, $expandable = false) {
	//------------------------------------------------------------------------------------------	
		if($text === null)
			return '';
		
		$text = strval($text);
		$len = strlen($text);
		
		if($max_chars > 0 && $len > $max_chars) {
			$ret = htmlspecialchars(substr($text, 0, $max_chars), ENT_COMPAT | ENT_HTML401);
			
			if($expandable)
				$ret .= "<a role='button' class='clipped_text'>[show clipped text]</a><span class='clipped_text'>" .
				htmlspecialchars(substr($text, $max_chars), ENT_COMPAT | ENT_HTML401) .
				"</span>";
			else
				$ret .= '...';
			
			return $ret;
		}
		
		else
			return htmlspecialchars($text, ENT_COMPAT | ENT_HTML401);
	}
	
	//------------------------------------------------------------------------------------------
	function html_val($field_name, $default = '') {
	//------------------------------------------------------------------------------------------
		if(!isset($_POST[$field_name]))	{		
		#	if(isset($_GET["pre:{$field_name}"]))
		#		return html($_GET["pre:{$field_name}"]);
			
			return $default;
		}
				
		return html($_POST[$field_name]);
	}
	
	//------------------------------------------------------------------------------------------
	function post_val($name, $default = '') {
	//------------------------------------------------------------------------------------------
		return isset($_POST[$name]) ? $_POST[$name] : $default;
	}
	
	//------------------------------------------------------------------------------------------
	function get_script_url($with_params) {
	//------------------------------------------------------------------------------------------
		$port = $_SERVER['SERVER_PORT'] != '80' ? ":{$_SERVER['SERVER_PORT']}" : '';
		$script = $_SERVER['SCRIPT_NAME'];
		if(substr($script, -9) == 'index.php')
			$script = substr($script, 0, -9);
		
		$url = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['SERVER_NAME']}{$port}/{$script}";
		
		if($with_params)
			$url .= build_get_params();
		
		return $url;
	}
	
	//------------------------------------------------------------------------------------------
	// params in $arr_additional override those in $_GET !!
	function build_get_params($arr_additional = array(), $clean = false) {
	//------------------------------------------------------------------------------------------
		$u = array();
		
		if(!$clean) {
			foreach($_GET as $p => $v) {
				if(isset($arr_additional[$p])) {
					$v = $arr_additional[$p];				
					unset($arr_additional[$p]);
				}
				$u[$p] = $v;
			}
		}
		
		foreach($arr_additional as $p => $v)
			$u[$p] = $v;
			
		return '?' . http_build_query($u);
	}
	
	//------------------------------------------------------------------------------------------
	function is_positive_int($v) {
	//------------------------------------------------------------------------------------------
		return preg_match('/^[1-9]+[0-9]*$/', strval($v));
	}
	
	//------------------------------------------------------------------------------------------
	function in_range($val, $bound_lo, $bound_hi, $inclusive = true) {
	//------------------------------------------------------------------------------------------
		return $inclusive ? ($val >= $bound_lo && $val <= $bound_hi) : ($val > $bound_lo && $val < $bound_hi);
	}
	
	//------------------------------------------------------------------------------------------
	function get_session($what, $default = '') {
	//------------------------------------------------------------------------------------------
		if(!isset($_SESSION[$what]))
			return $default;
		
		return $_SESSION[$what];
	}
	
	//------------------------------------------------------------------------------------------
	function render_messages() {
	//------------------------------------------------------------------------------------------
		foreach($_SESSION['msg'] as $msg) {
			echo $msg;
		}
		
		$_SESSION['msg'] = array();
	}
	
	//------------------------------------------------------------------------------------------
	function check_table_name($n) {
	//------------------------------------------------------------------------------------------
		return preg_match('/^[a-zA-Z0-9_]+$/i', $n);
	}
	
	//------------------------------------------------------------------------------------------
	function proc_error($txt, $db = null, $clear_msg_buffer = false) {
	//------------------------------------------------------------------------------------------
		if($clear_msg_buffer)
			$_SESSION['msg'] = array();
		
		$msg = '<div class="alert alert-danger"><b>Error</b>: ' . $txt;
		if(is_object($db)) {			
			$e = $db->errorInfo();
			$msg .= "<ul>\n<li>". str_replace("\n", '</li><li>', html($e[2])) . "</li>\n";
			$msg .= "<li>Error Codes: SQLSTATE {$e[0]}, Driver {$e[1]}</li>\n";
			$msg .= "</ul>\n";
		}
		$msg .= "</div>\n";
		$_SESSION['msg'][] = $msg;
		return false;
	}
	
	//------------------------------------------------------------------------------------------
	function proc_success($txt) {
	//------------------------------------------------------------------------------------------
		$_SESSION['msg'][] = '<div class="alert alert-success"><b>Success</b>: ' . html($txt) . "</div>\n";
		return true;
	}
	
	//------------------------------------------------------------------------------------------
	function get_file_url($file_name, $field_info) {
	//------------------------------------------------------------------------------------------
		$url = $field_info['location'] . '/' . $file_name;
		return str_replace('//', '/', $url);
	}
	
	//------------------------------------------------------------------------------------------
	function is_field_editable(&$field) {
	//------------------------------------------------------------------------------------------
		return !isset($field['editable']) || $field['editable'] === true;
	}
	

	//------------------------------------------------------------------------------------------
	function is_allowed_create_new(&$field) {
	// default is true
	//------------------------------------------------------------------------------------------
		if(isset($field['allow-create']) && $field['allow-create'] === false)
			return false;
		
		return true;
	}
	
	
	//------------------------------------------------------------------------------------------
	function is_field_required(&$field_info) {
	//------------------------------------------------------------------------------------------
		return isset($field_info['required']) && $field_info['required'] === true;
		//return isset($field_info['required']) && $field_info['required'] === 'false';
	}
	
	//------------------------------------------------------------------------------------------
	function is_field_setnull($field_name, &$field_info) {
	//------------------------------------------------------------------------------------------
		return 
			(isset($_POST["{$field_name}_null"]) && $_POST["{$field_name}_null"] === 'true') 
			
			||
			
			(isset($_POST[$field_name]) && $_POST[$field_name] === NULL_OPTION && 
			 ($field_info['type'] == T_ENUM || $field_info['type'] == T_LOOKUP));
	}
	
	//------------------------------------------------------------------------------------------
	function get_primary_key_values_from_url($table) {
	//------------------------------------------------------------------------------------------
		$pk_vals = array();
		
		foreach($table['primary_key']['columns'] as $pk) {
			if(!isset($_GET[$pk]))
				return proc_error("Key '$pk' of object to edit not provided");
			
			$pk_vals[$pk] = $_GET[$pk];
		}
		
		return $pk_vals;
	}
	
	//------------------------------------------------------------------------------------------
	function db_esc($name) {
	//------------------------------------------------------------------------------------------
		global $DB;
		
		switch($DB['type']) {
			case DB_POSTGRESQL:
				$escape_char = '"';
				break; 
				
			default:
				return proc_error('Invalid database type specified in config/settings.php'); 
		}
		
		if($name[0] == $escape_char)
			return $name; // already escaped
		
		return $escape_char . $name . $escape_char;
	}
	
	//------------------------------------------------------------------------------------------
	// $return_escaped:
	//   if NULL, it will return escaped only of $fieldname is already escaped, otherwise not
	//   if TRUE/FALSE, it will/will not escape the postfixed fieldname
	function db_postfix_fieldname($fieldname, $postfix, $return_escaped) {
	//------------------------------------------------------------------------------------------
		global $DB;
		
		switch($DB['type']) {
			case DB_POSTGRESQL:
				$escape_char = '"';
				break; 
				
			default:
				return proc_error('Invalid database type specified in config/settings.php'); 
		}
		
		$fieldname_unescaped = trim($fieldname, $escape_char);
		$was_escaped = ($fieldname_unescaped == $fieldname);
		$do_escape = ($return_escaped === TRUE || ($return_escaped === NULL && $was_escaped === TRUE));
		
		if(!$do_escape)
			$escape_char = '';
		
		return "{$escape_char}{$fieldname}{$postfix}{$escape_char}";
	}
	
	//------------------------------------------------------------------------------------------
	function db_get_single_val($sql, $params, &$retrieved_value) {
	//------------------------------------------------------------------------------------------
		$db = db_connect();
		if($db === false)
			return proc_error('Cannot connect to DB.');
		
		$stmt = $db->prepare($sql);
		if($stmt === false)
			return proc_error('Preparing SQL statement failed', $db);
		
		if(false === $stmt->execute($params))
			return proc_error('Executing SQL statement failed', $db);
		
		$retrieved_value = $stmt->fetchColumn();
		return true;
	}
	
	//------------------------------------------------------------------------------------------
	function db_get_single_row($sql, $params, &$row) {
	//------------------------------------------------------------------------------------------
		$db = db_connect();
		if($db === false)
			return proc_error('Cannot connect to DB.');
		
		$stmt = $db->prepare($sql);
		if($stmt === false)
			return proc_error('Preparing SQL statement failed', $db);
		
		if(false === $stmt->execute($params))
			return proc_error('Executing SQL statement failed', $db);
		
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return true;
	}
	
	//------------------------------------------------------------------------------------------
	function get_file_upload_error_msg($code) {
	//------------------------------------------------------------------------------------------
    
        switch ($code) { 
            case UPLOAD_ERR_INI_SIZE: 
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini"; 
                break; 
            case UPLOAD_ERR_FORM_SIZE: 
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"; 
                break; 
            case UPLOAD_ERR_PARTIAL: 
                $message = "The uploaded file was only partially uploaded"; 
                break; 
            case UPLOAD_ERR_NO_FILE: 
                $message = "No file was uploaded"; 
                break; 
            case UPLOAD_ERR_NO_TMP_DIR: 
                $message = "Missing a temporary folder"; 
                break; 
            case UPLOAD_ERR_CANT_WRITE: 
                $message = "Failed to write file to disk"; 
                break; 
            case UPLOAD_ERR_EXTENSION: 
                $message = "File upload stopped by extension"; 
                break; 
            default: 
                $message = "Unknown upload error"; 
                break; 
        } 
        return $message; 
    } 
	
	//------------------------------------------------------------------------------------------
	function resolve_display_expression($display, $table_qualifier = '') {
	//------------------------------------------------------------------------------------------
		if($table_qualifier != '')
			$table_qualifier = db_esc($table_qualifier) . '.';
		
		if(!is_array($display)) // simple field name string
			return $table_qualifier . db_esc($display);
			
		// here we have something like 
		// 'display' => [ 'columns' => ['firstname', 'lastname'], 'expression' => "concat_ws(' ', %1 %2)" ]
		if(!isset($display['columns']) || !is_array($display['columns']) || !isset($display['expression']))
			proc_error('Invalid display expression');
		
		$expr = $display['expression'];
		for($i = 1; /* loop until nothin is replaced any more */; $i++) {
			// keep replacing as long as there is something to replace
			if(strpos($expr, "%{$i}") === FALSE)
				return $expr;
			
			$expr = str_replace("%{$i}", $table_qualifier . db_esc($display['columns'][$i - 1]), $expr);
		}
		
		return proc_error('Something is totally wrong here. Contact your therapist.');
	}
	
	//------------------------------------------------------------------------------------------	
	function build_query($table_name, $table, $offset, $mode, $more_params, &$out_params) {
	//------------------------------------------------------------------------------------------
		global $APP;
		
		$out_params = array();
		
		if($mode != MODE_EDIT && $mode != MODE_LIST && $mode != MODE_VIEW)
			return proc_error('Unknown page mode.');
		
		$q = 'SELECT ';
		
		$cols = '';
		foreach($table['fields'] as $field_name => $field) {
			if($cols != '') 
				$cols .= ', ';
			
			// geometry
			if($field['type'] == T_POSTGIS_GEOM) {
				$cols .= sprintf('ST_AsText(%s) %s',  db_esc($field_name), db_esc($field_name));
				continue;
			}
			
			// lookup single field
			if($field['type'] == T_LOOKUP && $field['lookup']['cardinality'] == CARDINALITY_SINGLE) {
				if($mode == MODE_LIST || $mode == MODE_VIEW) {
					$cols .= sprintf('(SELECT %s FROM %s WHERE %s = t.%s) %s, t.%s %s',
						resolve_display_expression($field['lookup']['display']),
						db_esc($field['lookup']['table']), db_esc($field['lookup']['field']),
						db_esc($field_name), db_esc($field_name), db_esc($field_name), 
						db_postfix_fieldname($field_name, '_raw', true)); 
				}
				else {
					$cols .= db_esc($field_name);
				}
				
				continue;
			}
						
			// lookup multiple records 
			//TODO: WORK WITH COMPOSITE FK_SELF AND FK_OTHER
			if($field['type'] == T_LOOKUP && $field['lookup']['cardinality'] == CARDINALITY_MULTIPLE) {
				if($mode == MODE_LIST || $mode == MODE_VIEW) {
					$cols .= sprintf(
						"(SELECT array_to_string(array_agg(%s), '; ') " .
						'FROM %s other, %s link WHERE link.%s = t.%s AND other.%s = link.%s) %s',
						resolve_display_expression($field['lookup']['display'], 'other'),
						db_esc($field['lookup']['table']), db_esc($field['linkage']['table']),
						db_esc($field['linkage']['fk_self']), db_esc($table['primary_key']['columns'][0]),
						db_esc($field['lookup']['field']), db_esc($field['linkage']['fk_other']), db_esc($field_name));
				}
				else { // MODE_EDIT
					#$cols .= sprintf("(SELECT array_to_string(array_agg(link.%s), '%s') ".
					$cols .= sprintf("(SELECT array_to_json(array_agg(link.%s)) ".
							 "FROM %s link WHERE link.%s = ?) %s",
							 db_esc($field['linkage']['fk_other']), 
							 db_esc($field['linkage']['table']), db_esc($field['linkage']['fk_self']),
							 db_esc($field_name));
					
					$vals = array_values($offset);
					$out_params[] = $vals[0];
				}					
					
				continue;
			}
			
			// normal field!
			$cols .= db_esc($field_name);			
		}
		
		// now add any keys that are lookup values as "raw" fields, to properly create the link for list view
		if($mode == MODE_LIST || $mode == MODE_VIEW) {
			$pk_fields = '';
			foreach($table['primary_key']['columns'] as $pk)
				$pk_fields .= sprintf(', %s %s', $pk, db_postfix_fieldname($pk, '_raw', true));
			
			$cols .= $pk_fields;
		}
		
		$q .= sprintf('%s FROM %s t', $cols, db_esc($table_name)); 
		
		if($mode == MODE_EDIT || $mode == MODE_VIEW) {
			//TODO: WORK WITH COMPOSITE FK_SELF AND FK_OTHER
			$where = '';
			foreach($offset as $col => $val) {
				$where .= ($where != ''? ' AND ' : ' ') . db_esc($col) . ' = ?';
				$out_params[] = $val;
			}
			$q .= " WHERE $where";
		}
		
		if($mode == MODE_LIST) {
			$search = build_search_term($table, 't');
			
			if($search !== null) { // search is on
				$q .= ' WHERE ' . $search['sql'];
				
				foreach($search['params'] as $param)
					$out_params[] = $param;
			}
			
			$order_by = array();
			
			if(isset($_GET['sort']) && isset($table['fields'][$_GET['sort']])) {
				$dir = isset($_GET['dir']) ? $_GET['dir'] : 'asc';
				if($dir != 'asc' && $dir != 'desc')
					$dir = 'asc';
				
				$order_by[] = db_esc($_GET['sort']) . " $dir";
			}
			
			if(count($order_by) == 0 && isset($table['sort']) && is_array($table['sort']) && count($table['sort']) > 0 ) {
				foreach($table['sort'] as $field_name => $dir) {
					$order_by[] = db_esc($field_name) . " $dir";
					
					// fake the $_GET for later
					$_GET['sort'] = $field_name;
					$_GET['dir'] = $dir;
				}
			}
			
			if(count($order_by) > 0)
				$q .= ' ORDER BY ' . implode(', ', $order_by);
			
			$q .= " LIMIT ". $APP['page_size'] . " OFFSET $offset";
		}
		
		return $q;
	}
	
	//------------------------------------------------------------------------------------------
	function enable_delete() {
	//------------------------------------------------------------------------------------------
	echo <<<END
			<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<!--<div class="modal-header">                
						</div>-->
						<div class="modal-body">
							<h4>Confirm Delete</h4>
							Please confirm that you want to delete this record. This action cannot be undone. Note the deletion will only work if the record is not referenced by some other record.				
						</div>			
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
							<a class="btn btn-danger btn-ok">Delete</a>
						</div>
					</div>
				</div>
			</div>

			<script>
			$('#confirm-delete').on('click', '.btn-ok', function(e) {				
				$.get($(this).data('href'), function(data) {
					$('#confirm-delete').modal('hide');
					
					if(data == 'SUCCESS')
						location.reload();
					else
						$('#main-container').prepend( $(data) );
				});
			});
			$('#confirm-delete').on('show.bs.modal', function(e) {
			  var data = $(e.relatedTarget).data();  
			  $('.btn-ok', this).data('href', data.href);
			});
			</script>
END;
	}
?>