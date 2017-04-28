<?
	if(!defined('QUERYPAGE_NO_INCLUDES')) {
		require_once 'chart.base.php';
		require_once 'chart.google.base.php';
		if(isset($APP['register_custom_chart_type_func']))
			$APP['register_custom_chart_type_func']();
		foreach(array_keys(QueryPage::$chart_types) as $type)
			require_once (isset(QueryPage::$chart_custom_file_locations[$type]) ? QueryPage::$chart_custom_file_locations[$type] : '') . "chart.$type.php";
	}

	//==========================================================================================
	class QueryPage extends dbWebGenPage {
	//==========================================================================================
		protected $sql, $view;
		protected $chart;
		protected $query_ui, $settings_ui, $viz_ui, $store_ui;
		protected $error_msg;
		protected $db;
		protected $stored_title, $stored_description; // set only from stored query
		protected $query_info = null;

		//--------------------------------------------------------------------------------------
		public static $chart_types = array(
			'table' => 'chart-type.table',
			'annotated_timeline' => 'chart-type.annotated-timeline',
			'bar' => 'chart-type.bar',
			'candlestick' => 'chart-type.candlestick',
			'geo' => 'chart-type.geo',
			'leaflet' => 'chart-type.leaflet',
			'network_visjs' => 'chart-type.network-visjs',
			'sankey' => 'chart-type.sankey',
			'timeline' => 'chart-type.timeline'
		);

		//--------------------------------------------------------------------------------------
		public static $chart_custom_file_locations = array(
			// this is managed through register_custom_chart_type()
		);

		//--------------------------------------------------------------------------------------
		public function __construct() {
		//--------------------------------------------------------------------------------------
			global $APP;
			parent::__construct();

			$this->chart = null;
			$this->error_msg = null;
			$this->db = db_connect();
			$this->stored_title = $this->stored_description = '';

			if(isset($APP['querypage_permissions_func']) && !$APP['querypage_permissions_func']()) {
				$this->sql = null;
				$this->error_msg = l10n('error.not-allowed');
			}

			if($this->is_stored_query() && !$this->fetch_stored_query()) {
				$this->sql = null;
				$this->error_msg = l10n('error.query-fetch');
			}
			else {
				$this->sql = trim($this->get_post(QUERYPAGE_FIELD_SQL, ''));
				$this->view = $this->get_urlparam(QUERY_PARAM_VIEW, QUERY_VIEW_FULL);

				// fill parameterized query info, if there are params
				$this->query_info = $this->parse_param_query();
			}
		}

		//--------------------------------------------------------------------------------------
		public static function register_custom_chart_type(
			$handle, // filename must be chart.$handle.php and class name must be dbWebGenChart_$handle
			$label, // to be displayed in the dropdown box
			$directory = '' // location of the file relative to the app directory
		) {
		//--------------------------------------------------------------------------------------
			self::$chart_types[$handle] = $label;
			if($directory != '' && mb_substr($directory, -1) != '/')
				$directory .= '/';
			self::$chart_custom_file_locations[$handle] = $directory;
		}

		//--------------------------------------------------------------------------------------
		public static function is_stored_query() {
		//--------------------------------------------------------------------------------------
			return isset($_GET[QUERY_PARAM_ID]);
		}

		//--------------------------------------------------------------------------------------
		public static function get_stored_query_id() {
		//--------------------------------------------------------------------------------------
			return $_GET[QUERY_PARAM_ID];
		}

		//--------------------------------------------------------------------------------------
		// returns false or query id token on success
		public static function store_query(&$error_msg) {
		//--------------------------------------------------------------------------------------
			global $APP;
			if(!isset($APP['querypage_stored_queries_table'])) {
				$error_msg = l10n('error.storedquery-config-table');
				return false;
			}

			$db = db_connect();
			if($db === false) {
				$error_msg = l10n('error.db-connect');
				return false;
			}

			if(!QueryPage::create_stored_queries_table($db)) {
				$error_msg = l10n('error.storedquery-create-table');
				return false;
			}

			if(isset($_POST['storedquery-replace-id']) && $_POST['storedquery-replace-id'] != '') {
				$id = $_POST['storedquery-replace-id'];
				$sql = sprintf('update %s set title = :title, description = :description, params_json = :params_json where id = :id', db_esc($APP['querypage_stored_queries_table']));
			}
			else {
				$id = get_random_token(STORED_QUERY_ID_LENGTH);
				$sql = sprintf('insert into %s (id, title, description, params_json) values(:id, :title, :description, :params_json)', db_esc($APP['querypage_stored_queries_table']));
			}

			$stmt = $db->prepare($sql);
			if($stmt === false) {
				$error_msg = 'Could not prepare query';
				return false;
			}

			$params = array(
				':id' => $id,
				':title' => safehash($_POST, 'storedquery-title'),
				':description' => safehash($_POST, 'storedquery-description'),
				':params_json' => json_encode(safehash($_POST, 'storedquery-json'))
			);

			if($stmt->execute($params) === false) {
				$error_msg = l10n('error.storedquery-exec-params', arr_str($params));
				return false;
			}

			return $id;
		}

		//--------------------------------------------------------------------------------------
		protected static function create_stored_queries_table(&$db) {
		//--------------------------------------------------------------------------------------
			global $APP;
			$create_sql = <<<SQL
				create table if not exists %s (
					id char(%s) primary key,
					title varchar(100),
					description varchar(1000),
					params_json text not null,
					create_time timestamp default current_timestamp
				)
SQL;
			return false !== $db->exec(sprintf($create_sql, db_esc($APP['querypage_stored_queries_table']), STORED_QUERY_ID_LENGTH));
		}

		//--------------------------------------------------------------------------------------
		public function view($new_val = null) {
		//--------------------------------------------------------------------------------------
			if($new_val !== null)
				$this->view = $new_val;

			return $this->view;
		}

		//--------------------------------------------------------------------------------------
		public function sql($new_val = null) {
		//--------------------------------------------------------------------------------------
			if($new_val !== null)
				$this->sql = $new_val;

			return $this->sql;
		}

		//--------------------------------------------------------------------------------------
		public function db() {
		//--------------------------------------------------------------------------------------
			return $this->db;
		}

		//--------------------------------------------------------------------------------------
		protected function fetch_stored_query() {
		//--------------------------------------------------------------------------------------
			global $APP;

			if($this->is_stored_query() && isset($APP['querypage_stored_queries_table'])) {
				// retrieve query details
				$stmt = $this->db->prepare(sprintf("select * from %s where id = ?", db_esc($APP['querypage_stored_queries_table'])));
				if($stmt === false)
					return false;

				if($stmt->execute(array($this->get_stored_query_id())) === false)
					return false;

				if($stored_query = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$_POST = $_POST + (array) json_decode($stored_query['params_json']);
					if(!isset($_GET[QUERY_PARAM_VIEW]))
						$_GET[QUERY_PARAM_VIEW] = QUERY_VIEW_RESULT;

					$this->stored_title = $stored_query['title'] !== null ? trim($stored_query['title']) : '';
					$this->stored_description = $stored_query['description'] !== null ? trim($stored_query['description']) : '';
					return true;
				}
			}

			return false;
		}

		//--------------------------------------------------------------------------------------
		public function render() {
		//--------------------------------------------------------------------------------------
			if($this->error_msg !== null) {
				// something went wrong in c'tor
				return proc_error($this->error_msg);
			}

			$this->store_ui = '';

			if($this->has_post_values()) {
				if(mb_substr(mb_strtolower($this->sql), 0, 6) !== 'select') {
					proc_error(l10n('error.storedquery-invalid-sql'));
					$this->sql = null;
				}

				$this->chart = dbWebGenChart::create($this->get_post(QUERYPAGE_FIELD_VISTYPE), $this);
				$this->chart->add_required_scripts();
				$this->build_stored_query_part();
			}

			$this->build_query_part();
			$this->build_settings_part();
			$this->build_visualization_part();
			$this->layout();

			return true;
		}

		//--------------------------------------------------------------------------------------
		protected function build_query_part() {
		//--------------------------------------------------------------------------------------
			$sql_html = html($this->sql);
			$sql_field = QUERYPAGE_FIELD_SQL;
			$sql_label = l10n('querypage.sql-label');
			$exec_label = l10n('querypage.exec-button');
			$sql_help = get_help_popup(l10n('querypage.sql-help-head'), l10n('querypage.sql-help-text'));

			$this->query_ui = <<<QUI
				<div class="form-group">
					<label class="control-label" for="$sql_field">$sql_label $sql_help</label>
					<textarea class="form-control vresize" id="$sql_field" name="$sql_field" rows="10">$sql_html</textarea>
				</div>
				<div class="form-group">
					<button class="btn btn-primary" name="submit" type="submit"><span class="glyphicon glyphicon-triangle-right"></span> $exec_label</button>
				</div>
QUI;
		}

		//--------------------------------------------------------------------------------------
		public function is_cache_enabled() {
		//--------------------------------------------------------------------------------------
			global $APP;
			return isset($APP['cache_dir']) && is_string($APP['cache_dir']);
		}

		//--------------------------------------------------------------------------------------
		protected function get_cache_settings_html() {
		//--------------------------------------------------------------------------------------
			if(!$this->is_cache_enabled())
				return '';

			$head = l10n('querypage.store-settings-heading');
			$cache_public = l10n('querypage.store-settings-cache-public');
			$cache_exp = l10n('querypage.store-settings-cache-expires');

			$s = <<<HTML
			<div class="form-group">
				<label class="control-label">$head</label>
				<div class="checkbox top-margin-zero">
					<label>{$this->render_checkbox($this->chart->ctrlname('public_access'), 'ON', false)}$cache_public</label>
				</div>
				<div class="table">
					<div class="tr">
						<div class='checkbox td'>
							<label>{$this->render_checkbox($this->chart->ctrlname('caching'), 'ON', false)}$cache_exp:</label>
						</div>
						<div class="td align-middle" style="width: 75px">
							{$this->render_textbox($this->chart->ctrlname('cache_ttl'), strval(DEFAULT_CACHE_TTL), 'input-sm ')}
						</div>
					</div>
					<script>
						$('#{$this->chart->ctrlname('cache_ttl')}').enabledBy($('#{$this->chart->ctrlname('caching')}'));
					</script>
				</div>
			</div>
HTML;
			return $s;
		}

		//--------------------------------------------------------------------------------------
		protected function build_settings_part() {
		//--------------------------------------------------------------------------------------
			$vistype_field = QUERYPAGE_FIELD_VISTYPE;
			$chart_types_sorted = l10n_values(QueryPage::$chart_types);
			asort($chart_types_sorted);
			$select = $this->render_select($vistype_field, /*default*/ 'table', $chart_types_sorted);
			$settings = '';
			foreach(array_keys($chart_types_sorted) as $type) {
				$chart = dbWebGenChart::create($type, $this);
				$settings .= sprintf('<div id="viz-option-%s">%s</div>', $type, $chart->settings_html());
			}

			$str_head = l10n('querypage.settings-head');
			$str_viz_label = l10n('querypage.settings-viz-label');

			$this->settings_ui = <<<STR
				<div class="panel panel-default">
					<div class="panel-heading">$str_head</div>
					<div class="panel-body">
						<div class="form-group">
							<label class="control-label" for="$vistype_field">$str_viz_label</label>
							$select
						</div>
						<div class='viz-options form-group'>
							$settings
						</div>
					</div>
				</div>
				<script>
					$('#{$vistype_field}').change(function() {
						$('.viz-options').children('div').hide();
						$("#viz-option-" + $('#{$vistype_field}').val()).show();
					});
				</script>
STR;
		}

		//--------------------------------------------------------------------------------------
		protected function build_stored_query_part() {
		//--------------------------------------------------------------------------------------
			if(!$this->chart || !$this->sql || $this->sql === '')
				return;

			global $APP;
			if(!isset($APP['querypage_stored_queries_table']))
				return;

			$link_url = '?' . http_build_query(array(
				'mode' => MODE_FUNC,
				'target' => GET_SHAREABLE_QUERY_LINK
			));

			// only store the SQL query, the visualization type, and the posted values relevant to the stored query type
			$post_data = array();
			foreach($_POST as $key => $value) {
				if($key == QUERYPAGE_FIELD_SQL
					|| $key == QUERYPAGE_FIELD_VISTYPE
					|| starts_with($this->chart->type(), $key))
				{
					$post_data[$key] = $value;
				}
			}

			$post_data = json_encode((object) $post_data);
			$title = json_encode(safehash($_POST, 'storedquery-title'));
			$description = json_encode(safehash($_POST, 'storedquery-description'));
			$save_label = $this->is_stored_query() ? l10n('querypage.store-button-new') : l10n('querypage.store-button-save');
			$replace_existing = $this->is_stored_query() ? '<button type="button" id="viz-share-replace" class="btn btn-default">'.l10n('querypage.store-button-update').'</button>' : '';
			$replace_id = json_encode($this->is_stored_query() ? $this->get_stored_query_id() : '');
			$js_cache_enabled = json_encode($this->is_cache_enabled());
			$str_descr_placeholder = l10n('querypage.store-description-placeholder');
			$str_title_placeholder = l10n('querypage.store-title-placeholder');
			$str_intro = l10n('querypage.store-intro');
			$str_store_success = json_encode(l10n('querypage.store-success'));
			$str_store_error = json_encode(l10n('querypage.store-error'));

			$share_popup = <<<SHARE
				<form>
				<p>$str_intro</p>
				<p><input class="form-control" placeholder="$str_title_placeholder" id="stored_query_title" /></p>
				<p><textarea class="form-control vresize" placeholder="$str_descr_placeholder" id="stored_query_description"></textarea></p>
				{$this->get_cache_settings_html()}
				<p class="nowrap">
					<button type="button" id="viz-share" class="btn btn-primary space-right">$save_label</button>
					$replace_existing
				</p>
				</form>
				<script>
					function viz_save_clicked(replace) {
						var button = $('#viz-share-popup');
						button.prop("disabled", true);

						var post_obj = {};
						post_obj['storedquery-title'] = $('#stored_query_title').val();
						post_obj['storedquery-description'] = $('#stored_query_description').val();

						if(replace)
							post_obj['storedquery-replace-id'] = $replace_id;

						var params_json = $post_data;
						if($js_cache_enabled) {
							params_json['{$this->chart->ctrlname('public_access')}'] = $('#{$this->chart->ctrlname('public_access')}').is(':checked') ? 'ON' : 'OFF';
							params_json['{$this->chart->ctrlname('caching')}'] = $('#{$this->chart->ctrlname('caching')}').is(':checked') ? 'ON' : 'OFF';
							params_json['{$this->chart->ctrlname('cache_ttl')}'] = $('#{$this->chart->ctrlname('cache_ttl')}').val();
						}
						post_obj['storedquery-json'] = params_json;

						$.post('{$link_url}', post_obj, function(url_query) {
							if(url_query && url_query.substring(0, 1) != '?') { // error
								$('.viz-share-url').html(url_query).show();
								return;
							}

							button.hide();
							var link = $('<a/>', {id: 'shared', target: '_blank', href: url_query});
							link.text(document.location.origin + document.location.pathname + url_query);
							$('.viz-share-url').html($str_store_success + '<br />').append(link).show();
							button.popover('hide');
						}).fail(function() {
							$('.viz-share-url').text($str_store_error).show();
						});
						return false;
					}
					$('#viz-share').click(function() { viz_save_clicked(false) });
					$('#viz-share-replace').click(function() { viz_save_clicked(true) });
				</script>
SHARE;

			$share_popup = json_encode($share_popup);
			$js_title = json_encode($this->stored_title);
			$js_desc = json_encode($this->stored_description);
			$str_btn_label = html(l10n('querypage.store-button-label'));

			$this->store_ui = <<<HTML
				&nbsp;
				<p>
					<a class='btn btn-default' id='viz-share-popup' href='javascript:void(0)' data-purpose='help' data-toggle='popover' data-placement='bottom'><span class='glyphicon glyphicon-link'></span> $str_btn_label <span class='caret' /></a>
					<div class='viz-share-url'></div>
				</p>
				<script>
					$('#viz-share-popup')
					.data('content', $share_popup)
					.on('shown.bs.popover', function() {
						$('#stored_query_title').val($js_title);
						$('#stored_query_description').val($js_desc);
						$('#viz-share-popup').prop("disabled", false);
					});
				</script>
HTML;
		}

		//--------------------------------------------------------------------------------------
		protected function render_lookup_field($table_name, $field_name, $param_name, $param_value) {
		//--------------------------------------------------------------------------------------
			global $TABLES;
			if(!isset($TABLES[$table_name]) || !isset($TABLES[$table_name]['fields'][$field_name])) {
				proc_error(l10n('error.storedquery-invalid-params'));
				return '';
			}

			$field = $TABLES[$table_name]['fields'][$field_name];
			$f = FieldFactory::create($table_name, $field_name);

			switch($field['type']) {
				case T_ENUM:
				case T_LOOKUP:
					if(!isset($_GET["p:$param_name"]))
						$_GET["p:$param_name"] = $param_value;
					$render_options = array(
						'form_method' => 'GET',
						'name_attr' => "p:$param_name",
						'id_attr' => $param_name,
						'null_option_allowed' => false,
						'force_cardinality_single' => true,
					);
					return $f->render_control($render_options);
					break;
			}

			return '';
		}

		//--------------------------------------------------------------------------------------
		public function can_cache() {
		//--------------------------------------------------------------------------------------
			return $this->is_cache_enabled() // cache setting enabled
				&& !isset($_GET['nocache']) // caching not turned off in URL param
				&& $this->is_stored_query() // must be a loaded stored query and
				&& count($this->query_info['params']) == 0 // must not be a parameterized query and
				&& $this->view != QUERY_VIEW_FULL // must not be in SQL editing mode
				;
		}

		//--------------------------------------------------------------------------------------
		protected function build_visualization_part() {
		//--------------------------------------------------------------------------------------
			$this->viz_ui = '';

			if($this->view === QUERY_VIEW_RESULT) {
				$size = 12;
				$css_class = 'result-full fill-height';

				// remove padding of main container to fill page
				/*$this->viz_ui .= <<<JS
					<script>
						$(document).ready(function() {
							$('#main-container').css('padding', '0');
						});
					</script>
JS;*/
				$nometa = isset($_GET['meta']) && $_GET['meta'] == 'none'; // some kind of hack to allow hiding title + description (for embedding)
				$css = isset($_GET[PLUGIN_PARAM_NAVBAR]) && $_GET[PLUGIN_PARAM_NAVBAR] == PLUGIN_NAVBAR_ON ? 'margin-top:0' : '';
				if($this->stored_title != '' && !$nometa)
					$this->viz_ui .= "<h3 style='$css'>" . html($this->stored_title) . "</h3>\n";
				if($this->stored_description != '' && !$nometa)
					$this->viz_ui .= '<p>' . html($this->stored_description) . "</p>\n";
				if(count($this->query_info['params']) > 0) {
					$param_fields = '';
					foreach($this->query_info['params'] as $param_name => $param_value) {
						$control_html = '';
						if(isset($this->query_info['lookups'][$param_name])) {
							$lookup_expr = $this->query_info['lookups'][$param_name];
							if(preg_match('/^table:(?P<table>[^,]+),field:(?P<field>.+)$/', $lookup_expr, $matches)) {
								$control_html = $this->render_lookup_field($matches['table'], $matches['field'], substr($param_name, 1), $param_value);
							}
						}
						$param_name = substr($param_name, 1);
						if($control_html == '') {
							$param_value = unquote($param_value);
							$control_html = "<input id='$param_name' type='text' class='input-sm form-control' name='p:$param_name' value='$param_value' />";
						}
						$param_fields .= <<<HTML
							<div class="form-group">
								<label for='$param_name'>$param_name:</label>
								$control_html &nbsp;&nbsp;
							</div>
HTML;
					}

					foreach($_GET as $p => $v) {
						if(substr($p, 0, 2) == 'p:')
							continue;
						$param_fields .= "<input type='hidden' name='$p' value='" . unquote($v) . "' />\n";
					}

					// render form for parameters:
					$str_refresh = l10n('querypage.param-query-refresh');
					$this->viz_ui .= <<<HTML
					<p><form class='form-inline' method='get'>
						{$param_fields} <button class='btn btn-default hidden-print' type='submit'><span class="glyphicon glyphicon-refresh"></span> $str_refresh</button>
					</form></p>
HTML;
				}
			}
			else {
				$size = 7;
				$css_class = 'result-column';
			}

			$this->viz_ui .= "<div class='col-sm-$size'>\n";
			if($this->view != QUERY_VIEW_RESULT)
				$this->viz_ui .= "  <label for='chart_div'>".l10n('querypage.results-head')."</label>{$this->store_ui}\n";
			$this->viz_ui .= "  <div class='$css_class' id='chart_div'></div>\n";
			$this->viz_ui .= "</div>\n";

			if($this->chart === null || !$this->sql)
				return;

			$js = false;
			$can_cache = $this->can_cache();
			// caching only when it's not a parameterized query
			if($can_cache)
				$js = $this->chart->cache_get_js();

			if($js === false) {
				$stmt = $this->db->prepare($this->query_info['sql']);
				if($stmt === false)
					return proc_error(l10n('error.db-prepare'), $this->db);

				if($stmt->execute($this->query_info['params']) === false)
					return proc_error(l10n('error.db-execute'), $this->db);

				$js = $this->chart->get_js($stmt);

				if($can_cache)
					$this->chart->cache_put_js($js);
			}
			else {
				$js .= "console.log('Query visualization loaded from cache.');";
			}

			$this->viz_ui .= "<script>\n{$js}\n</script>\n";
		}

		//--------------------------------------------------------------------------------------
		protected function parse_param_query() {
		//--------------------------------------------------------------------------------------
			$retval = array(
				'sql' => $this->sql,
				'params' => array(),
				'lookups' => array()
			);

			// extract parameters
			if(preg_match_all('/#{(?P<params>\\w+)\\|(?P<defvals>[^}|]*)(\\|(?P<lookups>[^}]*))?}/', $this->sql, $matches) > 0) {
				//debug_log($matches);

				for($i = 0; $i < count($matches['params']); $i++) {
					// prefer to take value from GET parameters e.g ...&p:xxx=blah
					$val = isset($_GET['p:' . $matches['params'][$i]]) ? $_GET['p:' . $matches['params'][$i]] : $matches['defvals'][$i];

					// replace this stuff in the sql statement
					$retval['sql'] = preg_replace(
						'/#{' . $matches['params'][$i] . '\\|[^}]*}/',
						':' . $matches['params'][$i],
						$retval['sql']);

					// set param for query
					$retval['params'][':' . $matches['params'][$i]] = $val;

					// set lookup info
					if(mb_strlen($lookup = trim($matches['lookups'][$i])) > 0)
						$retval['lookups'][':' . $matches['params'][$i]] = $lookup;
				}
			}

			//debug_log($retval);
			return $retval;
		}

		//--------------------------------------------------------------------------------------
		public function layout() {
		//--------------------------------------------------------------------------------------
			if($this->view === QUERY_VIEW_FULL) {
				echo <<<HTML
				<form class='' role='form' method='post' enctype='multipart/form-data'>
					<div class='col-sm-5'>
						<div>{$this->query_ui}</div>
						<div>{$this->settings_ui}</div>
					</div>
				</form>
HTML;
			}

			echo $this->viz_ui;

			if(isset($_GET['padding']) && $_GET['padding'] == 'none') {
				echo <<<JS
				<script>
					$(document).ready(function() {
						$('#main-container').addClass('no-padding');
					});
				</script>
JS;
			}
		}
	};
?>
