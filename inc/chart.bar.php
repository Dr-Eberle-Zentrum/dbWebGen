<?
	//==========================================================================================
	class dbWebGen_chart_bar extends dbWebGen_chart_base {
	//==========================================================================================
		
		//--------------------------------------------------------------------------------------
		public function settings_html() { 
		//--------------------------------------------------------------------------------------
			return <<<HTML
			<p>Put labels in the first column, followed by one column per data series.</p>
			<div class="form-group">
				<label class="control-label">Bar Direction</label>
				<div>
					<label class="radio-inline">{$this->page->render_radio('bar-direction', 'horizontal')}Horizontal</label>
					<label class="radio-inline">{$this->page->render_radio('bar-direction', 'vertical', true)}Vertical</label>
				</div>
			</div>
			<!-- STACKED DOES NOT WORK !<div class="form-group">
				<label class="control-label">Stacking of Values</label>
				<div>
					{$this->page->render_select('bar-stacking', 0, array(
						0 => 'None',
						'absolute' => 'Absolute values',
						'percent' => 'Relative values as a percentage of 100%',
						'relative' => 'Relative values as a fraction of 1'						
					))}
				</div>
			</div>-->
HTML;
		}
		
		//--------------------------------------------------------------------------------------
		// need to override this because of material options conversion
		public function before_draw_js() {
		//--------------------------------------------------------------------------------------
			return 'options = google.charts.Bar.convertOptions(options);';
		}
		
		//--------------------------------------------------------------------------------------
		protected function options() {			
		//--------------------------------------------------------------------------------------
			return parent::options() + array(
				'bars' => $this->page->get_post('bar-direction'),
				'isStacked' => $this->page->get_post('bar-stacking')
			);
		}
		
		//--------------------------------------------------------------------------------------
		// return google charts class name to instantiate
		public function class_name() {
		//--------------------------------------------------------------------------------------
			return 'google.charts.Bar';
		}
		
		//--------------------------------------------------------------------------------------
		// return google charts packages to include
		public function packages() {
		//--------------------------------------------------------------------------------------
			return array('bar');
		}
	};
?>