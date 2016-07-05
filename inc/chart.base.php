<?
	//==========================================================================================
	abstract class dbWebGenChart {
	//==========================================================================================
		protected $page;
		
		//--------------------------------------------------------------------------------------
		public function __construct($page) {
		//--------------------------------------------------------------------------------------
			$this->page = $page;
		}
		
		//--------------------------------------------------------------------------------------
		// returns a chart instance based on the chart type
		public static function create($chart_type, $page) {
		//--------------------------------------------------------------------------------------
			$class_name = 'dbWebGenChart_' . $chart_type;
			return new $class_name($page);
		}
		
		// returns html form for chart settings
		abstract public /*string*/ function settings_html();
		
		// override if additional scripts are needed for this type
		abstract public /*void*/ function add_required_scripts();
		
		// returns js code to fill the chart div
		abstract public /*string*/ function get_js(/*PDOStatement*/ $query_result);
	};
?>