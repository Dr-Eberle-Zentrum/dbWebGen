<?php
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
				$this->page->render_checkbox($this->ctrlname('isDonut'), 'ON', false),
                $this->page->render_select($this->ctrlname('pieSliceText'), 'percentage', array(
					'percentage' => l10n('chart.pie.pie-slice-text.percentage'),
					'value' => l10n('chart.pie.pie-slice-text.value'),
					'label' => l10n('chart.pie.pie-slice-text.label'),
					'none' => l10n('chart.pie.pie-slice-text.none')
				)),
                $this->page->render_select($this->ctrlname('legendPosition'), 'right', array(
					'bottom' => l10n('chart.pie.legend-position.bottom'),
					'labeled' => l10n('chart.pie.legend-position.labeled'),
                    'left' => l10n('chart.pie.legend-position.left'),
                    'none' => l10n('chart.pie.legend-position.none'),
                    'right' => l10n('chart.pie.legend-position.right'),
                    'top' => l10n('chart.pie.legend-position.top')
				))
			);
		}

		//--------------------------------------------------------------------------------------
		protected function options() {
		//--------------------------------------------------------------------------------------
			return parent::options() + array(
                'legend' => array('position' => $this->page->get_post($this->ctrlname('legendPosition'))),
                'pieSliceText' => $this->page->get_post($this->ctrlname('pieSliceText')),
				'is3D' => $this->page->get_post($this->ctrlname('is3D')) == 'ON',
				'pieHole' => $this->page->get_post($this->ctrlname('isDonut')) == 'ON' ? .5 : 0,
                'chartArea' => array(
                    'top' => '4%',
                    'height' => '90%'
                )
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
