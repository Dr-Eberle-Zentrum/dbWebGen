<?php
	//==========================================================================================
	class dbWebGenChart_bar extends dbWebGenChart_Google {
	//==========================================================================================

		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() {
		//--------------------------------------------------------------------------------------
			return l10n(
				'chart.bar.settings',
				$this->page->render_radio($this->ctrlname('direction'), 'horizontal', true),
				$this->page->render_radio($this->ctrlname('direction'), 'vertical'),
				$this->page->render_select($this->ctrlname('stacking'), 0, array( // this is ignored
					0 => 'None',
					'absolute' => 'Absolute values',
					'percent' => 'Relative values as a percentage of 100%',
					'relative' => 'Relative values as a fraction of 1'
				))
			);
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
				'bars' => $this->page->get_post($this->ctrlname('direction')),
				'isStacked' => $this->page->get_post($this->ctrlname('stacking'))
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
