<?
	//==========================================================================================
	abstract class dbWebGenChart_Google extends dbWebGenChart {
	//==========================================================================================
		// return google charts class name to instantiate
		abstract public function class_name();
		
		// return google charts packages to include
		abstract protected function packages();		
		
		//--------------------------------------------------------------------------------------
		public function options_js() {
		//--------------------------------------------------------------------------------------
			return json_encode($this->options());
		}
		
		//--------------------------------------------------------------------------------------
		public function packages_js() {
		//--------------------------------------------------------------------------------------
			return json_encode($this->packages());
		}
		
		//--------------------------------------------------------------------------------------
		// override if additional scripts are needed for this type
		public function add_required_scripts() {
		//--------------------------------------------------------------------------------------
			add_javascript('https://www.gstatic.com/charts/loader.js');
		}
		
		//--------------------------------------------------------------------------------------
		// any default options. call this from subclasses, then add to default array
		protected function options() {			
		//--------------------------------------------------------------------------------------
			return array();
		}
		
		//--------------------------------------------------------------------------------------
		// shall we subtract scrollbar from visualization width? default true
		protected function shall_subtract_scrollbar() {			
		//--------------------------------------------------------------------------------------
			return true;
		}
		
		//--------------------------------------------------------------------------------------
		// any js to be rendered before the actual draw() call.
		public function before_draw_js() {
		//--------------------------------------------------------------------------------------
			return '';
		}
		
		//--------------------------------------------------------------------------------------
		public /*string*/ function data_to_js(&$row, $row_nr) {
		//--------------------------------------------------------------------------------------
			$r = '';
			if($row_nr === 0) // first row => render headers
				$r .= json_encode(array_keys($row)) . ",\n";				
			
			return $r . json_encode(array_values($row), JSON_NUMERIC_CHECK) . ",\n";
		}
		
		//--------------------------------------------------------------------------------------
		// returns js code to fill the chart div
		public function get_js($query_result) {
		//--------------------------------------------------------------------------------------
			$viz_ui = <<<JS
				google.charts.load('current', { packages: {$this->packages_js()} } );
				google.charts.setOnLoadCallback(draw_chart);				

				function draw_chart() {
					console.log('draw_chart');
					var data = google.visualization.arrayToDataTable([
JS;

			// for those chart types that do not have built in scrollbar (like table) we need to subtract scrollbar width
			$subtract_width = $this->shall_subtract_scrollbar() ? 20 : 0;
			
			$row_nr = 0;
			while($row = $query_result->fetch(PDO::FETCH_ASSOC)) {				
				$viz_ui .= $this->data_to_js($row, $row_nr++);
			}			

			$viz_ui .= <<<JS
					]);
					var options = {$this->options_js()};		
					options.width = $('#chart_div').width() - {$subtract_width};
					var chart = new {$this->class_name()}(document.getElementById('chart_div'));
					{$this->before_draw_js()}
					chart.draw(data, options);
				}		
				
				$('#chart_div').deferredResize(draw_chart, 500);
JS;
			return $viz_ui;
		}		
	};
?>