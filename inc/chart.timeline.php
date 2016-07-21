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
				<div class='checkbox'>
					<label>{$this->page->render_checkbox('timeline-tooltips', 'ON', true)}Show tooltips</label>
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
		
		private $start_value_seen = false;
		
		//--------------------------------------------------------------------------------------
		public /*string*/ function value_to_js($value, &$column_info) {
		//--------------------------------------------------------------------------------------
			if($value !== null && $column_info['js_type'] == 'date') {
				if($this->start_value_seen) {					
					$this->start_value_seen = false;
					return "new Date('{$value}-12-31')";
				}
				else {
					$this->start_value_seen = true;
					return "new Date('{$value}-01-01')";					
				}
			}
				
			return parent::value_to_js($value, $column_info);
		}
		
		//--------------------------------------------------------------------------------------
		protected function options() {			
		//--------------------------------------------------------------------------------------
			return parent::options() + array(
				'timeline' => array(
					'showRowLabels' => ($this->page->get_post('timeline-showRowLabels') == 'ON'),
					'singleColor' => ($this->page->get_post('timeline-has-singleColor') == 'ON' ? 
						$this->page->get_post('timeline-singleColor') : null)
				),
				'tooltip' => array(
					'trigger' => ($this->page->get_post('timeline-tooltips') == 'ON' ? 'focus' : 'none')
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