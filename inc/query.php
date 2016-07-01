<?
	/*
	BAR
	select lastname_translit "Persons by Family Name", count(*)::int "Number of Persons", max(length(forename_translit))::int "Longest Name" from persons group by 1 order by 2 desc limit 10
	
	SANKEY
	select (select name from users u where u.id=h.edit_user) "User", (select signature from documents d where d.id = h.id) "Document", count(*)::int "Edits" from documents_history h group by id, edit_user order by 3 desc limit 50
	*/
	
	require_once 'chart.base.php';
	foreach(array_keys(QueryPage::$chart_types) as $type)
		require_once "chart.$type.php";
	
	//==========================================================================================
	class QueryPage extends dbWebGenPage {
	//==========================================================================================
		protected $sql, $view;
		protected $query_ui, $settings_ui, $viz_ui;
		public static $chart_types = array(
			'table' => 'Table', 
			'bar' => 'Bar Chart',
			'sankey' => 'Sankey Chart',
			'candlestick' => 'Candlestick Chart'
		);
	
		//--------------------------------------------------------------------------------------
		public function __construct() {
		//--------------------------------------------------------------------------------------
			parent::__construct();
			
			$this->sql = trim($this->get_post('sql', ''));
			$this->view = $this->get_urlparam(QUERY_PARAM_VIEW, QUERY_VIEW_AUTO);
		}
	
		//--------------------------------------------------------------------------------------
		public function render() {
		//--------------------------------------------------------------------------------------
			if($this->has_post_values() && mb_substr(mb_strtolower($this->sql), 0, 6) !== 'select') {
				proc_error('Invalid SQL query. Only SELECT statements are allowed!');
				$this->sql = null;
			}
			
			if($this->has_post_values())
				$this->add_script();

			$this->build_query_part();
			$this->build_settings_part();
			$this->build_visualization_part();
			$this->layout();
		}
		
		//--------------------------------------------------------------------------------------
		protected function add_script() {
		//--------------------------------------------------------------------------------------
			echo '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';
		}
		
		//--------------------------------------------------------------------------------------
		protected function build_query_part() {
		//--------------------------------------------------------------------------------------
			$sql_html = html($this->sql);
			
			$this->query_ui = <<<QUI
				<div class="form-group">
					<label class="control-label" for="sql">SQL Query</label>
					<textarea class="form-control" id="sql" name="sql" rows="10">{$sql_html}</textarea>
				</div>
				<div class="form-group">
					<button class="btn btn-primary" type="submit">Execute</button>
				</div>
QUI;
		}
		
		//--------------------------------------------------------------------------------------
		protected function build_settings_part() {
		//--------------------------------------------------------------------------------------
			$select = $this->render_select('viz-type', first(array_keys(QueryPage::$chart_types)), QueryPage::$chart_types);
			$settings = '';
			foreach(QueryPage::$chart_types as $type => $name)
				$settings .= "<div id='viz-option-$type'>" . ChartFactory::get($type, $this)->settings_html() . "</div>\n";
			
			$this->settings_ui = <<<STR
				<div class="panel panel-default">
					<div class="panel-heading">
						Result Visualization Settings
					</div>
					<div class="panel-body">						
						<div class="form-group">
							<label class="control-label" for="viz-type">Visualization</label>
							{$select}
						</div>						
						<div class='viz-options form-group'>
							{$settings}							
						</div>
					</div>					
				</div>
				<script>
					$('#viz-type').change(function() {
						$('.viz-options').children('div').hide();
						$("#viz-option-" + $('#viz-type').val()).show();
					});
				</script>
STR;
		}
		
		//--------------------------------------------------------------------------------------
		protected function build_visualization_part() {
		//--------------------------------------------------------------------------------------
			$this->viz_ui = '';
			
			$size = $this->view === QUERY_VIEW_RESULT ? 12 : 7;
			$css_class = $this->view === QUERY_VIEW_RESULT ? 'result-full fill-height' : 'result-column';
			
			if($this->view != QUERY_VIEW_RESULT)
				$this->viz_ui .= "<label for='chart_div'>Result Visualization</label>\n";
			
			$this->viz_ui .= "<div class='col-sm-$size $css_class' id='chart_div'></div>\n";
			
			if(!$this->sql)
				return;
			
			$db = db_connect();
			$stmt = $db->prepare($this->sql);
			if($stmt === false)
				return proc_error('Failed to prepare statement', $db);
			
			if($stmt->execute() === false)
				return proc_error('Failed to execute statement', $db);
			
			$chart = ChartFactory::get($this->get_post('viz-type'), $this);
			
			$this->viz_ui .= <<<HTML
			<script>
				google.charts.load('current', { packages: {$chart->packages_js()} } );
				google.charts.setOnLoadCallback(draw_chart);				

				function draw_chart() {
					console.log('draw_chart');
					var data = google.visualization.arrayToDataTable([
HTML;

			$first = true;
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					if($first) {
						$this->viz_ui .= json_encode(array_keys($row)) . ",\n";
						$first = false;
					}
					$this->viz_ui .= json_encode(array_values($row)) . ",\n";
			}

			$this->viz_ui .= <<<HTML
					]);
					var options = {$chart->options_js()};		
					options.width = $('#chart_div').width() - 15;
					var chart = new {$chart->class_name()}(document.getElementById('chart_div'));
					{$chart->before_draw_js()}
					chart.draw(data, options);
				}		
				
				$('#chart_div').deferredResize(draw_chart, 500);
			</script>
HTML;
		}
		
		//--------------------------------------------------------------------------------------
		public function layout() {
		//--------------------------------------------------------------------------------------
			if($this->view === QUERY_VIEW_AUTO) {
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