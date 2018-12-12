<?php
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
		// override this to return true if the chart allows downloading of data
		public /*bool*/ function can_download() {
		//--------------------------------------------------------------------------------------
            return true;
		}

		//--------------------------------------------------------------------------------------
		public /*array*/ function get_columns(&$stmt) {
		//--------------------------------------------------------------------------------------
			$cols = array();
			$this->column_infos = array();

			for($i=0; $i<$stmt->columnCount(); $i++) {
				// type can be 'string', 'number', 'boolean', 'date', 'datetime', and 'timeofday'
				$col_info = $stmt->getColumnMeta($i);
				#debug_log($col_info);
				// we could be dealing with a PDOStatementEmulator instance (see class in db.php),
				// then the js_type is already set
				if(!isset($col_info['js_type'])) {
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
				}

				$col_info['index'] = $i;
				$this->column_infos[] = $col_info;

				$cols[] = array(
					'type' => $col_info['js_type'],
					'id' => $col_info['name'],
					'label' => $col_info['name']
				);
			}

			#debug_log($this->column_infos);
			return $cols;
		}

		//--------------------------------------------------------------------------------------
		public /*string*/ function value_to_js($value, &$column_info) {
		//--------------------------------------------------------------------------------------
			if($value === null)
				return json_encode($value);

			// javascript month in [0,11] !!!

			switch($column_info['js_type']) {
				case 'timeofday':
					$d = strtotime($value);
					return $d === false ?
						json_encode($value) :
						sprintf('new Date(0,0,0,%s)', date('G,i,s', $d));

				case 'date':
					$d = strtotime($value);
					return $d === false ?
						json_encode($value) :
						sprintf('new Date(%s,%s,%s)', date('Y', $d), intval(date('n', $d)) - 1, date('j', $d));

				case 'datetime':
					return "new Date('{$value}')";
					$d = strtotime($value);
					if(d === false)
						return json_encode($value);

					$microsecs = intval(date('u', $d));
					sprintf('new Date(%s,%s,%s%s)', date('Y', $d), intval(date('n', $d)) - 1, date('j,G,i,s', $d),
						$microsecs == 0 ? '' : ".{$microsecs}");

				case 'number':
					return json_encode($value, JSON_NUMERIC_CHECK);

				default:
					return json_encode($value);
			}
		}

		//--------------------------------------------------------------------------------------
		public /*string*/ function data_to_js(&$row, $row_nr) {
		//--------------------------------------------------------------------------------------
			$json = array();
			$col_nr = 0;
			foreach($row as $col_name => $value)
				$json[] = $this->value_to_js($value, $this->column_infos[$col_nr++]);

			return '[' . implode(', ', $json) . ']';
		}

		//--------------------------------------------------------------------------------------
		// returns js code to fill the chart div
		public function get_js($query_result) {
		//--------------------------------------------------------------------------------------
			// for those chart types that do not have built in scrollbar (like table) we need to subtract scrollbar width
			$subtract_width = $this->shall_subtract_scrollbar() ? 20 : 0;

			$table_cols = '';
			$columns = $this->get_columns($query_result);
			foreach($columns as $col_obj)
				$table_cols .= 'data_table.addColumn(' . json_encode($col_obj) . ");\n";

			$data_array = '';
			$row_nr = 0;
			while($row = $query_result->fetch(PDO::FETCH_ASSOC)) {
				// check for duplicate column names, because this will cause "invisible" error in the browser
				if($row_nr == 0 && count($row) != count($columns))
					proc_error(l10n('error.chart-duplicate-cols'));
				// <<

				$data_array .= ($row_nr == 0 ? '' : ",\n") . $this->data_to_js($row, $row_nr++);
			}
			$can_download_js = json_encode($this->can_download());
			$viz_ui = <<<JS
				var data_array = [
					{$data_array}
				];
				var downloadable_data = $can_download_js ? data_array : null;
				google.charts.load('current', { packages: {$this->packages_js()} } );
				google.charts.setOnLoadCallback(draw_chart);
				function draw_chart() {
					console.log('draw_chart');
					var data_table = new google.visualization.DataTable();
					{$table_cols}
					data_table.addRows(data_array);

					var options = {$this->options_js()};
					options.width = $('#chart_div').width() - {$subtract_width};
					var chart = new {$this->class_name()}(document.getElementById('chart_div'));
					{$this->before_draw_js()}
					if(typeof dbwebgen_chart_beforedraw === 'function')
						dbwebgen_chart_beforedraw(chart, data_table, options);
					chart.draw(data_table, options);
				}

				$('#chart_div').deferredResize(draw_chart, 500);
JS;
			return $viz_ui;
		}
	};
?>
