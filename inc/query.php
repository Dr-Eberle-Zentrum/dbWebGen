<?
	/*
	select lastname_translit "Persons by Family Name", count(*)::int "Number of Persons", max(length(forename_translit))::int "Longest Name" from persons group by 1 order by 2 desc limit 10
	
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
			
			$this->sql = $this->get_post('sql', '');
		}
	
		//--------------------------------------------------------------------------------------
		public function render() {
		//--------------------------------------------------------------------------------------
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
								'barchart' => 'Bar Chart'
							))}																
						</div>						
						<div class='viz-options form-group'>
							<div id='viz-option-table'>{$this->get_table_settings()}</div>
							<div id='viz-option-barchart'>{$this->get_barchart_settings()}</div>
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
			$this->viz_ui = '<div class="bg-gray col-sm-7" id="chart_div" style="min-height:400px;">Query results will appear here.</div>';
			
			if(!$this->sql)
				return;
			
			$db = db_connect();
			$stmt = $db->prepare($this->sql);
			if($stmt === false)
				return proc_error('Failed to prepare statement', $db);
			
			if($stmt->execute() === false)
				return proc_error('Failed to execute statement', $db);
			
			echo <<<HTML
			<script>
				google.charts.load('current', {packages: ['corechart', 'bar']});
				google.charts.setOnLoadCallback(drawTitleSubtitle);

				function drawTitleSubtitle() {
					  var data = google.visualization.arrayToDataTable([
HTML;

			$first = true;
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					if($first) {
						echo json_encode(array_keys($row)) . ",\n";
						$first = false;
					}
					echo json_encode(array_values($row)) . ",\n";
			}

			echo <<<HTML
					]);
					var options = {
						chart: {
							title: '',
							subtitle: '',
							chartArea: { left: '5px', right: '12px' }
						},
						hAxis: {
							title: 'Total Population',
							minValue: 0,
						},
						vAxis: {
							title: 'City'
						},
						bars: 'horizontal'
					};
					var material = new google.charts.Bar(document.getElementById('chart_div'));
					material.draw(data, options);
			}
			</script>
HTML;
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