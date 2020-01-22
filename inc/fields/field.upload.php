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
		// during edit mode, determines whether remote delete of existing file is allowed
		public function allow_remote_delete() {
		//--------------------------------------------------------------------------------------
			return !isset($this->field['allow_remote_delete']) // default: true
				|| $this->field['allow_remote_delete'] === true;
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
			if($_GET['mode'] == MODE_EDIT) {
				$output_buf .= '<span class="help-block">' . l10n('upload-field.hint-empty') . '</span>';
				if(!$this->is_required() && $this->get_submitted_value('') !== '' && $this->allow_remote_delete()) {
					// this control's attributes are used in dbweb.js in init_file_selection_handler() and in new_edit.php ---> be careful!!!
					$output_buf .= sprintf(
						'<span id="%s__remove_file_container" class="help-block top-margin-zero" ><input type="checkbox" value="remove" id="%s__remove_file" name="%s__remove_file"> %s</span>',
						$this->get_control_id(),
						$this->get_control_id(),
						$this->get_control_name(),
						l10n('upload-field.remove-existing-file', html($this->get_submitted_value()))
					);
				}
			}
			return $output_buf;
		}

		//--------------------------------------------------------------------------------------
		public function /*bool*/ is_included_in_global_search() {
		//--------------------------------------------------------------------------------------
			return true;
		}
	}
?>
