<?
	//==========================================================================================
	class dbWebGenChart_pie extends dbWebGenChart_Google {
	//==========================================================================================

		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() {
		//--------------------------------------------------------------------------------------
			return l10n(
				'chart.pie.settings',
                $this->page->render_checkbox($this->ctrlname('is3D'), 'ON', false),
				$this->page->render_checkbox($this->ctrlname('isDonut'), 'ON', false)
			);
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
                'legend' => 'none',
                'pieSliceText' => 'label',
				'is3D' => $this->page->get_post($this->ctrlname('is3D')) == 'ON',
				'pieHole' => $this->page->get_post($this->ctrlname('isDonut')) == 'ON' ? .5 : 0
			);
		}

		//--------------------------------------------------------------------------------------
		// return google charts class name to instantiate
		public function class_name() {
		//--------------------------------------------------------------------------------------
			return 'google.visualization.PieChart';
		}

		//--------------------------------------------------------------------------------------
		// return google charts packages to include
		public function packages() {
		//--------------------------------------------------------------------------------------
			return array('corechart');
		}
	};
?>
