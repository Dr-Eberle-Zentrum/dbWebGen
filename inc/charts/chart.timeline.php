<?php
	//==========================================================================================
	class dbWebGenChart_timeline extends dbWebGenChart_Google {
	//==========================================================================================
		
		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() {
		//--------------------------------------------------------------------------------------
			return l10n(
				'chart.timeline.settings',
				$this->page->render_checkbox($this->ctrlname('showRowLabels'), 'ON', true),
				$this->page->render_checkbox($this->ctrlname('has-singleColor'), 'ON', false),
				$this->page->render_textbox($this->ctrlname('singleColor'), 'darkgreen'),
				$this->page->render_checkbox($this->ctrlname('tooltips'), 'ON', true)
			);
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
					'showRowLabels' => ($this->page->get_post($this->ctrlname('showRowLabels')) == 'ON'),
					'singleColor' => ($this->page->get_post($this->ctrlname('has-singleColor')) == 'ON' ?
						$this->page->get_post($this->ctrlname('singleColor')) : null)
				),
				'tooltip' => array(
					'trigger' => ($this->page->get_post($this->ctrlname('tooltips')) == 'ON' ? 'focus' : 'none')
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
