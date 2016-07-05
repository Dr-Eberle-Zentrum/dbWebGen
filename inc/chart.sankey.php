<?
	//==========================================================================================
	class dbWebGenChart_sankey extends dbWebGenChart_Google {
	//==========================================================================================
		// select (select name from users u where u.id=h.edit_user) "User", (select signature from documents d where d.id = h.id) "Document", count(*)::int "Edits" from documents_history h group by id, edit_user order by 3 desc limit 50
		
		//--------------------------------------------------------------------------------------
		public function settings_html() { 
		//--------------------------------------------------------------------------------------
			return <<<HTML
				<p>A sankey diagram is a visualization used to depict a flow (links) from one set of values (nodes) to another. Sankeys are best used when you want to show a many-to-many mapping between two domains or multiple paths through a set of stages.</p>
				<p><a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/sankey#data-format">Required columns</a>:
				<ul class='columns'>
					<li>1. Source node (string)</li>
					<li>2. Target node (string)</li>
					<li>3. Weight (number)</li>
				</ul>
				</p>
				
HTML;
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