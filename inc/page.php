<?
	//==========================================================================================
	abstract class dbWebGenPage {
	//==========================================================================================
		
		
		//--------------------------------------------------------------------------------------
		public function __construct() {
		//--------------------------------------------------------------------------------------			
		}
		
		//--------------------------------------------------------------------------------------
		protected function get_post($name, $default = null) {
		//--------------------------------------------------------------------------------------			
			return isset($_POST[$name]) ? $_POST[$name] : $default;				
		}
		
		//--------------------------------------------------------------------------------------
		protected function render_select($name, $default_value, $options) {
		//--------------------------------------------------------------------------------------			
			$html = "<select id='$name' name='$name'>\n";
			foreach($options as $value => $label) {
				$is_checked = (!$this->has_post_values() && $value == $default_value) 
					|| $this->get_post($name) == $value;					
				$checked_attr = $is_checked ? 'selected' : '';			
				$html .= "\n<option value='$value' $checked_attr>$label</option>";
			}
			$html .= "\n</select>";
			return $html;
		}
		
		//--------------------------------------------------------------------------------------
		protected function render_radio($name, $value, $checked_default = false) {
		//--------------------------------------------------------------------------------------			
			$is_checked = (!$this->has_post_values() && $checked_default) 
				|| $this->get_post($name) == $value;
				
			$checked_attr = $is_checked ? 'checked' : '';			
			return "<input type='radio' value='$value' name='$name' $checked_attr>";
		}
		
		//--------------------------------------------------------------------------------------
		protected function has_post_values() {
		//--------------------------------------------------------------------------------------			
			return count($_POST) > 0;
		}
	
		//--------------------------------------------------------------------------------------
		// abstract functions
		//--------------------------------------------------------------------------------------		
		abstract public function render();
	};	
?>