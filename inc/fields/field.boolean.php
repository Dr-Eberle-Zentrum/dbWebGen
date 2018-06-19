<?php
    //==========================================================================================
    class BooleanField extends SingleLineTextInputField
    //==========================================================================================
    {
        const ON = 'on';
        const OFF = 'off';

        //--------------------------------------------------------------------------------------
		// whether to principally allow the set NULL check box next to the control, if the
		// settings permit. override in subclasses to disallow, if needed
		public function allow_setnull_box() {
		//--------------------------------------------------------------------------------------
			return false;
		}

        //--------------------------------------------------------------------------------------
		public function is_null_option_allowed() { // override
		//--------------------------------------------------------------------------------------
			return false;
		}

        //--------------------------------------------------------------------------------------
		public function get_show_setnull() { // default: false
		//--------------------------------------------------------------------------------------
			return false;
		}

        //--------------------------------------------------------------------------------------
        public function get_init_options() {
        //--------------------------------------------------------------------------------------
            return $this->field['options'];
        }

        //--------------------------------------------------------------------------------------
        public function has_init_options() {
        //--------------------------------------------------------------------------------------
            return isset($this->field['options']);
        }

        //--------------------------------------------------------------------------------------
        public function get_display_value($onoff) {
        //--------------------------------------------------------------------------------------
            $default = $onoff == self::ON ? l10n('boolean-field.default.yes') : l10n('boolean-field.default.no');
            if(!$this->has_init_options())
                return $default;
            $opt = $this->get_init_options();
            return isset($opt[$onoff]) ? $opt[$onoff] : $default;
        }

        //--------------------------------------------------------------------------------------
        public function get_toggle_status_from_custom_value($db_val) {
        //--------------------------------------------------------------------------------------
            foreach($this->field['values'] as $onoff => $custom_val)
                if($custom_val === $db_val)
                    return $onoff;
            return $this->get_default_value(BooleanField::OFF); // not sure whether this fallback makes sense in all cases
        }

        //--------------------------------------------------------------------------------------
        public function get_custom_value($onoff) {
        //--------------------------------------------------------------------------------------
            return $this->field['values'][$onoff];
        }

        //--------------------------------------------------------------------------------------
        public function has_custom_values() {
        //--------------------------------------------------------------------------------------
            return isset($this->field['values']);
        }

        //--------------------------------------------------------------------------------------
        protected function /*string*/ render_internal(&$output_buf) {
        //--------------------------------------------------------------------------------------
            $output_buf .= sprintf(
                "<input type='hidden' value='%s' name='%s' />
                <input %s %s type='checkbox' class='form-control' id='%s' value='%s' name='%s' %s title='%s' />",
                self::OFF,
                $this->get_control_name(),
                $this->get_disabled_attr(),
                $this->get_required_attr(),
                $this->get_control_id(),
                self::ON,
                $this->get_control_name(),
                $this->get_focus_attr(),
                unquote($this->get_label())
            );

            $options = array(
                'on' => l10n('boolean-field.default.yes'),
                'off' => l10n('boolean-field.default.no')
            );
            if($this->has_init_options())
                $options = array_merge($options, $this->get_init_options());
            $options = json_encode($options);

            if($this->has_submitted_value())
                $is_on = $this->get_submitted_value() == self::ON;
            else
                $is_on = $this->get_default_value(self::OFF) == self::ON;
            $is_on = json_encode($is_on);

            $output_buf .= <<<JS
                <script>
                    $(function() {
                        var toggle = $('#{$this->get_control_id()}').bootstrapToggle($options);
                        if($is_on) toggle.prop('checked', true).change();
                    });
                </script>
JS;
        }

        //--------------------------------------------------------------------------------------
        public function /*bool*/ is_included_in_global_search() {
        //--------------------------------------------------------------------------------------
            return false;
        }
    }
?>
