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
				$this->page->render_select($this->ctrlname('stacking'), '0', array( // this is ignored
					'0' => l10n('chart.bar.stacking.0'),
					'1' => l10n('chart.bar.stacking.1'),
					'percent' => l10n('chart.bar.stacking.percent'),
					'relative' => l10n('chart.bar.stacking.relative'),
				))
			) . parent::settings_html();
		}

		//--------------------------------------------------------------------------------------
		// need to override this because of material options conversion
		public function before_draw_js() {
		//--------------------------------------------------------------------------------------
			return parent::before_draw_js()
			 	. 'options = google.charts.Bar.convertOptions(options);';
		}

		//--------------------------------------------------------------------------------------
		protected function options() {
		//--------------------------------------------------------------------------------------
			$stacking = $this->page->get_post($this->ctrlname('stacking'));
			if($stacking == '0')
				$stacking = false;
			else if($stacking == '1')
				$stacking = true;

			return parent::options() + array(
				'bars' => $this->page->get_post($this->ctrlname('direction')),
				'isStacked' => $stacking
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
