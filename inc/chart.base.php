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
		// this function must be used in all settings forms to generate names for controls,
		// because the chart type name is used for storing options in the DB for stored queries
		protected function ctrlname($field) {
		//--------------------------------------------------------------------------------------
			// e.g. if the classname is 'dbWebGenChart_foobar', it will return "foobar-$field"
			return "{$this->type}-$field";
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

		//--------------------------------------------------------------------------------------
		// returns cached js for visualization, or false if no cache exists
		public /*string | false*/ function cache_get_js() {
		//-------------------------------------------------------------------------------------
			return false;
		}

		//--------------------------------------------------------------------------------------
		// store cached js of visualization; true on success, else false
		public /*bool*/ function cache_put_js($js) {
		//--------------------------------------------------------------------------------------
			return false;
		}

		//--------------------------------------------------------------------------------------
		// returns the chart code version. this is only used for caching.
		// if this code is newer version than the cached version, the cache is emptied.
		// default version = 1; override and increment to ignore any existing cache
		public /*int*/ function cache_get_version() {
		//-------------------------------------------------------------------------------------
			return 1;
		}

		//--------------------------------------------------------------------------------------
		// returns the time to live of the cache. default 1 hour. override to change.
		public /*int*/ function cache_get_ttl() {
		//--------------------------------------------------------------------------------------
			global $APP;
			if(!isset($APP['cache_ttl']))
				return 3600;
			return typeof $APP['cache_ttl'] === 'function' ? $APP['cache_ttl']($this->type) : $APP['cache_ttl'];
		}

		//--------------------------------------------------------------------------------------
		// returns the cache directory
		public /*string*/ function cache_get_dir($val_if_missing = null) {
		//--------------------------------------------------------------------------------------
			global $APP;
			if(!isset($APP['cache_dir']))
				return $val_if_missing;
			return $APP['cache_dir'] . '/' . $this->type;
		}
	};
?>
