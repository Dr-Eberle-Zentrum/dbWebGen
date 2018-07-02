<?php
	// see engine.php for info which includes are available during MODE_FUNC calls.
	// everything needed on top of that needs to be included here.

	//------------------------------------------------------------------------------------------
	function process_func() {
	//------------------------------------------------------------------------------------------
		global $APP;

		if(!isset($_GET['target']))
			return proc_error('Invalid function call');

		switch($_GET['target']) {
			case LOOKUP_ASYNC:
				invoke_lookup_async();
				return;

			case LINKED_ITEM_HTML:
				process_linked_item_html();
				return;

			case GET_SHAREABLE_QUERY_LINK:
				get_sharable_query_link();
				return;

			case VISJS_NETWORK_CACHE_POSITIONS:
				visjs_network_cache_positions();
				return;

			case POSTGIS_TRANSFORM_WKT:
				postgis_transform_wkt_proxy();
				return;

			case SETUPWIZARD_SAVE_SETTINGS:
				setupwizard_save_settings();
				return;
		}

		if(!isset($APP['additional_callable_plugin_functions'])
			|| !in_array($_GET['target'], $APP['additional_callable_plugin_functions']))
		{
			return proc_error(l10n('error.invalid-function', $_GET['target']));
		}

		// call func.
		$_GET['target']();
	}

	//------------------------------------------------------------------------------------------
	function invoke_lookup_async() {
	//------------------------------------------------------------------------------------------
		require_once 'lookup_async.php';
		process_lookup_async();
	}

	//------------------------------------------------------------------------------------------
	function process_linked_item_html() {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		if(!isset($_GET['table']) || !isset($TABLES[$_GET['table']])
			|| !isset($_GET['field']) || !isset($TABLES[$_GET['table']]['fields'][$_GET['field']])
			|| !isset($_GET['self_id'])
			|| !isset($_GET['other_id'])
			|| !isset($_GET['label'])
			|| !isset($_GET['parent_form']))
		{
			return proc_error(l10n('error.invalid-params'));
		}
		$table = $TABLES[$_GET['table']];

		$can_edit = count($table['primary_key']['columns']) == 1 // currently not possible to do inline edit in table with composite key
			&& has_additional_editable_fields($table['fields'][$_GET['field']]['linkage']);

		echo get_linked_item_html($_GET['parent_form'], $table, $_GET['table'], $_GET['field'], $can_edit, $_GET['other_id'], $_GET['label'], $_GET['self_id']);
	}

	//------------------------------------------------------------------------------------------
	function get_sharable_query_link() {
	//------------------------------------------------------------------------------------------
		header('Content-Type: text/plain');
		define('QUERYPAGE_NO_INCLUDES', 1);
		require_once 'query.php';

		$stored_query = QueryPage::store_query($error_msg);
		if($stored_query === false) {
			echo 'Error: ' . $error_msg;
			return;
		}

		echo '?' . http_build_query(array(
			'mode' => MODE_QUERY,
			'id' => $stored_query
		));
	}

	//------------------------------------------------------------------------------------------
	function visjs_network_cache_positions() {
	//------------------------------------------------------------------------------------------
		require_once 'charts/chart.base.php';
		require_once 'charts/chart.network_visjs.php';
		dbWebGenChart_network_visjs::cache_positions_async();
	}

	//------------------------------------------------------------------------------------------
	function postgis_transform_wkt_proxy() {
	//------------------------------------------------------------------------------------------
		header('Content-Type: text/plain');
		if(postgis_transform_wkt($_REQUEST['geom_wkt'], $_REQUEST['source_srid'], $_REQUEST['target_srid'], $target_wkt))
			echo $target_wkt;
		else
			echo l10n('error.invalid-wkt-input');
	}

	//------------------------------------------------------------------------------------------
	function setupwizard_save_settings() {
	//------------------------------------------------------------------------------------------
		header('Content-Type: text/plain');
		require_once 'setup/wizard.php';
		echo SetupWizard::save_settings();
	}
?>
