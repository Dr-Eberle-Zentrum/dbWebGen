<?
	/*
	BAR
	select lastname_translit "Persons by Family Name", count(*)::int "Number of Persons", max(length(forename_translit))::int "Longest Name" from persons group by 1 order by 2 desc limit 10
	
	SANKEY
	select (select name from users u where u.id=h.edit_user) "User", (select signature from documents d where d.id = h.id) "Document", count(*)::int "Edits" from documents_history h group by id, edit_user order by 3 desc limit 50	
	*/
	
	//==========================================================================================
	class QueryPage extends dbWebGenPage {
	//==========================================================================================
		protected $sql;
		protected $query_ui, $settings_ui, $viz_ui;
	
		//--------------------------------------------------------------------------------------
		public function __construct() {
		//--------------------------------------------------------------------------------------
			parent::__construct();
			
			$this->sql = trim($this->get_post('sql', ''));
		}
	
		//--------------------------------------------------------------------------------------
		public function render() {
		//--------------------------------------------------------------------------------------
			if(mb_substr(mb_strtolower($this->sql), 0, 6) !== 'select') {
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
		protected function get_table_settings() { 
		//--------------------------------------------------------------------------------------
			return <<<HTML
				<p>The query result will be visualized as a table.</p>
HTML;
		}
		
		//--------------------------------------------------------------------------------------
		protected function get_sankey_settings() { 
		//--------------------------------------------------------------------------------------
			return <<<HTML
				<p>A sankey diagram is a visualization used to depict a flow from one set of values to another. The things being connected are called nodes and the connections are called links. Sankeys are best used when you want to show a many-to-many mapping between two domains (e.g., universities and majors) or multiple paths through a set of stages (for instance, Google Analytics uses sankeys to show how traffic flows from pages to other pages on your web site).</p>
HTML;
		}
		
		//--------------------------------------------------------------------------------------
		protected function get_barchart_settings() { 
		//--------------------------------------------------------------------------------------
			return <<<HTML
			<p>Put labels in the first column, followed by one column per data series.</p>
			<div class="form-group">
				<label class="control-label">Bar Direction</label>
				<div>
					<label class="radio-inline">{$this->render_radio('barchart-direct', 'horizontal')}Horizontal</label>
					<label class="radio-inline">{$this->render_radio('barchart-direct', 'vertical',true)}Vertical</label>
				</div>
			</div>
HTML;
		}
		
		//--------------------------------------------------------------------------------------
		protected function build_settings_part() {
		//--------------------------------------------------------------------------------------
			$this->settings_ui = <<<STR
				<div class="panel panel-default">
					<div class="panel-heading">
						Result Visualization Settings
					</div>
					<div class="panel-body">						
						<div class="form-group">
							<label class="control-label" for="viz-type">Visualization</label>
							{$this->render_select('viz-type', 'table', array(
								'table' => 'Table',
								'barchart' => 'Bar Chart',
								'sankey' => 'Sankey Chart'
							))}																
						</div>						
						<div class='viz-options form-group'>
							<div id='viz-option-table'>{$this->get_table_settings()}</div>
							<div id='viz-option-barchart'>{$this->get_barchart_settings()}</div>
							<div id='viz-option-sankey'>{$this->get_sankey_settings()}</div>
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
			$this->viz_ui = '<label for="chart_div">Result Visualization</label><div class="col-sm-7" id="chart_div">Query results will appear here.</div>';
			
			if(!$this->sql)
				return;
			
			$db = db_connect();
			$stmt = $db->prepare($this->sql);
			if($stmt === false)
				return proc_error('Failed to prepare statement', $db);
			
			if($stmt->execute() === false)
				return proc_error('Failed to execute statement', $db);
			
			$this->viz_ui .= <<<HTML
			<script>
				google.charts.load('current', {packages: [ {$this->get_chart_packages()} ]});
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
					var options = {$this->get_chart_options()};		
					options['width'] = $('#chart_div').width() - 15;
					var chart = new {$this->get_chart_classname()}(document.getElementById('chart_div'));					
					chart.draw(data, options);
				}		
				
				$('#chart_div').deferredResize(draw_chart, 500);
			</script>
HTML;
		}
		
		//--------------------------------------------------------------------------------------
		public function get_chart_options() {
		//--------------------------------------------------------------------------------------
			$options = array();
			switch($this->get_post('viz-type')) {
				case 'barchart': 
					$options['bars'] = $this->get_post('barchart-direct');
					break;
			}			
			return json_encode($options);
		}
		
		//--------------------------------------------------------------------------------------
		public function get_chart_classname() {
		//--------------------------------------------------------------------------------------
			switch($this->get_post('viz-type')) {
				case 'table': return 'google.visualization.Table';
				case 'barchart': return 'google.charts.Bar';
				case 'sankey': return 'google.visualization.Sankey';
			}
		}
		
		//--------------------------------------------------------------------------------------
		public function get_chart_packages() {
		//--------------------------------------------------------------------------------------
			switch($this->get_post('viz-type')) {
				case 'table': return "'table'";
				case 'barchart': return "'bar'";
				case 'sankey': return "'sankey'";
			}
		}
		
		//--------------------------------------------------------------------------------------
		public function layout() {
		//--------------------------------------------------------------------------------------
echo <<<HTML
			<form class='' role='form' method='post' enctype='multipart/form-data'>
				<div class='col-sm-5'>
					<div>{$this->query_ui}</div>
					<div>{$this->settings_ui}</div>
				</div>			
			</form>
			{$this->viz_ui}
HTML;
		}		
	};	
?>