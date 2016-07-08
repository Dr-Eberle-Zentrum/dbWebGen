<?
	//==========================================================================================
	class dbWebGenChart_timeline extends dbWebGenChart_Google {
	//==========================================================================================
		// select id::varchar, signature, gregorian_year_lower, gregorian_year_upper from documents where gregorian_year_lower is not null order by gregorian_year_lower
		
		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() { 
		//--------------------------------------------------------------------------------------
			return <<<HTML
			<p>Plots date and time ranges as bars on a scrollable timeline. The query result columns must comply with the <a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/timeline#data-format">specified data format</a>.</p>
			<div class="form-group">
				<label class="control-label">Options</label>
				<div class='checkbox top-margin-zero'>
					<label>{$this->page->render_checkbox('timeline-showRowLabels', 'ON', true)}Show row labels</label>
				</div>
				<div class='checkbox'>
					<label>{$this->page->render_checkbox('timeline-has-singleColor', 'ON', false)}Single color for all bars: {$this->page->render_textbox('timeline-singleColor', 'darkgreen')}</label>
				</div>
			</div>
HTML;
		}
		
		//--------------------------------------------------------------------------------------
		public function add_required_scripts() {			
		//--------------------------------------------------------------------------------------
			parent::add_required_scripts();
		}		
		
		//--------------------------------------------------------------------------------------
		// need to override this because of material options conversion
		public function before_draw_js() {
		//--------------------------------------------------------------------------------------
			return '';
		}
		
		//--------------------------------------------------------------------------------------
		public /*array*/ function get_columns(&$stmt) {
		//--------------------------------------------------------------------------------------
			// need to convert numbers (=years) to dates, otherwise timeline wont display
			$cols = parent::get_columns($stmt);
			
			for($c = 0; $c < count($this->column_infos); $c++) {
				if($this->column_infos[$c]['js_type'] == 'number')
					$this->column_infos[$c]['js_type'] = $cols[$c]['type'] = 'date';
			}
			
			return $cols;
		}
		
		//--------------------------------------------------------------------------------------
		public /*string*/ function data_to_js(&$row, $row_nr) {
		//--------------------------------------------------------------------------------------
			// convert numeric to date or timeofday
			
			$json = array();
			$c = 0;
			$started = false;
			foreach($row as $col_name => $col_value) {								
				if($col_value !== null && $this->column_infos[$c]['js_type'] == 'date') {
					if($started)
						$json[] = "new Date('{$col_value}-12-31')";
					else
						$json[] = "new Date('{$col_value}-01-01')";
					$started = true;
				}
				else
					$json[] = json_encode($col_value);
				
				$c++;
			}			
			
			return '[' . implode(', ', $json) . ']';
		}		
		
		//--------------------------------------------------------------------------------------
		protected function options() {			
		//--------------------------------------------------------------------------------------
			return parent::options() + array(
				'timeline' => array(
					'showRowLabels' => ($this->page->get_post('timeline-showRowLabels') == 'ON'),
					'singleColor' => ($this->page->get_post('timeline-has-singleColor') == 'ON' ? 
						$this->page->get_post('timeline-singleColor') : null)
				)
			);
		}
		
		//--------------------------------------------------------------------------------------
		// return google charts class name to instantiate
		public function class_name() {
		//--------------------------------------------------------------------------------------
			return 'google.visualization.Timeline';
		}
		
		//--------------------------------------------------------------------------------------
		// return google charts packages to include
		public function packages() {
		//--------------------------------------------------------------------------------------
			return array('timeline');
		}
	};
?>