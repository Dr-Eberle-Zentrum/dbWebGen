<?
	//==========================================================================================
	class dbWebGenChart_bar extends dbWebGenChart_Google {
	//==========================================================================================
		// select name "Student", num_changes "Number of Edits" from view_changes_by_user v, users u where role = 'user' and u.id = v.user_id order by 2 desc

		// select lastname_translit "Persons by Family Name", count(*)::int "Number of Persons", max(length(forename_translit))::int "Longest Name" from persons group by 1 order by 2 desc limit 10

		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() {
		//--------------------------------------------------------------------------------------
			return l10n(
				'chart.bar.settings',
				$this->page->render_radio($this->ctrlname('direction'), 'horizontal', true),
				$this->page->render_radio($this->ctrlname('direction'), 'vertical'),
				$this->page->render_select($this->ctrlname('stacking'), 0, array(
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
