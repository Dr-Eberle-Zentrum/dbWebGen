<?
	//==========================================================================================
	class ChartFactory {
	//==========================================================================================
		public static function get($chart_type, $page) {
			$class_name = 'dbWebGen_chart_' . $chart_type;
			return new $class_name($page);
		}
	};
	
	//==========================================================================================
	abstract class dbWebGen_chart_base {
	//==========================================================================================
		protected $page;
		
		public function __construct($page) {
			$this->page = $page;
		}
		
		public function settings_html() { 
			return ''; 
		}
		
		public function options_js() {
			return json_encode($this->options());
		}
		
		public function packages_js() {
			return json_encode($this->packages());
		}
		
		// any default options. call this from subclasses, then add to default array
		protected function options() {			
			return array();
		}
		
		// any js to be rendered before the actual draw() call.
		public function before_draw_js() {
			return '';
		}
		
		// return google charts class name to instantiate
		abstract public function class_name();
		
		// return google charts packages to include
		abstract protected function packages();
	};
?>