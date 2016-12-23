<?
	//==========================================================================================
	class UploadField extends Field
	//==========================================================================================
	{
		//--------------------------------------------------------------------------------------
		// override parent
		public function allow_setnull_box() {
		//--------------------------------------------------------------------------------------
			return false;
		}
		
		//--------------------------------------------------------------------------------------		
		protected function /*string*/ render_internal(&$output_buf) {
		// render_settings: form_method, name_attr, id_attr
		//--------------------------------------------------------------------------------------
			$output_buf .= sprintf(
				"<span class='btn btn-default btn-file file-input'>Browse <input %s %s data-text='%s_text' type='file' id='%s' name='%s' /></span><span class='filename' id='%s_text'></span>",
				$this->get_disabled_attr(), 
				$this->get_required_attr(), 
				$this->get_control_name(),
				$this->get_control_id(), 
				$this->get_control_name(),
				$this->get_control_name()
			);			
			return $output_buf;
		}
	}
?>