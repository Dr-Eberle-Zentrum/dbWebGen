<?
	if(!defined('QUERYPAGE_NO_INCLUDES')) {
		require_once 'chart.base.php';
		require_once 'chart.google.base.php';
		foreach(array_keys(QueryPage::$chart_types) as $type)
			require_once "chart.$type.php";
	}
	
	define('QUERYPAGE_FIELD_SQL', 'sql');
	define('QUERYPAGE_FIELD_VISTYPE', 'vistype');
	
	//==========================================================================================
	class QueryPage extends dbWebGenPage {
	//==========================================================================================
		protected $sql, $view;
		protected $chart;
		protected $query_ui, $settings_ui, $viz_ui, $store_ui;
		protected $error_msg;
		
		public static $chart_types = array(
			'table' => 'Table', 
			'bar' => 'Bar Chart',
			'candlestick' => 'Candlestick Chart',
			'geo' => 'Geo Chart',
			'leaflet' => 'Leaflet Map',
			'sankey' => 'Sankey Chart',
			'timeline' => 'Timeline'
		);
	
		//--------------------------------------------------------------------------------------
		public function __construct() {
		//--------------------------------------------------------------------------------------
			global $APP;
			parent::__construct();
			
			$this->chart = null;
			$this->error_msg = null;
			
			if(isset($APP['querypage_permissions_func']) && !$APP['querypage_permissions_func']()) {
				$this->sql = null;
				$this->error_msg = 'You are not allowed to view this.';
			}
			
			if($this->is_stored_query() && !$this->fetch_stored_query()) {
				$this->sql = null;
				$this->error_msg = 'Could not retrieve the stored query';
			}
			else {
				$this->sql = trim($this->get_post(QUERYPAGE_FIELD_SQL, ''));
				$this->view = $this->get_urlparam(QUERY_PARAM_VIEW, QUERY_VIEW_FULL);
			}
		}
		
		//--------------------------------------------------------------------------------------
		public static function is_stored_query() {
		//--------------------------------------------------------------------------------------
			return isset($_GET['id']); // && mb_strlen($_GET['id']) == STORED_QUERY_ID_LENGTH;
		}
		
		//--------------------------------------------------------------------------------------
		// returns false or query id token on success
		public static function store_query(&$error_msg) {
		//--------------------------------------------------------------------------------------
			global $APP;
			if(!isset($APP['querypage_stored_queries_table']))
				return false;
			
			$db = db_connect();
			if($db === false) {
				$error_msg = 'Could not connect to DB';
				return false;
			}
			
			if(!QueryPage::create_stored_queries_table($db)) {
				$error_msg = 'Could not create table';
				return false;
			}
			
			$sql = "insert into {$APP['querypage_stored_queries_table']} (id, title, description, params_json) values(?, ?, ?, ?)";
			$stmt = $db->prepare($sql);
			if($stmt === false) {
				$error_msg = 'Could not prepare query';
				return false;
			}
			
			$params = array(
				/*id*/ get_random_token(STORED_QUERY_ID_LENGTH),
				/*title*/ safehash($_POST, 'storedquery-title'),
				/*description*/ safehash($_POST, 'storedquery-description'),
				/*params_json*/ json_encode(safehash($_POST, 'storedquery-json'))
			);
			
			if($stmt->execute($params) === false) {
				$error_msg = 'Failed to execute query with ' . arr_str($params);
				return false;
			}
			
			return $params[0];
		}
		
		//--------------------------------------------------------------------------------------
		protected static function create_stored_queries_table(&$db) {
		//--------------------------------------------------------------------------------------
			global $APP;
			
			$id_len = STORED_QUERY_ID_LENGTH;
			
			$create_sql = <<<SQL
				create table if not exists {$APP['querypage_stored_queries_table']} (
					id char({$id_len}) primary key,					
					title varchar(100),
					description varchar(1000),
					params_json text not null,
					create_time timestamp default current_timestamp
				)
SQL;
			return false !== $db->exec($create_sql);
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
		protected function fetch_stored_query() {
		//--------------------------------------------------------------------------------------
			global $APP;		
			
			if(isset($APP['querypage_stored_queries_table']) && isset($_GET['id'])) {
				// retrieve query details
				$db = db_connect();
				if($db === false)
					return false;
				
				$stmt = $db->prepare("select * from {$APP['querypage_stored_queries_table']} where id = ?");
				if($stmt === false)
					return false;
				
				if($stmt->execute(array($_GET['id'])) === false)
					return false;
				
				if($stored_query = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$_POST = $_POST + (array) json_decode($stored_query['params_json']);
					$_GET[QUERY_PARAM_VIEW] = QUERY_VIEW_RESULT;
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
					proc_error('Invalid SQL query. Only SELECT statements are allowed!');
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
			
			$this->query_ui = <<<QUI
				<div class="form-group">
					<label class="control-label" for="{$sql_field}">SQL Query</label>
					<textarea class="form-control" id="{$sql_field}" name="{$sql_field}" rows="10">{$sql_html}</textarea>
				</div>
				<div class="form-group">
					<button class="btn btn-primary" type="submit">Execute</button>
				</div>
QUI;
		}
		
		//--------------------------------------------------------------------------------------
		protected function build_settings_part() {
		//--------------------------------------------------------------------------------------
			$vistype_field = QUERYPAGE_FIELD_VISTYPE;
			
			$select = $this->render_select($vistype_field, first(array_keys(QueryPage::$chart_types)), QueryPage::$chart_types);
			$settings = '';
			foreach(QueryPage::$chart_types as $type => $name)
				$settings .= "<div id='viz-option-$type'>" . dbWebGenChart::create($type, $this)->settings_html() . "</div>\n";
			
			$this->settings_ui = <<<STR
				<div class="panel panel-default">
					<div class="panel-heading">
						Result Visualization Settings
					</div>
					<div class="panel-body">						
						<div class="form-group">
							<label class="control-label" for="{$vistype_field}">Visualization</label>
							{$select}
						</div>						
						<div class='viz-options form-group'>
							{$settings}							
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
			
			$this->store_ui = <<<HTML
				&nbsp;
				<a id='viz-share' role='button' href='javascript:void(0)'><span title='Share this live query visualization' class='glyphicon glyphicon-link'></span> Get Shareable Link</a>
				<div class='viz-share-url'></div>
				<script>
					$('#viz-share').click(function() {
						var button = $(this);
						$.post('{$link_url}', {
							'storedquery-title' : $title,
							'storedquery-description' : $description,
							'storedquery-json': $post_data
						}, function(url_query) {
							if(url_query && url_query.substring(0, 1) != '?') { // error
								$('.viz-share-url').html(url_query).show(); 
								return;
							}
							else {
								button.hide();
								var link = $('<a/>', {id: 'shared', target: '_blank', href: url_query});
								link.text(document.location.origin + document.location.pathname + url_query);
								$('.viz-share-url').text('').show().append(link);						
							}									
						}).fail(function() {
							$('.viz-share-url').text('Error: Could not generate a shareable URL.').show();
						});
					});
				</script>
HTML;
		}
		
		//--------------------------------------------------------------------------------------
		protected function build_visualization_part() {
		//--------------------------------------------------------------------------------------
			$this->viz_ui = '';
			
			if($this->view === QUERY_VIEW_RESULT) {
				$size = 12;
				$css_class = 'result-full fill-height';
				
				// remove padding of main container to fill page
				$this->viz_ui .= <<<JS
					<script>
						$(document).ready(function() {
							$('#main-container').css('padding', '0');
						});
					</script>
JS;
			}
			else {
				$size = 7;
				$css_class = 'result-column';
			}
			
			$this->viz_ui .= "<div class='col-sm-$size'>\n";			
			if($this->view != QUERY_VIEW_RESULT)
				$this->viz_ui .= "  <label for='chart_div'>Result Visualization</label>{$this->store_ui}\n";
			$this->viz_ui .= "  <div class='$css_class' id='chart_div'></div>\n";
			$this->viz_ui .= "</div>\n";
			
			if($this->chart === null || !$this->sql)
				return;
			
			$db = db_connect();
			$stmt = $db->prepare($this->sql);
			if($stmt === false)
				return proc_error('Failed to prepare statement', $db);			
			if($stmt->execute() === false)
				return proc_error('Failed to execute statement', $db);
			
			$this->viz_ui .= "<script>\n" . $this->chart->get_js($stmt) . "</script>\n";
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
		}		
	};	
?>