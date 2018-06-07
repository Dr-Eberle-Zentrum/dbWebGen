<?php
	//==========================================================================================
	class FieldFactory
	//==========================================================================================
	{
		//--------------------------------------------------------------------------------------
		public static function create(
			$table_name,
			$field_name,
			$field = null // if not provided, will be feteched
		) {
		//--------------------------------------------------------------------------------------
			global $TABLES;

			if($field === null) {
				if(!isset($TABLES[$table_name]) || !isset($TABLES[$table_name]['fields'][$field_name]))
					return null;
				$field = $TABLES[$table_name]['fields'][$field_name];
			}

			switch($field['type']) {
				case T_ENUM: return new EnumField($table_name, $field_name, $field);
				case T_LOOKUP: return new LookupField($table_name, $field_name, $field);
				case T_UPLOAD: return new UploadField($table_name, $field_name, $field);
				case T_TEXT_LINE: return new TextLineField($table_name, $field_name, $field);
				case T_NUMBER: return new NumberField($table_name, $field_name, $field);
				case T_TEXT_AREA: return new TextAreaField($table_name, $field_name, $field);
				case T_PASSWORD: return new PasswordField($table_name, $field_name, $field);
				case T_POSTGIS_GEOM: return new PostgisGeomField($table_name, $field_name, $field);
				default: return null;
			}
		}
	}

	//==========================================================================================
	abstract class Field
	//==========================================================================================
	{
		protected /*string*/	$table_name;
		protected /*string*/	$field_name;
		protected /*array ref*/	$field;
		protected /*array ref*/	$table;
		protected /*array*/		$render_settings;

		//--------------------------------------------------------------------------------------
		public function __construct(
			$table_name,
			$field_name,
			&$field
		) {
		//--------------------------------------------------------------------------------------
			$this->table_name = $table_name;
			$this->field_name = $field_name;
			$this->field = $field;
			global $TABLES;
			$this->table = &$TABLES[$table_name];
			$this->init();
		}

		//--------------------------------------------------------------------------------------
		// override this to perform initializations; this is invoked by constructor
		public function init() {}
		//--------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------
		// whether to principally allow the set NULL check box next to the control, if the
		// settings permit. override in subclasses to disallow, if needed
		public function allow_setnull_box() {
		//--------------------------------------------------------------------------------------
			return true;
		}
		//--------------------------------------------------------------------------------------
		public function get_type() {
		//--------------------------------------------------------------------------------------
			return $this->field['type'];
		}
		//--------------------------------------------------------------------------------------
		public function get_label() {
		//--------------------------------------------------------------------------------------
			return $this->field['label'];
		}
		//--------------------------------------------------------------------------------------
		public function is_required() {
		//--------------------------------------------------------------------------------------
			return isset($this->field['required']) && $this->field['required'] === true;
		}
		//--------------------------------------------------------------------------------------
		public function get_required_attr() {
		//--------------------------------------------------------------------------------------
			return $this->is_required() ? 'required' : '';
		}
		//--------------------------------------------------------------------------------------
		public function is_disabled() { // default: false
		//--------------------------------------------------------------------------------------
			return isset($this->render_settings['disabled']) && $this->render_settings['disabled'] === true;
		}
		//--------------------------------------------------------------------------------------
		public function get_disabled_attr() {
		//--------------------------------------------------------------------------------------
			return $this->is_disabled() ? 'disabled readonly' : '';
		}
		//--------------------------------------------------------------------------------------
		public function has_focus() { // default: false
		//--------------------------------------------------------------------------------------
			return isset($this->render_settings['focus']) && $this->render_settings['focus'] === true;
		}
		//--------------------------------------------------------------------------------------
		public function get_focus_attr() {
		//--------------------------------------------------------------------------------------
			$this->has_focus() ? 'autofocus' : '';
		}
		//--------------------------------------------------------------------------------------
		public function get_control_name() { // default: field name
		//--------------------------------------------------------------------------------------
			return isset($this->render_settings['name_attr']) ? $this->render_settings['name_attr'] : $this->field_name;
		}
		//--------------------------------------------------------------------------------------
		public function get_control_id() { // default: field name
		//--------------------------------------------------------------------------------------
			return isset($this->render_settings['id_attr']) ? $this->render_settings['id_attr'] : $this->field_name;
		}
		//--------------------------------------------------------------------------------------
		public function get_form_method() { // default: POST
		//--------------------------------------------------------------------------------------
			return isset($this->render_settings['form_method']) ? strtoupper($this->render_settings['form_method']) : 'POST';
		}
		//--------------------------------------------------------------------------------------
		public function is_null_option_allowed() { // default: true
		//--------------------------------------------------------------------------------------
			return !isset($this->render_settings['null_option_allowed']) || $this->render_settings['null_option_allowed'] === true;
		}
		//--------------------------------------------------------------------------------------
		public function get_submitted_value($value_if_missing = null) {
		//--------------------------------------------------------------------------------------
			$n = $this->get_control_name();
			$a = $this->get_form_method() == 'POST' ? $_POST : $_GET;
			return isset($a[$n]) ? strval($a[$n]) : $value_if_missing;
		}
		//--------------------------------------------------------------------------------------
		public function has_submitted_value() {
		//--------------------------------------------------------------------------------------
			$a = $this->get_form_method() == 'POST' ? $_POST : $_GET;
			return isset($a[$this->get_control_name()]);
		}
		//--------------------------------------------------------------------------------------
		public function get_default_value($value_if_missing = null) {
		//--------------------------------------------------------------------------------------
			return isset($this->field['default']) ? strval($this->field['default']) : $value_if_missing;
		}
		//--------------------------------------------------------------------------------------
		public function has_fixed_column_width() {
		//--------------------------------------------------------------------------------------
			return isset($this->field['width_columns']);
		}
		//--------------------------------------------------------------------------------------
		public function get_fixed_column_width() {
		//--------------------------------------------------------------------------------------
			return $this->field['width_columns'];
		}
		//--------------------------------------------------------------------------------------
		public function get_width() {
		//--------------------------------------------------------------------------------------
			return $this->has_fixed_column_width() ? $this->get_fixed_column_width() : DEFAULT_FIELD_WIDTH;
		}
		//--------------------------------------------------------------------------------------
		public function get_show_setnull() { // default: false
		//--------------------------------------------------------------------------------------
			return isset($this->field['show_setnull']) && $this->field['show_setnull'] === true;
		}
		//--------------------------------------------------------------------------------------
		public function get_setnull_label() { // default: NULL
		//--------------------------------------------------------------------------------------
			global $APP;
			return isset($APP['null_label']) ? $APP['null_label'] : 'NULL';
		}
		//--------------------------------------------------------------------------------------
		public function has_custom_placeholder() {
		//--------------------------------------------------------------------------------------
			return isset($this->field['placeholder']) && is_string($this->field['placeholder']);
		}
		//--------------------------------------------------------------------------------------
		public function get_custom_placeholder($default = '') {
		//--------------------------------------------------------------------------------------
			return $this->has_custom_placeholder() ? $this->field['placeholder'] : $default;
		}

		//--------------------------------------------------------------------------------------
		public function /*string*/ render_control(
			array $render_settings = null,
			string &$output_buf = null
		) {
		//--------------------------------------------------------------------------------------
			$this->render_settings = ($render_settings === null ? array() : $render_settings);
			if($output_buf === null)
				$output_buf = '';
			$this->render_internal($output_buf);
			return $output_buf;
		}

		//------------------------------------------------------------------------------------------
		public function render_setnull_box(&$output_buf = null) {
		//------------------------------------------------------------------------------------------
			if($output_buf === null)
				$output_buf = '';

			if($this->allow_setnull_box()) {
				$post_val_key = sprintf('%s__null__', $this->get_control_name());
				$is_checked = !isset($_POST[$post_val_key]) || $_POST[$post_val_key] == 'true';
				$checked_attr = $is_checked? "checked='checked'" : '';
				$visibility = ($this->get_show_setnull() ? '' : 'invisible');

				$output_buf .= sprintf(
					"<div class='checkbox col-sm-1 %s'><label><input type='hidden' name='%s__null__' value='false' />".
					"<input id='%s__null__' name='%s__null__' type='checkbox' value='true' %s />%s</label></div>",
					$visibility, $this->get_control_name(), $this->get_control_name(), $this->get_control_name(), $checked_attr, $this->get_setnull_label()
				);
			}
			return $output_buf;
		}

		//--------------------------------------------------------------------------------------
		protected abstract function /*string*/ render_internal(&$output_buf);

		//--------------------------------------------------------------------------------------
		// whether or not to include this type of field in global search
		public abstract function /*bool*/ is_included_in_global_search();

		//--------------------------------------------------------------------------------------
		// expression used in sprintf(...) to fetch fields of this type. default: no transform.
		// override if needed (see e.g. T_POSTGIS_GEOM)
		public function /*string*/ sql_select_transformation() {
			return '%s';
		}

		//--------------------------------------------------------------------------------------
		public function /*string*/ get_global_search_condition(
			$param_name,
			$search_string_transformation,
			$table_qualifier = null)
		{
			return sprintf(
				db_cast_text('(%s)') . " like concat('%%', ". db_cast_text(':%s') .", '%%')",
				sprintf($search_string_transformation, db_esc($this->field_name, $table_qualifier)),
				$param_name
			);
		}
	}

	//==========================================================================================
	abstract class TextFieldBase extends Field
	//==========================================================================================
	{
		//--------------------------------------------------------------------------------------
		public function get_maxlen_attr() {
		//--------------------------------------------------------------------------------------
			return $this->has_char_length() ? sprintf("maxlength='%s'", $this->get_char_length()) : '';
		}

		//--------------------------------------------------------------------------------------
		public function has_char_length() {
		//--------------------------------------------------------------------------------------
			return isset($this->field['len']);
		}

		//--------------------------------------------------------------------------------------
		public function get_char_length($value_if_missing = -1) {
		//--------------------------------------------------------------------------------------
			return isset($this->field['len']) ? $this->field['len'] : $value_if_missing;
		}

		//--------------------------------------------------------------------------------------
		public function get_remaining_chars_display() {
		// note this is linked to init_remaining_chars_display() in dbweb.js and also css
		//--------------------------------------------------------------------------------------
			if(!$this->has_char_length() ||
				!isset($this->field['display_remaining_chars']) ||
				$this->field['display_remaining_chars'] !== true)
				return '';

			return sprintf(
				"<span class='help-block remaining-chars'><span data-control-id='%s'></span> %s</span>\n",
				$this->get_control_id(),
				l10n('text-field.remaining-chars')
			);
		}
	}

	//==========================================================================================
	abstract class SingleLineTextInputField extends TextFieldBase
	//==========================================================================================
	{
		protected $datetime_picker_main_icon = 'glyphicon glyphicon-calendar';

		//--------------------------------------------------------------------------------------
		public function init() {
		//--------------------------------------------------------------------------------------
			parent::init();

			// if datetime picker options set, check settings
			if($this->has_datetime_picker()) {
				$this->field['datetime_picker']['showClear'] = !$this->is_required();
				$this->field['datetime_picker']['locale'] = DBWEBGEN_LANG;
				if($this->has_submitted_value())
					$this->field['datetime_picker']['defaultDate'] = $this->get_submitted_value('');
				if(isset($this->field['datetime_picker']['mainIcon'])) {
					$this->datetime_picker_main_icon = $this->field['datetime_picker']['mainIcon'];
					unset($this->field['datetime_picker']['mainIcon']);
				}
			}
		}

		//------------------------------------------------------------------------------------------
		protected function get_input_size_class($max) {
		//------------------------------------------------------------------------------------------
			if(!$this->has_char_length() || ($len = $this->get_char_length()) > 56)	return min($max, 7);
			if($len > 46) return min($max, 6);
			if($len > 35) return min($max, 5);
			if($len > 25) return min($max, 4);
			if($len > 14) return min($max, 3);
			if($len > 4) return min($max, 2);
			return min($max, 1);
		}

		//--------------------------------------------------------------------------------------
		// override to adjust to length setting
		public function get_width() {
		//--------------------------------------------------------------------------------------
			$width = parent::get_width();
			if(!$this->has_fixed_column_width())
				$width = $this->get_input_size_class($width);
			return $width;
		}

		//--------------------------------------------------------------------------------------
		public function has_datetime_picker() {
		//--------------------------------------------------------------------------------------
			return isset($this->field['datetime_picker']) && is_array($this->field['datetime_picker']);
		}

		//--------------------------------------------------------------------------------------
		public function get_datetime_picker_options() {
		//--------------------------------------------------------------------------------------
			return $this->field['datetime_picker'];
		}

		//--------------------------------------------------------------------------------------
		public function get_datetime_picker_main_icon() {
		//--------------------------------------------------------------------------------------
			return $this->datetime_picker_main_icon;
		}
	}
?>
