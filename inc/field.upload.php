<?php
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
		public function is_required() {
		//--------------------------------------------------------------------------------------
			return $_GET['mode'] == MODE_EDIT ? false : parent::is_required(); // issue #20
		}

		//--------------------------------------------------------------------------------------
		protected function /*string*/ render_internal(&$output_buf) {
		// render_settings: form_method, name_attr, id_attr
		//--------------------------------------------------------------------------------------
			$output_buf .= sprintf(
				"<span class='btn btn-default btn-file file-input'><span class='glyphicon glyphicon-search'></span> %s <input %s %s data-text='%s_text' type='file' id='%s' name='%s' /></span><span class='filename' id='%s_text'></span>",
				l10n('upload-field.browse'),
				$this->get_disabled_attr(),
				$this->get_required_attr(),
				$this->get_control_name(),
				$this->get_control_id(),
				$this->get_control_name(),
				$this->get_control_name()
			);
			if($_GET['mode'] == MODE_EDIT)
				$output_buf .= '<span class="help-block">' . l10n('upload-field.hint-empty') . '</span>';
			return $output_buf;
		}

		//--------------------------------------------------------------------------------------
		public function /*bool*/ is_included_in_global_search() {
		//--------------------------------------------------------------------------------------
			return true;
		}
	}
?>
