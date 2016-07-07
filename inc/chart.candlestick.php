<?
	//==========================================================================================
	class dbWebGenChart_candlestick extends dbWebGenChart_Google {
	//==========================================================================================
		
		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() { 
		//--------------------------------------------------------------------------------------
			return <<<HTML
				<p>A candlestick chart is used to show an opening and closing value overlaid on top of a total variance. It requires <a target=_blank href="https://developers.google.com/chart/interactive/docs/gallery/candlestickchart#data-format">four columns</a> in the query result.</p>
				
HTML;
		}
		
		//--------------------------------------------------------------------------------------
		protected function options() {			
		//--------------------------------------------------------------------------------------
			return parent::options() + array(				
			);
		}
		
		//--------------------------------------------------------------------------------------
		// return google charts class name to instantiate
		public function class_name() {
		//--------------------------------------------------------------------------------------
			return 'google.visualization.CandlestickChart';
		}
		
		//--------------------------------------------------------------------------------------
		// return google charts packages to include
		public function packages() {
		//--------------------------------------------------------------------------------------
			return array('corechart');
		}
	};
?>