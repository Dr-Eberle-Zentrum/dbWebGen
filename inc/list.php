<?
	/*
		Functions to render and process a list of records within a table.
	*/

	//------------------------------------------------------------------------------------------
	function get_pagination($count, &$num_pages, &$cur_page) {
	//------------------------------------------------------------------------------------------
		global $APP;

		$cur_page = 1;
		$num_pages = ceil($count / $APP['page_size']);
		if($num_pages < 2)
			return '';

		if(isset($_GET['page']) && is_positive_int($_GET['page']) && in_range($_GET['page'], 1, $num_pages))
			$cur_page = intval($_GET['page']);

		$prev_disabled = ($cur_page <= 1 ? 'disabled' : '');
		$next_disabled = ($cur_page >= $num_pages ? 'disabled' : '');

		$prev_href = build_get_params(array('page' => $cur_page - 1));
		$next_href = build_get_params(array('page' => $cur_page + 1));

		$div = "<p><div class='hidden-print'><div class='btn-group'>\n";
		$div .= "<a href='$prev_href' class='btn btn-sm btn-default $prev_disabled'><span class='glyphicon glyphicon-triangle-left'></span></a>\n";
		$div .= "<a href='$next_href' class='btn btn-sm btn-default $next_disabled'><span class='glyphicon glyphicon-triangle-right'></span></a>\n";
		$div .= '</div><span class="page-jumper">Jump to page: ';

		$before_skip = ($cur_page >= 3 + $APP['pages_prevnext']);
		$after_skip = ($num_pages - $cur_page >= 3 + $APP['pages_prevnext']);

		$lower_bound = max($cur_page - $APP['pages_prevnext'], 1);
		$upper_bound = min($cur_page + $APP['pages_prevnext'], $num_pages);
		$before_skipped = false;
		$after_skipped = false;

		for($p = 1; $p <= $num_pages; $p++) {
			$style = ($p == $cur_page ? 'btn-primary disabled' : 'btn-default');

			if($p == 1 || $p == $num_pages || ($p >= $lower_bound && $p <= $upper_bound))
				$div .= "<a href='".build_get_params(array('page' => $p))."' class='btn btn-sm $style'>$p</a>";
			else if($p < $lower_bound && !$before_skipped) {
				$before_skipped = true;
				$div .= " • • • ";
			}
			else if($p > $upper_bound && !$after_skipped) {
				$after_skipped = true;
				$div .= " • • • ";
			}
		}

		$div .= "\n";
		$div .= "</span></div></p>\n";
		return $div;
	}

	//------------------------------------------------------------------------------------------
	function initialize_search_popover() {
	//------------------------------------------------------------------------------------------
		echo "<script>var search_popover_template = \"<form class='form-inline' action='".build_get_params()."' method='get'><div class='form-group'>" .
			"<select id='searchoption' class='form-control input-sm space-right' name='".SEARCH_PARAM_OPTION."'>".
			"<option value='".SEARCH_ANY."'>Field contains</option>".
			"<option value='".SEARCH_WORD."'>Field contains word</option>".
			"<option value='".SEARCH_EXACT."'>Field is exactly</option>".
			"<option value='".SEARCH_START."'>Field starts with</option>".
			"<option value='".SEARCH_END."'>Field ends with</option>".
			"</select>".
			"<input id='searchtext' type='text' class='form-control input-sm space-right' name='".SEARCH_PARAM_QUERY."' placeholder='Enter search text' autofocus>".
			"<input type='hidden' name='table' value='{$_GET['table']}'>".
			"<input type='hidden' name='mode' value='list'>".
			"<input type='hidden' name='".SEARCH_PARAM_FIELD."' value='%FIELDNAME%'>". // the %FIELDNAME% is replaced during runtime in dbweb.js
			//"<input class='btn btn-sm btn-primary' type='submit' value='Search'/></div></form>\";</script>\n";
			"<button class='btn btn-sm btn-primary' type='submit'><span class='glyphicon glyphicon-search'></span></button></div></form>\";</script>\n";

		echo "<div id='search-popover'></div>\n"; //needed for CSS styling of the popover
	}

	//------------------------------------------------------------------------------------------
	function render_list() {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		global $APP;

		$table_name = $_GET['table'];
		if(!isset($TABLES[$table_name]))
			return proc_error('Invalid table name or table not configured.');

		$table = $TABLES[$table_name];

		if(!is_allowed($table, $_GET['mode']))
			return proc_error('You are not allowed to perform this action.');

		$fields = $table['fields'];

		echo "<h1>{$table['display_name']}</h1>\n";
		echo "<p>{$table['description']}</p>\n";

		$search = build_search_term($table, 'd');

		$db = db_connect();
		if($db === false)
			return proc_error('Cannot connect to DB.');

		$sql = 'SELECT COUNT(*) FROM ' . db_esc($table_name) . ' d' . ($search !== null ? " WHERE {$search['sql']}" : '');
		#debug_log($sql);

		$res = $db->prepare($sql);
		$res->execute($search !== null? $search['params'] : array());

		$num_records = intval($res->fetchColumn());

		$total_in_table = -1;
		if($search !== null) {
			$res = $db->prepare('SELECT COUNT(*) FROM ' . db_esc($table_name));
			$res->execute();
			$total_in_table = intval($res->fetchColumn());
		}

		$pag = get_pagination($num_records, $num_pages, $cur_page);
		$offset = ($cur_page - 1) * $APP['page_size'];
		$start_record = $offset + 1;
		$end_record = min($start_record + $APP['page_size'] - 1, $num_records);

		initialize_search_popover();

		echo "<div class='col-sm-12'>\n";

		if(is_allowed($table, MODE_NEW))
			echo "<p class='hidden-print'><a href='?table={$table_name}&amp;mode=".MODE_NEW."' class='btn btn-default'><span class='glyphicon glyphicon-plus'></span> New {$table['item_name']}</a></p>\n";

		if($search !== null) {
			$search_type = 'contains';
			if(isset($_GET[SEARCH_PARAM_OPTION])) switch($_GET[SEARCH_PARAM_OPTION]) {
				case SEARCH_END: $search_type = 'ends with'; break;
				case SEARCH_START: $search_type = 'starts with'; break;
				case SEARCH_EXACT: $search_type = 'matches'; break;
				case SEARCH_WORD: $search_type = 'contains word'; break;
			}
			echo "<p class='text-info'>Searching all records where <b>".html($fields[$_GET[SEARCH_PARAM_FIELD]]['label'])."</b> {$search_type} <span class='bg-success'><strong>".html($_GET[SEARCH_PARAM_QUERY])."</strong></span> ".
			"<a class='btn btn-default space-left hidden-print' href='?".http_build_query(array('table'=>$table_name, 'mode'=>MODE_LIST))."'><span class='glyphicon glyphicon-remove-circle'></span> Clear search</a></p>\n";
		}

		if($num_records == 0) {
			echo "<p class='text-info'>No records found.</p>";
		}
		else {
			$what = ($search !== null ? 'search results' : 'records');
			$total_ind = ($total_in_table > 0 ? "  (total in table: <b>$total_in_table</b>)" : '');

			echo "<p class='text-info'>Displaying {$what} <b>{$start_record}</b>&ndash;<b>{$end_record}</b> of <b>{$num_records}</b>{$total_ind}</p>";
			$query = build_query($table_name, $table, $offset, MODE_LIST, null, $params_arr);

			#debug_log($query);
			#debug_log($params_arr);

			$res = $db->prepare($query);
			if($res === FALSE)
				return proc_error('List query preparation went wrong.', $db);

			if($res->execute($params_arr) === FALSE)
				return proc_error('List query execution went wrong.', $db);

			echo $pag;

			$relevant_fields = array();
			foreach($fields as $field_name => &$field)
				if(!is_field_hidden_in_list($field))
					$relevant_fields[$field_name] = $field;
			require_once 'record_renderer.php';
			$rr = new RecordRenderer($table_name, $table, $relevant_fields, $res, true, true, null);
			echo $rr->html();

			echo $pag;
		}

		echo "</div>";

		if(is_allowed($table, MODE_DELETE))
			enable_delete();
	}
?>
