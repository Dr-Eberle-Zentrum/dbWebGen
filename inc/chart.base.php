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
		public function get_param($param_name, $default = null) {
		//--------------------------------------------------------------------------------------
			return $this->page->get_post($this->ctrlname($param_name), $default);
		}

		//--------------------------------------------------------------------------------------
		public function get_param_checkbox($param_name, $default = false) {
		//--------------------------------------------------------------------------------------
			return 'ON' == $this->page->get_post($this->ctrlname($param_name), $default ? 'ON' : 'OFF');
		}

		//--------------------------------------------------------------------------------------
		// this function must be used in all settings forms to generate names for controls,
		// because the chart type name is used for storing options in the DB for stored queries
		public function ctrlname($field) {
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
			if(!$this->page->is_cache_enabled() || !$this->get_param_checkbox('caching', false) || isset($_GET['nocache']))
				return false;
			$query_id = $this->page->get_stored_query_id();
			$dir = $this->cache_get_dir();
			// read cache
			$t = @filemtime($dir . '/' . $query_id . '.html');
			if($t === false) // probably does not exist yet
				return false;
			if(time() - $t > $this->cache_get_ttl()) // cache expired
				return false;
			$cache = @file_get_contents($dir . '/' . $query_id . '.html');
			if($cache === false)
				return false;
			// check version
			if(preg_match('/^<!-- (?P<ver>\d+) -->\n/', $cache, $matches) !== 1)
				return false; // can't find version info
			if(!isset($matches['ver']))
				return false;
			if(intval($matches['ver']) < $this->cache_get_version())
				return false; // this code is newer version -> don't return cache
			return $cache;
		}

		//--------------------------------------------------------------------------------------
		// store cached js of visualization; true on success, else false
		public /*bool*/ function cache_put_js($js) {
		//--------------------------------------------------------------------------------------
			if(!$this->page->is_cache_enabled() || !$this->get_param_checkbox('caching', false) || isset($_GET['nocache']))
				return false;

			$query_id = $this->page->get_stored_query_id();
			$dir = $this->cache_get_dir();

			if(!@is_dir($dir)) {
				if(!@mkdir($dir, 0777, true)) {
					$error = error_get_last();
					proc_error($error['message']);
				}
			}

			// make version
			$version = "<!-- " . $this->cache_get_version() . " -->\n";
			return @file_put_contents($dir . '/' . $query_id . '.html', $version . $js);
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
		// returns the time to live of the cache
		public /*int*/ function cache_get_ttl() {
		//--------------------------------------------------------------------------------------
			return $this->page->get_post($this->ctrlname('cache_ttl'), DEFAULT_CACHE_TTL);
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
