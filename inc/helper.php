<?php
	require_once ENGINE_PATH_LOCAL . 'inc/db.php';

	//------------------------------------------------------------------------------------------
	function get_post($param, &$value, $default = null) {
	//------------------------------------------------------------------------------------------
		$value = $default;
		if(!isset($_POST[$param]))
			return false;
		$value = $_POST[$param];
		return true;
	}

	//------------------------------------------------------------------------------------------
	function get_request($param, &$value, $default = null) {
	//------------------------------------------------------------------------------------------
		$value = $default;
		if(!isset($_REQUEST[$param]))
			return false;
		$value = $_REQUEST[$param];
		return true;
	}

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
	function create_dir_if_not_exists($dir) {
	//------------------------------------------------------------------------------------------
		if(!@is_dir($dir)) {
			$old = umask(0);
			if(!@mkdir($dir, 0777, true)) {
				umask($old);
				$error = error_get_last();
				return proc_error($error['message']);
			}
			umask($old);
		}
		return true;
	}

	//------------------------------------------------------------------------------------------
	function starts_with($prefix, $text) {
	//------------------------------------------------------------------------------------------
		return mb_substr($text, 0, mb_strlen($prefix)) === $prefix;
	}

	//------------------------------------------------------------------------------------------
	function ends_with($postfix, $text) {
	//------------------------------------------------------------------------------------------
		return mb_substr($text, - mb_strlen($postfix)) === $postfix;
	}

	//------------------------------------------------------------------------------------------
	function get_help_popup($title, $text, $addl_attrs = array()) {
	//------------------------------------------------------------------------------------------
		return sprintf(
			"<a href='javascript:void(0)' title='%s' data-container='body' data-purpose='help' data-toggle='popover' data-placement='bottom' data-content='%s' %s><span class='glyphicon glyphicon-info-sign'></span></a>\n",
			$title,
			htmlentities($text, ENT_QUOTES),
			join(' ', $addl_attrs)
		);
	}

	//------------------------------------------------------------------------------------------
	function add_meta_include($template, $file, $cache_control, $echo = false) {
	//------------------------------------------------------------------------------------------
		global $META_INCLUDES;
		if($cache_control) {
			$t = @filemtime($file);
			if($t !== false)
				$file .= "?v=$t";
		}
		if($echo)
			echo sprintf($template, $file);
		else
			$META_INCLUDES[] = sprintf($template, $file);
	}

	//------------------------------------------------------------------------------------------
	function echo_javascript(
		$src,
		$cache_control = false // only set this to true for local relative paths
	) {
	//------------------------------------------------------------------------------------------
		add_meta_include("  <script type='text/javascript' src='%s'></script>\n", $src, $cache_control, true);
	}

	//------------------------------------------------------------------------------------------
	function echo_stylesheet(
		$src,
		$cache_control = false // only set this to true for local relative paths
	) {
	//------------------------------------------------------------------------------------------
		add_meta_include("  <link rel='stylesheet' href='%s' />\n", $src, $cache_control, true);
	}

	//------------------------------------------------------------------------------------------
	function add_javascript(
		$src,
		$cache_control = false // only set this to true for local relative paths
	) {
	//------------------------------------------------------------------------------------------
		add_meta_include("  <script type='text/javascript' src='%s'></script>", $src, $cache_control, false);
	}

	//------------------------------------------------------------------------------------------
	function add_stylesheet(
		$src,
		$cache_control = false // only set this to true for local relative paths
	) {
	//------------------------------------------------------------------------------------------
		add_meta_include("  <link rel='stylesheet' href='%s' />", $src, $cache_control, false);
	}

	//------------------------------------------------------------------------------------------
	function is_inline() {
	//------------------------------------------------------------------------------------------
		return isset($_GET['inline']);
	}

	//------------------------------------------------------------------------------------------
	function get_field_label(/*const*/ &$field, /*const*/ &$record) {
	//------------------------------------------------------------------------------------------
		if(!isset($field['conditional_form_label']))
			return $field['label'];

		$conrolling_field_value = $record[$field['conditional_form_label']['controlled_by']];
		if(!isset($field['conditional_form_label']['mapping'][$conrolling_field_value]))
			return $field['label'];

		return $field['conditional_form_label']['mapping'][$conrolling_field_value];
	}

	//------------------------------------------------------------------------------------------
	function get_app_lang() {
	//------------------------------------------------------------------------------------------
		return defined('DBWEBGEN_LANG') ? DBWEBGEN_LANG : 'en';
	}

	//------------------------------------------------------------------------------------------
	function is_table_hidden_from_menu(&$table, $menu_mode) {
	//------------------------------------------------------------------------------------------
		return isset($table['hide_from_menu']) && is_array($table['hide_from_menu'])
			&& in_array($menu_mode, $table['hide_from_menu']);
	}

	//------------------------------------------------------------------------------------------
	function is_popup() {
	//------------------------------------------------------------------------------------------
		return isset($_GET['popup']);
	}

	//------------------------------------------------------------------------------------------
	function is_password_change_allowed() { // default: true
	//------------------------------------------------------------------------------------------
		global $LOGIN;
		if(isset($LOGIN['users_table']) && is_array($LOGIN['users_table']))
			return false;
		return !isset($LOGIN['allow_change_password']) || $LOGIN['allow_change_password'] === true;
	}

	//------------------------------------------------------------------------------------------
	function first($a) {
	//------------------------------------------------------------------------------------------
		return $a[0];
	}

	//------------------------------------------------------------------------------------------
	function get_mincolwidth_max() {
	//------------------------------------------------------------------------------------------
		global $APP;
		return isset($APP['list_mincolwidth_max']) ? $APP['list_mincolwidth_max'] : 300;
	}

	//------------------------------------------------------------------------------------------
	function get_mincolwidth_pxperchar() {
	//------------------------------------------------------------------------------------------
		global $APP;
		return isset($APP['list_mincolwidth_pxperchar']) ? $APP['list_mincolwidth_pxperchar'] : 6;
	}

	//------------------------------------------------------------------------------------------
	function __debug_val_display($v, $indent = 0, $quote_string = false) {
	//------------------------------------------------------------------------------------------
		switch(gettype($v)) {
			case 'NULL':
				return "<span class='pre-null'>NULL</span>";
			case 'object':
				$v = (array) $v;
			case 'array':
				return trim(__arr_str($v, $indent + 3));
			case 'boolean':
				return "<span class='pre-bool'>" . ($v ? 'TRUE' : 'FALSE') . "</span>";
			case 'string':
				$sret = "<span class='pre-string'>" . html($v) . "</span>";
				if($quote_string)
					$sret = "'" . $sret . "'";
				return $sret;
			case 'integer': case 'double':
				return "<span class='pre-number'>$v</span>";
			default:
				return "<span class='pre-any'>" . html($v) . "</span>";
		}	
	}

	//------------------------------------------------------------------------------------------
	function __arr_str(&$a, $indent = 0) {
	//------------------------------------------------------------------------------------------
		$s = str_repeat(' ', $indent) . "[\n";
		if(is_array($a)) foreach($a as $k => $v) {
			$s .= str_repeat(' ', $indent + 3) . "'<span class='pre-key'>{$k}</span>' => ";
			$s .= __debug_val_display($v, $indent, true) . ",\n";
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

		$term['params'][] = $search_query;
		$pre_term_op = '';
		$post_term_op = '';

		switch($search_option) {
			case SEARCH_EXACT:
				$op = '=';
				break;

			case SEARCH_ANY:
				$op = 'like';
				$pre_term_op = '%';
				$post_term_op = '%';
				break;

			case SEARCH_START:
				$op = 'like';
				$post_term_op = '%';
				break;

			case SEARCH_END:
				$op = 'like';
				$pre_term_op = '%';
				break;

			case SEARCH_WORD:
				$op = '~*';
				$pre_term_op = '\m';
				$post_term_op = '\M';
				break;

			default:
				return null;
		}

		$string_trafo = '%s';
		if(isset($APP['search_string_transformation']) && $APP['search_string_transformation'] != '') {
			$string_trafo = $APP['search_string_transformation'];
			if(substr_count($string_trafo, '%s') !== 1)
				proc_error(l10n('search.transformation-invalid'));
		}

		// for sprintf() we need to escape any %
		$pre_term_op = str_replace('%', '%%', $pre_term_op);
		$post_term_op = str_replace('%', '%%', $post_term_op);
		if($pre_term_op != '') $pre_term_op = "'$pre_term_op', ";
		if($post_term_op != '') $post_term_op = ", '$post_term_op'";
		$query_trafo_without_ops = str_replace('%s', '?', $string_trafo);
		$query_trafo = 'concat(' . $pre_term_op . $query_trafo_without_ops . $post_term_op . ')';
		#debug_log($query_trafo);

		if($APP['search_lookup_resolve']
			&& $fields[$search_field]['type'] == T_LOOKUP
			&& $fields[$search_field]['lookup']['cardinality'] == CARDINALITY_SINGLE
			&& !(isset($_GET['raw_fk']) && $_GET['raw_fk'] == 1)
		) {
			$lookup = $fields[$search_field]['lookup'];

			$field_trafo = str_replace('%s', db_cast_text('%s'), $string_trafo);

			$term['sql'] = sprintf("$field_trafo %s $query_trafo or (select $field_trafo from %s other where other.%s = %s.%s) %s $query_trafo",
				db_esc($search_field), $op,
				resolve_display_expression($lookup['display'], 'other'),
				db_esc($lookup['table']), db_esc($lookup['field']), db_esc($table_alias), db_esc($search_field), $op);

			$term['params'][]= $term['params'][count($term['params'])-1];
		}
		else if($APP['search_lookup_resolve'] && $fields[$search_field]['type'] == T_LOOKUP && $fields[$search_field]['lookup']['cardinality'] == CARDINALITY_MULTIPLE) {
			$field = $fields[$search_field];

			$field_trafo = str_replace('%s', db_array_to_string_array_agg('%s', ' '), $string_trafo);

			$term['sql'] = sprintf("(select $field_trafo FROM %s other, %s link WHERE link.%s = %s.%s AND other.%s = link.%s) %s $query_trafo",
				resolve_display_expression($field['lookup']['display'], 'other'),
				db_esc($field['lookup']['table']), db_esc($field['linkage']['table']),
				db_esc($field['linkage']['fk_self']), $table_alias, db_esc($table['primary_key']['columns'][0]),
				db_esc($field['lookup']['field']), db_esc($field['linkage']['fk_other']), $op);

			// for SEARCH_ANY & SEARCH_WORD queries (~ contains) we also want to look whether the provided query value matches any of the multiple foreign keys (not only the lookup values), expecting those key values to be integers (but also works with others)
			if($search_option === SEARCH_ANY || $search_option === SEARCH_WORD) {
				/*$field_trafo = sprintf("array_agg(%s)", $string_trafo);
				$or_term = sprintf("(select $field_trafo from %s link where link.%s = %s.%s) @> array[$query_trafo_without_ops]",
					db_esc($field['linkage']['fk_other']), db_esc($field['linkage']['table']),
					db_esc($field['linkage']['fk_self']), $table_alias, db_esc($table['primary_key']['columns'][0]));*/

				$or_term = sprintf("($query_trafo_without_ops) in (select $string_trafo from %s link where link.%s = %s.%s)",
					db_esc($field['linkage']['fk_other']), db_esc($field['linkage']['table']),
					db_esc($field['linkage']['fk_self']), $table_alias, db_esc($table['primary_key']['columns'][0]));

				$term['sql'] = "({$term['sql']} OR {$or_term})";
				$term['params'][] = $search_query;
			}
		}
		else {
			if($fields[$search_field]['type'] == T_POSTGIS_GEOM)
				$field_trafo = str_replace('%s', 'ST_AsText(%s)', $string_trafo);
			else
				$field_trafo = str_replace('%s', db_cast_text('%s'), $string_trafo);

			$term['sql'] = sprintf("$field_trafo %s $query_trafo", db_esc($search_field), $op);
		}

		#debug_log('term: ', $term);
		return $term;
	}

	//------------------------------------------------------------------------------------------
	function debug_log(/* variable argument list */) {
	//------------------------------------------------------------------------------------------
		$msg = '';

		foreach(func_get_args() as $arg) {
			if(is_object($arg))
				$arg = (array) $arg;
			$msg .= is_array($arg) ? arr_str($arg) : __debug_val_display($arg);
		}

		$_SESSION['msg'][] = "<div class='alert alert-info'>$msg</div>";
	}

	//------------------------------------------------------------------------------------------
	function is_allowed(&$table, $mode) {
	//------------------------------------------------------------------------------------------
		 return !isset($table['actions']) || in_array($mode, $table['actions']);
	}

	//------------------------------------------------------------------------------------------
	function get_primary_key_column($table_name) {
	//------------------------------------------------------------------------------------------
		 global $TABLES;
		 return $TABLES[$table_name]['primary_key']['columns'][0];
	}

	//------------------------------------------------------------------------------------------
	function get_default($def) {
	//------------------------------------------------------------------------------------------
		if(!isset($_SESSION['user_id']))
			return $def;

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
	function unquote($text) {
	//------------------------------------------------------------------------------------------
		return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
	}

	//------------------------------------------------------------------------------------------
	function has_additional_editable_fields($linkage) {
	//------------------------------------------------------------------------------------------
		global $TABLES;

		if(isset($TABLES[$linkage['table']])) {
			// check whether the linkage table has additional fields
			$linkage_table = $TABLES[$linkage['table']];
			foreach($linkage_table['fields'] as $lf_name => $lf_info) {
				if($lf_name != $linkage['fk_self']
					&& $lf_name != $linkage['fk_other']
					&& is_field_editable($lf_info))
				{
					return true;
				}
			}
		}

		return false;
	}

	//------------------------------------------------------------------------------------------
	function bootstrap_css() {
	//------------------------------------------------------------------------------------------
		global $APP;
		return isset($APP) && isset($APP['bootstrap_css']) ? $APP['bootstrap_css'] :
			(ENGINE_PATH_HTTP . 'node_modules/bootstrap/dist/css/bootstrap.min.css');
	}

	//------------------------------------------------------------------------------------------
	function page_icon() {
	//------------------------------------------------------------------------------------------
		global $APP;
		return isset($APP['page_icon']) ? $APP['page_icon'] : '';
	}

	//------------------------------------------------------------------------------------------
	function is_lookup_allow_edit(&$lookup_settings) {
	//------------------------------------------------------------------------------------------
		global $APP;
		$default = isset($APP['lookup_allow_edit_default'])	? $APP['lookup_allow_edit_default'] : false;
		return isset($lookup_settings['allow_edit']) ? $lookup_settings['allow_edit'] : $default;
	}

	//------------------------------------------------------------------------------------------
	function format_lookup_item_label(
		$raw_label, &$lookup_settings, $id_value,
	 	$format = 'html', // { html, unquote, plain }
		$raw_label_span = false // only used when format = html
	) {
	//------------------------------------------------------------------------------------------
		global $TABLES;

		if(isset($lookup_settings['label_display_expr_only']) && $lookup_settings['label_display_expr_only'] === true) {
			if($format == 'html' && $raw_label_span === true)
				return sprintf('<span class="display-label">%s</span>', html($raw_label));
			return $format == 'html' ? html($raw_label) : ($format == 'unquote' ? unquote($raw_label) : $raw_label);
		}

		$lookup_table = $lookup_settings['table'];
		$lookup_id_field = $lookup_settings['field'];

		if(isset($TABLES[$lookup_table])
			&& isset($TABLES[$lookup_table]['fields'][$lookup_id_field])
			&& isset($TABLES[$lookup_table]['fields'][$lookup_id_field]['label']))
		{
			$lookup_id_field = $TABLES[$lookup_table]['fields'][$lookup_id_field]['label'];
		}

		if($format == 'html' && $raw_label_span)
			return sprintf('<span class="display-label">%s</span> (%s = %s)', html($raw_label), html($lookup_id_field), html($id_value));

		$label = sprintf('%s (%s = %s)', $raw_label, $lookup_id_field, $id_value);
		return $format == 'html' ? html($label) : ($format == 'unquote' ? unquote($label) : $label);
	}

	//------------------------------------------------------------------------------------------
	// finds out whether there are missing values for required fields in records created using
	// the "Create New" button. If so, this is indicated in the form.
	function are_linkage_details_missing(
		$linkage_has_already_existed,
		$form_id,
		$linkage_field_name,
		&$linkage_field,
		$linked_record_fk
	) {
	//------------------------------------------------------------------------------------------
		global $TABLES;		
		if($linkage_has_already_existed)
			return false;

		if(!isset($TABLES[$linkage_field['linkage']['table']])) // there's no way of knowing
			return false;
		
		$linked_table = &$TABLES[$linkage_field['linkage']['table']];
		$linked_table_pks = $linked_table['primary_key']['columns'];
		$required_fields = array();
		$linkage_defaults = isset($linkage_field['linkage']['defaults']) ? $linkage_field['linkage']['defaults'] : array();
		foreach($linked_table['fields'] as $field_name => &$field) {
			// primary keys always required but not relevant here
			if(in_array($field_name, $linked_table_pks))
				continue;
			
			// ignore optional fields
			if(!is_field_required($field))
				continue;
			
			// igore fields that have default
			if(isset($linkage_defaults[$field_name]))
				continue;

			// here we have a required field with no defaults -> neeeds to have value in inline form data
			$required_fields[] = $field_name;
		}
		
		// if there are no required fields - go away
		if(count($required_fields) === 0)
			return false;

		// see whether we have values for the required field(s)
		$linkage_details = get_inline_linkage_details($form_id, $linkage_field_name, $linked_record_fk);
		if($linkage_details === false)
			return true;

		/* linkage details now looks like this:
			[
				'details' => [
					'user_id' => '18',
					'location_id' => '1',
					'rating' => '3',
					'review' => '',
				],
				'columns' => [
					'0' => 'user_id',
					'1' => 'location_id',
					'2' => 'rating',
					'3' => 'review',
				],
				'params' => [
					'0' => '18',
					'1' => '1',
					'2' => '3',
					'3' => NULL,
				],
			] 
		*/
		foreach($required_fields as $field_name) {
			if(!isset($linkage_details['details'][$field_name]))
				return true;
			// if value is empty then only fail if param is NULL
			if($linkage_details['details'][$field_name] === '') {
				// find column index
				$col_index = array_search(field_name, $linkage_details['columns']);
				
				// not found? -> missing
				if($col_index === false)
					return true;

				// find param value at this index
				if($linkage_details['params'][$col_index] === null)
					return true;

				// here we can let go...
			}
		}
		
		return false;
	}

	//------------------------------------------------------------------------------------------
	// CAREFUL: this function can be called via MODE_FUNC, e.g. it's called so from dbweb.js
	//------------------------------------------------------------------------------------------
	function get_linked_item_html(
		/*string*/  $parent_form,
		/*array*/	&$table,
		/*string*/ 	$table_name,
		/*string*/ 	$field_name,
		/*bool*/	$can_edit,
		/*string*/	$fk_other_value,
		/*string*/	$fk_other_text,
		/*string*/	$fk_self_value,
		/*bool*/	$has_already_existed = false
	) {
	//------------------------------------------------------------------------------------------
		global $TABLES;

		if(!isset($table['fields'][$field_name]))
			return proc_error('Invalid field');
		$field = $table['fields'][$field_name];
		$linkage = &$field['linkage'];
		$lookup = &$field['lookup'];

		$detail_data_span = '';
		if($can_edit) {
			$inline_url = '?' . http_build_query(array(
				'inline' => $table_name,
				'parent_form' => $parent_form,
				'lookup_field' => $field_name,
				'table' => $linkage['table'],
				'mode' => MODE_EDIT,
				"{$linkage['fk_other']}" => $fk_other_value,
				"{$linkage['fk_self']}" => $fk_self_value
			));
			$popup_title = unquote($TABLES[$linkage['table']]['item_name'] . ' Details');

			$details_missing = are_linkage_details_missing($has_already_existed, $parent_form, $field_name, $field, $fk_other_value);

			$detail_data_span = sprintf(
				"<a role='button' onclick='linkage_details_click(this)' class='%s space-left multiple-select-details-edit' data-id-other='%s' data-details-title='%s' data-details-url='%s' id='%s_details_%s' title='%s'><span class='glyphicon glyphicon-th-list'></span></a>",
				$details_missing ? 'linkage-details-missing' : '',
				$fk_other_value, $popup_title, $inline_url, $field_name, $fk_other_value, l10n('lookup-field.linkage-details-edit-tooltip')
			);
		}

		$lookup_table_item_name = isset($TABLES[$lookup['table']]) 
			? $TABLES[$lookup['table']]['item_name'] 
			: l10n('lookup-field.record');

		$edit_other_span = '';
		if(is_lookup_allow_edit($lookup)
			&& isset($TABLES[$lookup['table']])
			&& is_allowed($TABLES[$lookup['table']], MODE_EDIT)
		) {
			// offer to edit the linked item
			$edit_url = '?' . http_build_query(array(
				'table' => $lookup['table'],
				'mode' => MODE_EDIT,
				"{$lookup['field']}" => $fk_other_value,
				'parent_form' => $parent_form,
				'special' => SPECIAL_EDIT_LINKED_RECORD,
				'source_table' => $table_name,
				'source_field' => $field_name,
				PLUGIN_PARAM_NAVBAR => PLUGIN_NAVBAR_OFF
			));

			$edit_other_span = sprintf(
				"<a role='button' onclick='lookup_edit_other(this)' class='space-left multiple-select-lookup-edit' data-id-other='%s' data-edit-url='%s' id='%s_lookup_edit_%s' title='%s'><span class='glyphicon glyphicon-edit'></span></a>",
				$fk_other_value,
				$edit_url,
				$field_name,
				$fk_other_value,
				l10n('lookup-field.linkage-assoc-edit-tooltip', unquote($lookup_table_item_name))
			);
		}

		$item_div_format = <<<STR
			<div class='table multiple-select-item' data-field='%s' data-id-other='%s' data-newly-added='%s'>
				<div class='tr'>
					<div class='td nowrap'><a role='button' onclick='remove_linked_item(this)' data-field='%s' data-id='%s' data-role='remove' title='%s'><span class='glyphicon glyphicon-remove-circle'></span></a>%s%s</div>
					<div class='td full-width multiple-select-text'>%s</div>
				</div>
			</div>
STR;
		return sprintf($item_div_format,
			unquote($field_name),
			unquote($fk_other_value),
			$has_already_existed ? 0 : 1,
			unquote($field_name),
			unquote($fk_other_value),
			l10n('lookup-field.linkage-assoc-delete-tooltip', unquote($lookup_table_item_name)),
			$detail_data_span,
			$edit_other_span,
			format_lookup_item_label($fk_other_text, $field['lookup'], $fk_other_value, 'html', true)
		);
	}

	//------------------------------------------------------------------------------------------
	function html_linked_records(&$field, &$linked_records, $max_chars = 0) {
	//------------------------------------------------------------------------------------------
		$html = '';
		$cunt = count($linked_records);
		if($cunt == 0)
			return $html;

		$raw_len = 0;
		$clip_opened = false;
		$render_func = $_GET['mode'] == MODE_VIEW && isset($field['linkage']['render_func']) ? $field['linkage']['render_func'] : null;
		$params = array();

		for($i = 0; $i < $cunt; $i++) {
			$rec = $linked_records[$i];
			$raw_len += mb_strlen(strval($rec['raw_val'])) + (!$render_func ? mb_strlen(MULTIPLE_RECORDS_SEPARATOR) : 0);

			if(!$clip_opened // clipping hasn't started yet
				&& $max_chars > 0 // there must be a restriction
				&& $i > 0 // at least one must have been shown already
				&& $raw_len > $max_chars // the length exceeds the configured maximum
				&& $i < $cunt - 1 // we're not at the last record
			) {
				$html .= sprintf(
					"<a role='button' title='%s' class='clipped_text'>[%s more]</a><span class='clipped_text'>",
					l10n('lookup-field.linked-records-clipped-tooltip'),
					$cunt - $i
				);
				$clip_opened = true;
			}

			$item_html = get_lookup_display_html($rec['class'], $rec['title'], $rec['href'], $rec['html_val']);
			if($render_func)
				$item_html = $render_func($field, $item_html, $i, $cunt, $params);
			$html .= $item_html;

			if(!$render_func && $i < $cunt - 1)
				$html .= MULTIPLE_RECORDS_SEPARATOR;
		}

		if($clip_opened)
			$html .= '</span>';

		return $html;
	}

	//------------------------------------------------------------------------------------------
	function html($text, $max_chars = 0, $expandable = false, $html_linebreaks = false, $highlighter = null) {
	//------------------------------------------------------------------------------------------
		if($text === null)
			return '';
		if($highlighter === null)
			$highlighter = new NothingHighlighter;

		$text = strval($text);
		$len = mb_strlen($text);

		// PHP lower than v5.4 do not have ENT_HTML401
		if(!defined('ENT_HTML401'))
			define('ENT_HTML401', 0);

		if($max_chars > 0 && $len > $max_chars) {
			$ret = $highlighter->highlight(htmlspecialchars(mb_substr($text, 0, $max_chars), ENT_COMPAT | ENT_HTML401));

			if($expandable)
				$ret .= sprintf(
					"<a role='button' title='%s' class='clipped_text'>[...]</a><span class='clipped_text'>%s</span>",
					l10n('helper.html-text-clipped'),
					$highlighter->highlight(htmlspecialchars(mb_substr($text, $max_chars), ENT_COMPAT | ENT_HTML401))
				);
			else
				$ret .= '...';
		}

		else
			$ret = $highlighter->highlight(htmlspecialchars($text, ENT_COMPAT | ENT_HTML401));

		if($html_linebreaks)
			$ret = nl2br($ret);

		global $APP;
		if(isset($APP['preprocess_html_func']))
			$ret = $APP['preprocess_html_func']($ret);

		return $ret;
	}

	//------------------------------------------------------------------------------------------
	function get_lookup_display_html($class, $title, $href, $label_html) {
	//------------------------------------------------------------------------------------------
		if($href === null) {
			if($class == '' && $title == '')
				return $label_html;

			return "<span class='$class' title='$title'>$label_html</span>";
		}

		return "<a class='$class' title='$title' href=\"?$href\">$label_html</a>";
	}

	//------------------------------------------------------------------------------------------
	class NothingHighlighter {
	//------------------------------------------------------------------------------------------
		public function highlight($html, $css_class = 'hl') {
			return $html;
		}
	}

	//------------------------------------------------------------------------------------------
	// highlights all occurrences of an ascii $term_to_highlight in some
	// $html string that may contain all sorts of weird characters
	class SearchResultHighlighter {
	//------------------------------------------------------------------------------------------
		public $term_to_highlight;
		protected $term_len;
		protected static $transliterator = null;

		//------------------------------------------------------------------------------------------
		public function __construct(
			$term_to_highlight, 	// must be an already transliterated search term (ASCII only)
			$transliterator_rules 	// rules passed to Transliterator::createFromRules
		) {
			$this->term_to_highlight = $term_to_highlight;
			$this->term_len = mb_strlen($this->term_to_highlight);
			if(self::$transliterator === null) // Transliterator only available PHP >= 5.4.0, PECL intl >= 2.0.0
				self::$transliterator = class_exists('Transliterator') ? Transliterator::createFromRules($transliterator_rules) : null;
		}

		//------------------------------------------------------------------------------------------
		public function highlight(
			$html,				// the HTML in which to highlight all occurrences of $this->term_to_highlight
			$css_class = 'hl'	// the CSS class used to highlight occurrences
		) {
			if(self::$transliterator === null)
				return $html;
			$result = '';
			$source_len = mb_strlen($html);
			$matched_term_chars = 0;
			$source_match_startpos = 0;
			$source_match_len = 0;
			for($i = 0; $i < $source_len; $i++) {
				$c = mb_substr($html, $i, 1);
				$c_trans = mb_strtolower(self::$transliterator->transliterate($c));
				$c_trans_len = mb_strlen($c_trans); // note: single transliterated chars can be more than one char, e.g. transliterate('手') yields 'shou'
				if($c_trans_len <= $this->term_len - $matched_term_chars && $c_trans === mb_substr($this->term_to_highlight, $matched_term_chars, $c_trans_len))	{
					if($matched_term_chars == 0)
						$source_match_startpos = $i;
					$matched_term_chars += $c_trans_len;
					$source_match_len++;
					if($matched_term_chars == $this->term_len) {
						$result .= sprintf('<span class="%s">%s</span>', $css_class, mb_substr($html, $source_match_startpos, $source_match_len));
						$matched_term_chars = $source_match_len = 0;
					}
				}
				else {
					$result .= $source_match_len > 0 ? mb_substr($html, $source_match_startpos, $source_match_len + 1) : $c;
					$matched_term_chars = $source_match_startpos = $source_match_len = 0;
				}
			}
			return $result;
		}
	}

	//------------------------------------------------------------------------------------------
	function console_log($data) {
	//------------------------------------------------------------------------------------------
		echo '<script>console.log('.json_encode($data).')</script>';
	}

	//------------------------------------------------------------------------------------------
	function is_lookup_hyperlink_suppressed(&$field) {
	//------------------------------------------------------------------------------------------
		return isset($field['lookup']['no_link']) && $field['lookup']['no_link'] === true;
	}

	//------------------------------------------------------------------------------------------
	function prepare_field_display_val(&$table, &$record, &$field, $col, $val, $highlighter = null) {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		global $APP;

		if($highlighter === null)
			$highlighter = new NothingHighlighter;

		if($field['type'] == T_ENUM && $val !== NULL) {
			$val = $highlighter->highlight(html($field['values'][$val]));
		}
		else if($field['type'] == T_NUMBER && $val !== NULL) {
			if(isset($field['number_format'])) {
				$fmt = $field['number_format'];
				$plus = isset($fmt['plus_sign']) ? $fmt['plus_sign'] : '+';
				$minus = isset($fmt['minus_sign']) ? $fmt['minus_sign'] : '-';				
				$disp = '';
				for($i = 0; $i < strlen($fmt['template']); $i++) {
					$flag = $fmt['template'][$i];
					switch($flag) {
						case 'a': $disp .= $fmt['after']; break;
						case 'b': $disp .= $fmt['before']; break;
						case 'c': $disp .= $fmt['currency']; break;
						case 'm': $disp .= $val < 0 ? $minus : ''; break;
						case 's': $disp .= $val == 0 ? '' : ($val > 0 ? $plus : $minus); break;
						case 'n': $disp .= number_format(abs($val), $fmt['decimals'], $fmt['dec_point'], $fmt['thousands_sep']); break;
						case ' ': $disp .= $flag; break;
						case 'e'; $disp .= '&nbsp;';
					}
				}
				if($val >= 0 && isset($fmt['color_pos']))
					$val = sprintf('<span style="color:%s">%s</span>', $fmt['color_pos'], $disp);
				else if($val < 0 && isset($fmt['color_neg']))
					$val = sprintf('<span style="color:%s">%s</span>', $fmt['color_neg'], $disp);
				else
					$val = $disp;		
			}
			else if(isset($field['max_decimals'])) {
				if(is_array($field['max_decimals'])) {
					if(isset($field['max_decimals'][$_GET['mode']]))
						$val = (float) number_format($val, $field['max_decimals'][$_GET['mode']], '.', '');
					else
						$val = (float) $val;
				}
				else
					$val = (float) number_format($val, $field['max_decimals'], '.', '');
			}
			else
				$val = (float) $val;
			$val = $highlighter->highlight((string) $val); // TODO CHECK WHETHER THIS SCREWS UP ANYTHING
		}
		else if($field['type'] == T_PASSWORD) {
			$val = '●●●●●';
		}
		else if($field['type'] == T_UPLOAD) {
			$val = "<a href='". get_file_url($val, $field) ."'>" . $highlighter->highlight(html($val)). '</a>';
		}
		else if($field['type'] == T_LOOKUP && $field['lookup']['cardinality'] == CARDINALITY_SINGLE) {
			$href = isset($TABLES[$field['lookup']['table']]) && in_array(MODE_VIEW, $TABLES[$field['lookup']['table']]['actions']) && !is_lookup_hyperlink_suppressed($field) ?
			http_build_query(array(
				'table' => $field['lookup']['table'],
				'mode' => MODE_VIEW,
				$field['lookup']['field'] => isset($record[db_postfix_fieldname($col, FK_FIELD_POSTFIX, false)]) ? $record[db_postfix_fieldname($col, FK_FIELD_POSTFIX, false)] : $val
			)) : null;

			$html_val = html($val);
			$title = ''; $class = '';
			if($html_val == '') {
				$title = l10n('lookup-field.linked-record-no-display-value');
				$html_val = html($record[db_postfix_fieldname($col, FK_FIELD_POSTFIX, false)]);
				$class = 'dotted';
			}

			$val = get_lookup_display_html($class, $title, $href, $highlighter->highlight($html_val));
		}
		else if($field['type'] == T_LOOKUP && $field['lookup']['cardinality'] == CARDINALITY_MULTIPLE) {
			if($val !== null && trim($val) != '') {
				#debug_log($val);
				//postgre 9.4+ >> [must go hand in hand with build_query function]
				/*
				$id_display_map = json_decode($val);
				*/
				//<< postgre 9.4+

				// postgre 9.2
				$temp_arr = json_decode($val);
				$id_display_map = array();
				for($i=0; $i<count($temp_arr[0]); $i++)
					$id_display_map[$temp_arr[0][$i]] = $temp_arr[1][$i];
				//<< postgre 9.2

				if(!isset($field['lookup']['sort']) || $field['lookup']['sort'] == true)
					asort($id_display_map);

				$linked_records = array();
				foreach($id_display_map as $id_val => $display_val) {
					$linked_rec = array();
					$linked_rec['raw_val'] = $display_val;
					$params = array('mode' => MODE_VIEW);

					if($field['lookup']['cardinality'] == CARDINALITY_MULTIPLE
						&& isset($TABLES[$field['linkage']['table']])
						&& is_allowed($TABLES[$field['linkage']['table']], MODE_VIEW))
					{
						// display link to n:m table view (typically MODE_VIEW will be allowed when there are additional attributes to the n:m table, otherwise not)
						$params['table'] = $field['linkage']['table'];
						$params[$field['linkage']['fk_other']] = $id_val;
						$params[$field['linkage']['fk_self']] = $record[$table['primary_key']['columns'][0]];
					}
					else {
						// display link to linked item
						$params['table'] = $field['lookup']['table'];
						$params[$TABLES[$field['lookup']['table']]['primary_key']['columns'][0]] = $id_val;
					}

					$linked_rec['href'] = isset($TABLES[$params['table']]) && in_array(MODE_VIEW, $TABLES[$params['table']]['actions']) && !is_lookup_hyperlink_suppressed($field) ? http_build_query($params) : null;
					$linked_rec['html_val'] = $highlighter->highlight(html($display_val));
					$linked_rec['title'] = '';
					$linked_rec['class'] = '';
					if($linked_rec['html_val'] == '') {
						$linked_rec['title'] = l10n('lookup-field.linked-record-no-display-value');
						$linked_rec['html_val'] = $highlighter->highlight(html($linked_rec['raw_val'] = $id_val));
						$linked_rec['class'] = 'dotted';
					}

					$linked_records[] = $linked_rec;
				}
				$val = html_linked_records($field, $linked_records, $_GET['mode'] == MODE_VIEW ? 0 : $APP['max_text_len']);
			}
			else
				$val = '';
		}
		else if($field['type'] == T_BOOLEAN) {
			require_once 'fields/field.base.php';
			require_once 'fields/field.boolean.php';
			$field_obj = FieldFactory::create($_GET['table'], $col);
			if(($field_obj->has_custom_values() && $field_obj->get_custom_value(BooleanField::ON) == $val)
			 	|| (!$field_obj->has_custom_values() && $val == true))
				$val = $field_obj->get_display_value(BooleanField::ON);
			else
				$val = $field_obj->get_display_value(BooleanField::OFF);
		}
		else {
			if($_GET['mode'] == MODE_VIEW) {
				$val = html($val, 0, false, true, $highlighter);

				if(trim($val) !== '' && $field['type'] == T_POSTGIS_GEOM) {
					// append map button to show location
					require_once 'fields/field.base.php';
					require_once 'fields/field.postgisgeom.php';
					$field_obj = FieldFactory::create($_GET['table'], $col);
					if($field_obj->has_map_picker()) {
						$val = $field_obj->render_map_picker_button(
							$val, 'map-marker', l10n('geom-field.map-picker-view-tooltip'), true, 'btn-link', $record[$col]
						);
					}
				}
			}
			else // MODE_LIST or other: limit chars to display
				$val = html($val, $APP['max_text_len'], true, false, $highlighter);
		}

		return $val;
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

	/*
	// NOTE THIS FUNCTION DOES NOT WORK WITH ALL SERVER CONFIGURATIONS. DO NOT USE IT
	// ------------------------------------------------------------------------------------------
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
	}*/

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
	function get_session_var($what, &$obj, $default = null) {
	//------------------------------------------------------------------------------------------
		$obj = $default;
		if(!isset($_SESSION[$what]))
			return false;
		$obj = $_SESSION[$what];
		return true;
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
	function is_own_user_record(/* bool */ $check_if_password_change_allowed = true) {
	//------------------------------------------------------------------------------------------
		global $LOGIN;

		if(isset($LOGIN['users_table']) && is_array($LOGIN['users_table']))
			return false; // only for DB-based auth

		return is_logged_in()
			&& (!$check_if_password_change_allowed || ($check_if_password_change_allowed && is_password_change_allowed()))
			&& $_GET['table'] === $LOGIN['users_table']
			&& isset($_GET[$LOGIN['primary_key']])
			&& $_GET[$LOGIN['primary_key']] == strval($_SESSION['user_id'])
			&& !(isset($LOGIN['guest_user']) && $LOGIN['guest_user'] == $_SESSION['user_data'][$LOGIN['username_field']]);
	}

	//------------------------------------------------------------------------------------------
	function proc_error($txt, $db_obj = null /* can be db or statement obj */, $clear_msg_buffer = false) {
	//------------------------------------------------------------------------------------------
		if($clear_msg_buffer)
			$_SESSION['msg'] = array();

		$msg = '<div class="alert alert-danger"><b>'. l10n('info-box.error-head') .'</b>: ' . $txt;
		if(is_object($db_obj)) {
			$e = $db_obj->errorInfo();
			$msg .= "<ul>\n<li>". str_replace("\n", '</li><li>', html($e[2])) . "</li>\n";
			$msg .= "<li>". l10n('info-box.sql-codes') .": SQLSTATE {$e[0]}, Driver {$e[1]}</li>\n";
			$msg .= "</ul>\n";
		}
		$msg .= "</div>\n";
		$_SESSION['msg'][] = $msg;
		return false;
	}

	//------------------------------------------------------------------------------------------
	function proc_success($txt) {
	//------------------------------------------------------------------------------------------
		$_SESSION['msg'][] = '<div class="alert alert-success"><b>'.l10n('info-box.success-head').'</b>: ' . html($txt) . "</div>\n";
		return true;
	}

	//------------------------------------------------------------------------------------------
	function proc_info($txt, $db = null) {
	//------------------------------------------------------------------------------------------
		$msg = '<div class="alert alert-info"><b>'.l10n('info-box.info-head').'</b>: ' . $txt;
		if(is_object($db)) {
			$e = $db->errorInfo();
			$msg .= "<ul>\n<li>". str_replace("\n", '</li><li>', html($e[2])) . "</li>\n";
			$msg .= "<li>". l10n('info-box.sql-codes') .": SQLSTATE {$e[0]}, Driver {$e[1]}</li>\n";
			$msg .= "</ul>\n";
		}
		$msg .= "</div>\n";
		$_SESSION['msg'][] = $msg;
		return true;
	}

	//------------------------------------------------------------------------------------------
	function get_inline_fieldname_fk_other() {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		return $TABLES[$_GET['inline']]['fields'][$_GET['lookup_field']]['linkage']['fk_other'];
	}

	//------------------------------------------------------------------------------------------
	// returns assoc array with key=>value pairs; false otherwise
	function get_inline_linkage_details($form_id, $field_name, $linked_id,
		/*in assoc array*/ $fk_self_hash = null) {
	//------------------------------------------------------------------------------------------
		$linked_id = strval($linked_id);

		if(!isset($_SESSION[$form_id])
			|| !isset($_SESSION[$form_id][$field_name])
			|| !isset($_SESSION[$form_id][$field_name][$linked_id]))
		{
			return false;
		}

		if($fk_self_hash !== null && is_array($fk_self_hash)) {
			$fk_self_name = first(array_keys($fk_self_hash));
			// set fk_self in case it was not there when the inline details were edited (ie new parent record)
			for($i=0; $i<count($_SESSION[$form_id][$field_name][$linked_id]['columns']); $i++) {
				if($_SESSION[$form_id][$field_name][$linked_id]['columns'][$i] === $fk_self_name)
					$_SESSION[$form_id][$field_name][$linked_id]['params'][$i] = $fk_self_hash[$fk_self_name];
			}
		}

		return $_SESSION[$form_id][$field_name][$linked_id];
	}

	//------------------------------------------------------------------------------------------
	function set_inline_linkage_details($form_id, $field_name, $linked_id,
		/*const*/ &$arr_details, /*const*/ &$arr_columns, /*const*/ &$arr_params)
	{
	//------------------------------------------------------------------------------------------
		$linked_id = strval($linked_id);

		if(!isset($_SESSION[$form_id]))
			$_SESSION[$form_id] = array();

		if(!isset($_SESSION[$form_id][$field_name]))
			$_SESSION[$form_id][$field_name] = array();

		if(!isset($_SESSION[$form_id][$field_name]))
			$_SESSION[$form_id][$field_name][$linked_id] = array();

		$_SESSION[$form_id][$field_name][$linked_id]['details'] = array_merge($arr_details, array());
		$_SESSION[$form_id][$field_name][$linked_id]['columns'] = array_merge($arr_columns, array());;
		$_SESSION[$form_id][$field_name][$linked_id]['params'] = array_merge($arr_params, array());
		#$_SESSION[$form_id][$field_name][$linked_id]['empty_key_indexes'] = array_merge($empty_key_indexes, array());
	}

	//------------------------------------------------------------------------------------------
	function get_file_url($file_name, $field_info) {
	//------------------------------------------------------------------------------------------
		$url = $field_info['location'] . '/' . rawurlencode($file_name);
		return str_replace('//', '/', $url);
	}

	//------------------------------------------------------------------------------------------
	function get_field_sort_expression(&$field) {
	//------------------------------------------------------------------------------------------
		if(!isset($field['sort_expr']))
			return '%s';

		return $field['sort_expr'];
	}

	//------------------------------------------------------------------------------------------
	function is_field_hidden_in_list(&$field) {
	//------------------------------------------------------------------------------------------
		return isset($field['list_hide']) && $field['list_hide'] === true;
	}

	//------------------------------------------------------------------------------------------
	function is_field_editable(&$field) {
	//------------------------------------------------------------------------------------------
		return !isset($field['editable']) || $field['editable'] === true;
	}

	//------------------------------------------------------------------------------------------
	function is_field_trim(&$field) {
	//------------------------------------------------------------------------------------------
		return !isset($field['trim']) || $field['trim'] === true;
	}

	//------------------------------------------------------------------------------------------
	function is_field_reset(&$field) {
	//------------------------------------------------------------------------------------------
		return isset($field['reset']) && $field['reset'] === true;
	}

	//------------------------------------------------------------------------------------------
	function is_allowed_create_new(&$field) {
	// default is true
	//------------------------------------------------------------------------------------------
		if(isset($field['allow_create']) && $field['allow_create'] === false)
			return false;

		if(isset($field['allow-create']) && $field['allow-create'] === false) // legacy
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
			(isset($_POST["{$field_name}__null__"]) && $_POST["{$field_name}__null__"] === 'true')

			||

			(isset($_POST[$field_name]) && $_POST[$field_name] == '' &&
			 ($field_info['type'] == T_ENUM || ($field_info['type'] == T_LOOKUP && $field_info['lookup']['cardinality'] == CARDINALITY_SINGLE))
			 && !is_field_required($field_info)); // NULL_OPTION
	}

	//------------------------------------------------------------------------------------------
	function get_the_primary_key_value_from_url($table, $default_if_missing) {
	//------------------------------------------------------------------------------------------
		$pk_name = $table['primary_key']['columns'][0];

		if(!isset($_GET[$pk_name]))
			return $default_if_missing;

		return $_GET[$pk_name];
	}

	//------------------------------------------------------------------------------------------
	function get_primary_key_values_from_url($table) {
	//------------------------------------------------------------------------------------------
		$pk_vals = array();

		foreach($table['primary_key']['columns'] as $pk) {
			if(!isset($_GET[$pk]))
				return proc_error(l10n('error.missing-pk-value', $pk));

			$pk_vals[$pk] = $_GET[$pk];
		}

		return $pk_vals;
	}

	//------------------------------------------------------------------------------------------
	function get_file_upload_error_msg($code) {
	//------------------------------------------------------------------------------------------
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE: $message = l10n('error.upload-err-ini-size'); break;
            case UPLOAD_ERR_FORM_SIZE: $message = l10n('error.upload-err-form-size'); break;
            case UPLOAD_ERR_PARTIAL: $message = l10n('error.upload-err-partial'); break;
            case UPLOAD_ERR_NO_FILE: $message = l10n('error.upload-err-no-file'); break;
            case UPLOAD_ERR_NO_TMP_DIR: $message = l10n('error.upload-err-no-tmp-dir'); break;
            case UPLOAD_ERR_CANT_WRITE: $message = l10n('error.upload-err-cant-write'); break;
            case UPLOAD_ERR_EXTENSION: $message = l10n('error.upload-err-extension'); break;
            default: $message = l10n('error.upload-err-unknown'); break;
        }
        return $message;
    }

	//------------------------------------------------------------------------------------------
	function resolve_display_expression($display, $table_qualifier) {
	//------------------------------------------------------------------------------------------
		if($table_qualifier != '')
			$table_qualifier = db_esc($table_qualifier) . '.';
		else
			proc_error(l10n('error.query-withouth-qualifier')); // some cases absolutely require table qualifiers

		if(!is_array($display)) // simple field name string
			return $table_qualifier . db_esc($display);

		// here we have something like
		// 'display' => [ 'columns' => ['firstname', 'lastname'], 'expression' => "concat_ws(' ', %1 %2)" ]
		if(!isset($display['columns']) || !is_array($display['columns']) || !isset($display['expression']))
			proc_error(l10n('error.invalid-display-expression'));

		$expr = $display['expression'];
		$num_cols = count($display['columns']);
		for($i = $num_cols; $i >= 1; $i--) // need to start from highest number, because %1 would also replace %10
			$expr = str_replace("%$i", $table_qualifier . db_esc($display['columns'][$i - 1]), $expr);
		return $expr;
	}

	//------------------------------------------------------------------------------------------
	function get_sort_field_name(&$table, $field_name) {
	//------------------------------------------------------------------------------------------
		if(isset($table['sort_lookup_raw'])
			&& $table['sort_lookup_raw'] == true
			&& $table['fields'][$field_name]['type'] == T_LOOKUP
			&& $table['fields'][$field_name]['lookup']['cardinality'] == CARDINALITY_SINGLE
		) {
			return db_postfix_fieldname($field_name, FK_FIELD_POSTFIX, true);
		}

		return db_esc($field_name);
	}

	//------------------------------------------------------------------------------------------
	function build_query($table_name, $table, $offset, $mode, $more_params, &$out_params) {
	//------------------------------------------------------------------------------------------
		global $APP;
		global $TABLES;

		$out_params = array();

		if($mode != MODE_EDIT && $mode != MODE_LIST && $mode != MODE_VIEW)
			return proc_error(l10n('error.invalid-mode', $mode));

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
					$cols .= sprintf('(SELECT %s FROM %s k WHERE %s = t.%s) %s, t.%s %s',
						resolve_display_expression($field['lookup']['display'], 'k'),
						db_esc($field['lookup']['table']), db_esc($field['lookup']['field']),
						db_esc($field_name), db_esc($field_name), db_esc($field_name),
						db_postfix_fieldname($field_name, FK_FIELD_POSTFIX, true));
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
					// postgres 9.4+ >> (must go hand in hand with prepare_field_display_val function)
					/*$cols .= sprintf(
						"(SELECT json_object_agg(%s,%s) " .
						'FROM %s other, %s link WHERE link.%s = t.%s AND other.%s = link.%s) %s',
						db_esc($TABLES[$field['lookup']['table']]['primary_key']['columns'][0]), resolve_display_expression($field['lookup']['display'], 'other'),
						db_esc($field['lookup']['table']), db_esc($field['linkage']['table']),
						db_esc($field['linkage']['fk_self']), db_esc($table['primary_key']['columns'][0]),
						db_esc($field['lookup']['field']), db_esc($field['linkage']['fk_other']), db_esc($field_name));
					*/
					//<< postgres 9.4+

					// postgres 9.2 >>
					$cols .= sprintf(
						"(SELECT CONCAT('[', " . db_array_to_json_array_agg('%s') . ", ',', " . db_array_to_json_array_agg('%s') . ", ']') " .
						'FROM %s other, %s link WHERE link.%s = t.%s AND other.%s = link.%s) %s',
						db_esc($field['lookup']['field'], 'other'), resolve_display_expression($field['lookup']['display'], 'other'),
						db_esc($field['lookup']['table']), db_esc($field['linkage']['table']),
						db_esc($field['linkage']['fk_self']), db_esc($table['primary_key']['columns'][0]),
						db_esc($field['lookup']['field']), db_esc($field['linkage']['fk_other']), db_esc($field_name));
					//<< postgres 9.2
				}
				else { // MODE_EDIT
					$cols .= sprintf("(SELECT " . db_array_to_json_array_agg('link.%s') .
							 " FROM %s link WHERE link.%s = ?) %s",
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
				$pk_fields .= sprintf(', %s %s', db_esc($pk), db_postfix_fieldname($pk, FK_FIELD_POSTFIX, true));

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
				$order_by[] = sprintf(get_field_sort_expression($table['fields'][$_GET['sort']]), get_sort_field_name($table, $_GET['sort'])) . " $dir";
			}

			if(count($order_by) == 0 && isset($table['sort']) && is_array($table['sort']) && count($table['sort']) > 0 ) {
				foreach($table['sort'] as $field_name => $dir) {
					$order_by[] = sprintf(get_field_sort_expression($table['fields'][$field_name]), get_sort_field_name($table, $field_name)) . " $dir";

					// fake the $_GET for later
					$_GET['sort'] = $field_name;
					$_GET['dir'] = $dir;
				}
			}

			if(count($order_by) > 0)
				$q .= ' ORDER BY ' . implode(', ', $order_by);

			$q .= " LIMIT ". $APP['page_size'] . " OFFSET $offset";
		}

		#debug_log($q);
		return $q;
	}

	//------------------------------------------------------------------------------------------
	function get_random_token($length) {
	//------------------------------------------------------------------------------------------
		$alphabet = 'ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
		$max = strlen($alphabet) - 1;
		$token = '';
		for($i = 0; $i < $length; $i++)
			$token .= $alphabet[mt_rand(0, $max)];
		return $token;
	}

	//------------------------------------------------------------------------------------------
	function enable_delete(&$buf = null) {
	//------------------------------------------------------------------------------------------
		$box_head = l10n('delete.confirm-head');
		$box_msg = l10n('delete.confirm-msg');
		$button_cancel = l10n('delete.button-cancel');
		$button_delete = l10n('delete.button-delete');
		$echo = <<<END
			<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<!--<div class="modal-header">
						</div>-->
						<div class="modal-body">
							<h4>$box_head</h4>
							$box_msg
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">$button_cancel</button>
							<a class="btn btn-danger btn-ok">$button_delete</a>
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
		if($buf !== null)
			$buf = $echo;
		else
			echo $echo;
	}


	//------------------------------------------------------------------------------------------
	class FieldGrouper {
	//------------------------------------------------------------------------------------------
		private
			$groups = null,
			$table = null,
			$cur_field_name = null,
			$cur_group = null,
			$is_start = null,
			$is_last = null,
			$width = null,
			$cumul_width = null,
			$is_required = false;

		public function __construct(&$table) {
			$this->table = $table;
			$this->groups = isset($table['field_groups']) && is_array($table['field_groups']) ? $table['field_groups'] : array();
		}

		public function set_current_field($field_name) {
			$this->cur_field_name = $field_name;
			$this->is_start = false;
			$this->is_last = false;
			$this->cur_group = null;
			$this->width = null;
			$this->is_required = false;

			foreach($this->groups as $group) {
				if($this->cur_group !== null || !isset($group['fields'][$this->cur_field_name]))
				 	continue;

				$this->cur_group = $group;
				$i = 0;
				foreach($group['fields'] as $fn => $width) {
					if($fn == $this->cur_field_name) {
						if($i == 0) {
							$this->is_start = true;
							$this->cur_group = $group;
							$this->cumul_width = 0;
						}

						if($i == count(array_keys($group['fields'])) - 1)
							$this->is_last = true;

						$this->width = $width;
						$this->cumul_width += $width;
					}

					if($this->is_required === false && is_field_required($this->table['fields'][$fn]))
						$this->is_required = true;

					$i++;
				}
			}
		}

		public function is_new_row(&$row_no) {
			$row_no = intval($this->cumul_width / 12) + 1;

			return $this->cumul_width === 0
				|| ($this->cumul_width - $this->width) % 12 === 0;
		}

		public function is_group_start() {
			return $this->is_start;
		}

		public function is_last_in_group() {
			return $this->is_in_group() && $this->is_last;
		}

		public function is_in_group() {
			return null !== $this->cur_group;
		}

		public function get_help_text() {
			return $this->is_in_group() ? $this->cur_group['help'] : '';
		}

		public function get_width() {
			return $this->width;
		}

		public function get_label() {
			return $this->cur_group['label'];
		}

		public function is_required() {
			return $this->is_required;
		}

		public function get_label_tooltip() {
			return isset($this->cur_group['label_tooltip']) ? $this->cur_group['label_tooltip'] : '';
		}

		public function has_space_top() {
			return $this->cumul_width > 12;
		}

		public function debug() {
			debug_log(sprintf(
				"%s: w=%s %s %s %s %s",
				$this->cur_field_name,
				$this->get_width(),
				$this->has_space_top() ? 'space-top' : '',
				$this->is_in_group() ? 'in group' : '',
				$this->is_start ? 'start' : '',
				$this->is_last ? 'end' : ''
			));
		}
	}

	//------------------------------------------------------------------------------------------
	class FormTabs {
	//------------------------------------------------------------------------------------------
		private $form_tabs;
		private $tab_index;
		private $last_tab;
		private $sorted_fields;

		public function __construct(&$table) {
			$this->form_tabs = isset($table['form_tabs'])
				&& is_array($table['form_tabs'])
				&& count($table['form_tabs']['tabs']) > 0
				&& (!isset($table['form_tabs']['restrict_to']) || in_array($_GET['mode'], $table['form_tabs']['restrict_to']))
				? $table['form_tabs']['tabs'] : false;

			if($this->form_tabs !== false) {
				// sort fields accordingly
				$this->sorted_fields = array();
				for($t = 0; $t < count($this->form_tabs); $t++) {
					foreach($table['fields'] as $f_name => &$f_settings) {
						if($t == 0 && !isset($f_settings['tab'])) // first tab is default if missing setting in field
							$f_settings['tab'] = $this->form_tabs[0]['id'];

						if($f_settings['tab'] == $this->form_tabs[$t]['id'])
							$this->sorted_fields[$f_name] = $f_settings;
					}
				}
				$table['fields'] = $this->sorted_fields;
				$this->last_tab = $this->form_tabs[0]['id'];
			}
			$this->tab_index = -1;
		}

		public function begin() {
			if($this->form_tabs === false)
				return '';
			$html = '';
			$html .= "<ul class='nav nav-tabs'>";
			$active_done = false;
			foreach($this->form_tabs as &$tab) {
				$tab['anchor'] = 'tab_' . unquote(strtolower(preg_replace('/\s+/', '', $tab['id'])));
				$html .= sprintf("<li class='%s'><a data-toggle='tab' href='#%s'>%s</a></li>\n",
					$active_done? '' : 'active', $tab['anchor'], $tab['label']);
				$active_done = true;
			}
			$html .= "</ul>\n<div class='tab-content'>\n";
			return $html;
		}

		public function new_tab_if_needed($field_name) {
			if($this->form_tabs === false)
				return '';
			$html = '';
			if($this->tab_index == -1) {
				// first tab
				$this->tab_index++;
				$html .= sprintf("<div id='%s' class='tab-pane fade in active'>\n", $this->form_tabs[$this->tab_index]['anchor']);
			}
			else if($this->last_tab != $this->sorted_fields[$field_name]['tab']) {
				// start new tab
				$this->tab_index++;
				$this->last_tab = $this->form_tabs[$this->tab_index]['id'];
				$html .= sprintf("</div>\n<div id='%s' class='tab-pane fade'>\n", $this->form_tabs[$this->tab_index]['anchor']);
			}
			if($html != ''
				&& isset($this->form_tabs[$this->tab_index]['intro'])
				&& trim($this->form_tabs[$this->tab_index]['intro']) != '')
			{
				$html .= '<p>' . $this->form_tabs[$this->tab_index]['intro'] . '</p>';
			}
			return $html;
		}

		public function close() {
			if($this->form_tabs === false)
				return '';
			return "</div>\n</div>\n";
		}
	}

	//------------------------------------------------------------------------------------------
	function postgis_transform_wkt($geom_wkt, $source_srid, $target_srid, &$result) {
	//------------------------------------------------------------------------------------------
		if($source_srid == $target_srid) {
			$result = $geom_wkt;
			return true;
		}
		$sql = "select st_astext(st_transform(st_geomfromtext(?,?),?))";
		$params = array($geom_wkt, $source_srid, $target_srid);
		return db_get_single_val($sql, $params, $result);
	}

	//------------------------------------------------------------------------------------------
	function check_pseudo_login_public_queryviz() {
	//------------------------------------------------------------------------------------------
		// remove login requirement for specific queries
		global $LOGIN;
		if(isset($LOGIN)
			&& isset($LOGIN['users_table'])
			&& !is_logged_in()
			&& isset($_GET['mode'])
			&& $_GET['mode'] == MODE_QUERY
			/*&& isset($_GET[PLUGIN_PARAM_NAVBAR])
			&& $_GET[PLUGIN_PARAM_NAVBAR] == PLUGIN_NAVBAR_OFF*/
			&& isset($_GET['id'])
			&& strlen(trim($_GET['id'])) > 0)
		{
			global $APP;
			if(!db_get_single_val(
				sprintf('select params_json from %s where id = ?', db_esc($APP['querypage_stored_queries_table'])),
				array($_GET['id']),
				$params_json)
			)
				return;
			$params = json_decode($params_json, true);
			if(!$params)
				return;
			$vistype = $params[QUERYPAGE_FIELD_VISTYPE];
			if(isset($params["$vistype-public_access"]) && $params["$vistype-public_access"] == 'ON') {
				$LOGIN = array();
				$_SESSION['pseudo_login'] = true;
			}
		}
	}
?>
