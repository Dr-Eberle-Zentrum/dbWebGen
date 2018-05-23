<?php
	//==========================================================================================
	class dbWebGenChart_geo extends dbWebGenChart_Google {
	//==========================================================================================

		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() {
		//--------------------------------------------------------------------------------------
			return l10n(
				'chart.geo.settings',
				$this->page->render_radio($this->ctrlname('mode'), 'markers', true),
				$this->page->render_radio($this->ctrlname('mode'), 'regions'),
				$this->page->render_radio($this->ctrlname('mode'), 'text'),
				$this->ctrlname('region'),
				get_help_popup('Region', l10n('chart.geo.region-helptext')),
				$this->page->render_textbox($this->ctrlname('region'), 'world')
			);
		}

		//--------------------------------------------------------------------------------------
		public function add_required_scripts() {
		//--------------------------------------------------------------------------------------
			parent::add_required_scripts();
			add_javascript('https://www.google.com/jsapi');
		}

		//--------------------------------------------------------------------------------------
		// need to override this because of material options conversion
		public function before_draw_js() {
		//--------------------------------------------------------------------------------------
			return '';
		}

		//--------------------------------------------------------------------------------------
		protected function options() {
		//--------------------------------------------------------------------------------------
			return parent::options() + array(
				'region' => $this->page->get_post($this->ctrlname('region')),
				'displayMode' => $this->page->get_post($this->ctrlname('mode')),
				#'sizeAxis' => array('minSize' =>  1,  'maxSize' => 10),
			);
		}

		//--------------------------------------------------------------------------------------
		// return google charts class name to instantiate
		public function class_name() {
		//--------------------------------------------------------------------------------------
			return 'google.visualization.GeoChart';
		}

		//--------------------------------------------------------------------------------------
		// return google charts packages to include
		public function packages() {
		//--------------------------------------------------------------------------------------
			return array('geochart');
		}
	};
?>
