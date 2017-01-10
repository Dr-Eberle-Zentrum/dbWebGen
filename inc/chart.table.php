<?
	//==========================================================================================
	class dbWebGenChart_table extends dbWebGenChart_Google {
	//==========================================================================================

		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() {
		//--------------------------------------------------------------------------------------
			return <<<HTML
				<p>The query result will be visualized as a table.</p>
				<div class="form-group">
					<label class="control-label">Options</label>
					<div class='checkbox top-margin-zero'>
						<label>{$this->page->render_checkbox('table-allowHtml', 'ON', false)}Allow HTML inside cells</label>
					</div>
				</div>
HTML;
		}

		//--------------------------------------------------------------------------------------
		// shall we subtract scrollbar from visualization width? default true
		protected function shall_subtract_scrollbar() {
		//--------------------------------------------------------------------------------------
			return false;
		}

		//--------------------------------------------------------------------------------------
		protected function options() {
		//--------------------------------------------------------------------------------------
			return parent::options()  + array(
				'allowHtml' => ($this->page->get_post('table-allowHtml') === 'ON')
			);
		}

		//--------------------------------------------------------------------------------------
		// return google charts class name to instantiate
		public function class_name() {
		//--------------------------------------------------------------------------------------
			return 'google.visualization.Table';
		}

		//--------------------------------------------------------------------------------------
		// return google charts packages to include
		public function packages() {
		//--------------------------------------------------------------------------------------
			return array('table');
		}
	};
?>
