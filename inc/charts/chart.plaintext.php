<?php
	//==========================================================================================
	class dbWebGenChart_plaintext extends dbWebGenChart {
	//==========================================================================================
		//--------------------------------------------------------------------------------------
		// returns html form for chart settings
		public /*string*/ function settings_html() {
		//--------------------------------------------------------------------------------------
			return l10n('chart.plaintext.settings');
		}

		//--------------------------------------------------------------------------------------
		// override if additional scripts are needed for this type
		public /*void*/ function add_required_scripts() {
		//--------------------------------------------------------------------------------------
		}

        //--------------------------------------------------------------------------------------
		// override this to return true if the chart renders plaintext only
		public /*bool*/ function is_plaintext() {
		//--------------------------------------------------------------------------------------
            return true;
		}

		//--------------------------------------------------------------------------------------
		// returns html/js to render page
		public /*string*/ function get_js(/*PDOStatement*/ $query_result) {
		//--------------------------------------------------------------------------------------
            $row = $query_result->fetch(PDO::FETCH_NUM);
            if($row === false)
                return l10n('error.db-execute');
            return (string) $row[0];
		}
	};
?>
