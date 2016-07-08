<?
	//==========================================================================================
	abstract class dbWebGenChart_Google extends dbWebGenChart {
	//==========================================================================================
		protected $column_infos;
		
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
		public /*array*/ function get_columns(&$stmt) {
		//--------------------------------------------------------------------------------------
			$cols = array();
			$this->column_infos = array();
			
			for($i=0; $i<$stmt->columnCount(); $i++) {
				// type can be 'string', 'number', 'boolean', 'date', 'datetime', and 'timeofday'
				$col_info = $stmt->getColumnMeta($i);
				switch($col_info['native_type']) {
					case 'int': case 'int2': case 'int4': case 'int8': case 'numeric': case 'float4': case 'float8':
						$col_info['js_type'] = 'number'; break;						
					
					case 'text': case 'varchar': case 'bpchar':
						$col_info['js_type'] = 'string'; break;
						
					case 'bool':
						$col_info['js_type'] = 'boolean'; break;						
					
					case 'date':
						$col_info['js_type'] = 'date'; break;
						
					case 'timestamp': case 'timestamptz':
						$col_info['js_type'] = 'datetime'; break;
						
					case 'time': case 'timetz':
						$col_info['js_type'] = 'timeofday'; break;
						
					default:
						$col_info['js_type'] = 'string';
				}
				
				$col_info['index'] = $i;				
				$this->column_infos[] = $col_info;
				#debug_log($col_info);
				
				$cols[] = array(
					'type' => $col_info['js_type'],
					'id' => $col_info['name'],
					'label' => $col_info['name']
				);
			}
			
			return $cols;
		}
		
		//--------------------------------------------------------------------------------------
		public /*string*/ function data_to_js(&$row, $row_nr) {
		//--------------------------------------------------------------------------------------
			return json_encode(array_values($row), JSON_NUMERIC_CHECK);
		}
		
		//--------------------------------------------------------------------------------------
		// returns js code to fill the chart div
		public function get_js($query_result) {
		//--------------------------------------------------------------------------------------
			// for those chart types that do not have built in scrollbar (like table) we need to subtract scrollbar width
			$subtract_width = $this->shall_subtract_scrollbar() ? 20 : 0;			
			
			$table_cols = '';
			foreach($this->get_columns($query_result) as $col_obj)
				$table_cols .= 'data_table.addColumn(' . json_encode($col_obj) . ");\n";
			
			$data_array = '';
			$row_nr = 0;
			while($row = $query_result->fetch(PDO::FETCH_ASSOC)) {
				$data_array .= ($row_nr == 0 ? '' : ",\n") . $this->data_to_js($row, $row_nr++);
			}	
			
			$viz_ui = <<<JS
				google.charts.load('current', { packages: {$this->packages_js()} } );
				google.charts.setOnLoadCallback(draw_chart);				

				function draw_chart() {
					console.log('draw_chart');
					var data_array = [ 
						{$data_array}
					];
					var data_table = new google.visualization.DataTable();
					{$table_cols}
					data_table.addRows(data_array);

					var options = {$this->options_js()};		
					options.width = $('#chart_div').width() - {$subtract_width};
					var chart = new {$this->class_name()}(document.getElementById('chart_div'));
					{$this->before_draw_js()}
					chart.draw(data_table, options);
				}		
				
				$('#chart_div').deferredResize(draw_chart, 500);
JS;
			return $viz_ui;
		}		
	};
?>