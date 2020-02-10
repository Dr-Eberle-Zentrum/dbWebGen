<?php
	//==========================================================================================
	class dbWebGenChart_custom extends dbWebGenChart_Google {
	//==========================================================================================


		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() {
		//--------------------------------------------------------------------------------------
			return l10n(
				'chart.custom.settings',
				$this->page->render_textbox($this->ctrlname('packages'), ''),
				$this->page->render_textbox($this->ctrlname('classname'), '')
			) 
			. parent::settings_html();
		}

		//--------------------------------------------------------------------------------------
		protected function options() {
		//--------------------------------------------------------------------------------------
			return parent::options() + array(
			);
		}

		//--------------------------------------------------------------------------------------
		// return google charts class name to instantiate; default to table chart
		public function class_name() {
		//--------------------------------------------------------------------------------------
			return 'google.visualization.' . $this->page->get_post($this->ctrlname('classname'), 'Table');
		}

		//--------------------------------------------------------------------------------------
		// return google charts packages to include; default to table chart
		public function packages() {
		//--------------------------------------------------------------------------------------
			return array_map(
				function ($package) { return trim($package); }, 
				explode(',', $this->page->get_post($this->ctrlname('packages'), 'table'))
			);
		}
	};
?>
