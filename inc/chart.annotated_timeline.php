<?
	//==========================================================================================
	class dbWebGenChart_annotated_timeline extends dbWebGenChart_Google {
	//==========================================================================================
		// select timestamp::date, count(*) "# Edits" from recent_changes_list group by 1 order by 1
		
		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() { 
		//--------------------------------------------------------------------------------------
			return <<<HTML
			<p>Allows producing an interactive time series line chart with annotations. The first column must be a date, all subsequent columns numeric (<a href="https://developers.google.com/chart/interactive/docs/gallery/annotationchart#data-format" target="_blank">see here</a>).</p>
HTML;
		}
		
		//--------------------------------------------------------------------------------------
		// need to override this because of material options conversion
		public function before_draw_js() {
		//--------------------------------------------------------------------------------------
			return parent::before_draw_js();
		}
		
		//--------------------------------------------------------------------------------------
		protected function options() {			
		//--------------------------------------------------------------------------------------
			return parent::options() + array(
				/*'bars' => $this->page->get_post('bar-direction'),
				'isStacked' => $this->page->get_post('bar-stacking')*/
			);
		}
		
		//--------------------------------------------------------------------------------------
		// return google charts class name to instantiate
		public function class_name() {
		//--------------------------------------------------------------------------------------
			return 'google.visualization.AnnotationChart';
		}
		
		//--------------------------------------------------------------------------------------
		// return google charts packages to include
		public function packages() {
		//--------------------------------------------------------------------------------------
			return array('annotationchart');
		}
	};
?>