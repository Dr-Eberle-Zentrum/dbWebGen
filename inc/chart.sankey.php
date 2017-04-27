<?
	//==========================================================================================
	class dbWebGenChart_sankey extends dbWebGenChart_Google {
	//==========================================================================================
		// select (select name from users u where u.id=h.edit_user) "User", (select signature from documents d where d.id = h.id) "Document", count(*)::int "Edits" from documents_history h group by id, edit_user order by 3 desc limit 50

		// select (select name from users u where u.id=h.edit_user) "User", (select signature from documents d where d.id = h.id) "Document", count(*)::int "Edits" from documents_history h group by id, edit_user having count(*) > 0 order by 3 desc

		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() {
		//--------------------------------------------------------------------------------------
			return l10n('chart.sankey.settings');
		}

		//--------------------------------------------------------------------------------------
		protected function options() {
		//--------------------------------------------------------------------------------------
			return parent::options() + array(
			);
		}

		//--------------------------------------------------------------------------------------
		// return google charts class name to instantiate
		public function class_name() {
		//--------------------------------------------------------------------------------------
			return 'google.visualization.Sankey';
		}

		//--------------------------------------------------------------------------------------
		// return google charts packages to include
		public function packages() {
		//--------------------------------------------------------------------------------------
			return array('sankey');
		}
	};
?>
