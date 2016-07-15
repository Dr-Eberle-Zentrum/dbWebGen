<?
	//==========================================================================================
	abstract class dbWebGenChart {
	//==========================================================================================
		protected $page;	
		protected $type;		
		
		//--------------------------------------------------------------------------------------
		public function __construct($type, $page) {
		//--------------------------------------------------------------------------------------
			$this->page = $page;		
			$this->type = $type;
		}
		
		//--------------------------------------------------------------------------------------
		public function type() {
		//--------------------------------------------------------------------------------------
			return $this->type;
		}
		
		//--------------------------------------------------------------------------------------
		// returns a chart instance based on the chart type
		public static function create($chart_type, $page) {
		//--------------------------------------------------------------------------------------
			$class_name = 'dbWebGenChart_' . $chart_type;
			return new $class_name($chart_type, $page);
		}
		
		// returns html form for chart settings
		// form field @name must be prefixed with exact charttype followed by dash
		abstract public /*string*/ function settings_html();
		
		// override if additional scripts are needed for this type
		abstract public /*void*/ function add_required_scripts();
		
		// returns js code to fill the chart div
		abstract public /*string*/ function get_js(/*PDOStatement*/ $query_result);
	};
?>