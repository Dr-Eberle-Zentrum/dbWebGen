<?php
	//==========================================================================================
	class dbWebGenChart_annotated_timeline extends dbWebGenChart_Google {
	//==========================================================================================

		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() {
		//--------------------------------------------------------------------------------------
			return l10n(
				'chart.annotated-timeline.settings',
				$this->page->render_checkbox($this->ctrlname('scaleColumns'), 'ON', false)
			);
		}

		//--------------------------------------------------------------------------------------
		protected function options() {
		//--------------------------------------------------------------------------------------
			return parent::options() + array(
				'scaleColumns' => ($this->page->get_post($this->ctrlname('scaleColumns')) === 'ON' ? array(1,0) : null)
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
