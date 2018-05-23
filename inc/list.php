<?php
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
		$div .= '</div><span class="page-jumper">' . l10n('list.jump-to-page') . ': ';

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
		echo sprintf(
			"<script>var search_popover_template = \"<form class='form-inline' action='%s' method='get'><div class='form-group'>" .
			"<select id='searchoption' class='form-control input-sm space-right' name='%s'>".
			"<option value='%s'>%s</option>".
			"<option value='%s'>%s</option>".
			"<option value='%s'>%s</option>".
			"<option value='%s'>%s</option>".
			"<option value='%s'>%s</option>".
			"</select>".
			"<input id='searchtext' type='text' class='form-control input-sm space-right' name='%s' placeholder='%s' autofocus>".
			"<input type='hidden' name='table' value='%s'>".
			"<input type='hidden' name='mode' value='list'>".
			"<input type='hidden' name='%s' value='%%FIELDNAME%%'>". // the %FIELDNAME% is replaced during runtime in dbweb.js
			"<button class='btn btn-sm btn-primary' type='submit'><span class='glyphicon glyphicon-search'></span></button></div></form>\";</script>\n<div id='search-popover'></div>\n",

			build_get_params(),
			SEARCH_PARAM_OPTION,
			SEARCH_ANY, l10n('search.popover-option-any'),
			SEARCH_WORD, l10n('search.popover-option-word'),
			SEARCH_EXACT, l10n('search.popover-option-exact'),
			SEARCH_START, l10n('search.popover-option-start'),
			SEARCH_END, l10n('search.popover-option-end'),
			SEARCH_PARAM_QUERY,	l10n('search.popover-placeholder'),
			$_GET['table'],
			SEARCH_PARAM_FIELD
		);
	}

	//------------------------------------------------------------------------------------------
	function render_list() {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		global $APP;

		$table_name = $_GET['table'];
		if(!isset($TABLES[$table_name]))
			return proc_error(l10n('error.invalid-table', $table_name));

		$table = $TABLES[$table_name];

		if(!is_allowed($table, $_GET['mode']))
			return proc_error(l10n('error.not-allowed'));

		$fields = $table['fields'];

		echo "<h1>{$table['display_name']}</h1>\n";
		echo "<p>{$table['description']}</p>\n";

		$search = build_search_term($table, 'd');

		$db = db_connect();
		if($db === false)
			return proc_error(l10n('error.db-connect'));

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
			echo sprintf(
				"<p class='hidden-print'><a href='?%s' class='btn btn-default'><span class='glyphicon glyphicon-plus'></span> %s</a></p>\n",
				http_build_query(array('table' => $table_name, 'mode' => MODE_NEW)),
				l10n('list.button-new', $table['item_name'])
			);

		if($search !== null) {
			$info_l10n = 'search.infotext-any';
			if(isset($_GET[SEARCH_PARAM_OPTION])) switch($_GET[SEARCH_PARAM_OPTION]) {
				case SEARCH_END: $info_l10n = 'search.infotext-end'; break;
				case SEARCH_START: $info_l10n = 'search.infotext-start'; break;
				case SEARCH_EXACT: $info_l10n = 'search.infotext-exact'; break;
				case SEARCH_WORD: $info_l10n = 'search.infotext-word'; break;
			}
			echo sprintf(
				"<p class='text-info'>%s <a class='btn btn-default space-left hidden-print' href='?%s'><span class='glyphicon glyphicon-remove-circle'></span> %s</a></p>\n",
				l10n($info_l10n, html($fields[$_GET[SEARCH_PARAM_FIELD]]['label']), html($_GET[SEARCH_PARAM_QUERY])),
				http_build_query(array('table' => $table_name, 'mode' => MODE_LIST)),
				l10n('search.button-clear')
			);
		}

		if($num_records == 0) {
			echo "<p class='text-info'>". l10n('search.no-results') ."</p>";
		}
		else {
			$what = l10n($search !== null ? 'search.num-indicator' : 'list.num-indicator', $start_record, $end_record, $num_records);
			$total_ind = ($total_in_table > 0 ? "<span class='space-left'>(" . l10n('list.total-indicator', $total_in_table) . ")</span>" : '');
			echo "<p class='text-info'>{$what}{$total_ind}</p>";

			$query = build_query($table_name, $table, $offset, MODE_LIST, null, $params_arr);
			#debug_log($query);
			#debug_log($params_arr);
			$res = $db->prepare($query);
			if($res === false)
				return proc_error(l10n('error.db-prepare'), $db);
			if($res->execute($params_arr) === false)
				return proc_error(l10n('error.db-execute'), $res);

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
