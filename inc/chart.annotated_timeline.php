<?
	//==========================================================================================
	class dbWebGenChart_annotated_timeline extends dbWebGenChart_Google {
	//==========================================================================================
		// select day, edits "Daily Edits", (select count(*) from recent_changes_list where timestamp::date <= a.day) "Cumulative Edits" from (select timestamp::date "day", count(*) "edits" from recent_changes_list group by 1) a

		// select timestamp::date, count(*) "# Edits" from recent_changes_list group by 1 order by 1

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
