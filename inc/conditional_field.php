<?php
    //==========================================================================================
    class ConditionalFieldDisplay {
    //==========================================================================================
        protected $field;
        protected $field_name;
        protected $expr;
        protected $is_conditional;
        protected static $expressions;
        protected static $controlled_fields;

        //------------------------------------------------------------------------------------------
        public static function render(&$table) {
        //------------------------------------------------------------------------------------------
            self::$expressions = array();
            self::$controlled_fields = array();
            foreach($table['fields'] as $field_name => &$field) {
                $cfd = new ConditionalFieldDisplay($field_name, $field);
                if($cfd->is_conditional()) {
                    self::$expressions[$cfd->get_field_name()] = $cfd->get_conditional_expression();
                    $controlled_fields = $cfd->get_controlling_fields();
                    foreach($controlled_fields as $controlling_field) {
                        if(!isset(self::$controlled_fields[$controlling_field]))
                            self::$controlled_fields[$controlling_field] = array();
                        self::$controlled_fields[$controlling_field] += array($cfd->get_field_name());
                    }
                }
            }
            if(count(self::$expressions) == 0)
                return ''; // nothing to do

            $controlled_fields = json_encode(self::$controlled_fields);
            $expressions = json_encode(self::$expressions);

            echo <<<JS
                <script>
                    //------------------------------------------------------------------------------------------
                    function cfd_eval_condition(conds, cur_result = false, start_index = 0, end_index = 0) {
                    //------------------------------------------------------------------------------------------
                        if(end_index == 0)
                            end_index = conds.length - 1;
                        if(conds[start_index] === 'OPERATOR_GROUP_OPEN') {
                            // find group close index and call recursively
                            for(var i = start_index + 1; i <= end_index; i++) {
                                if(conds[i] === 'OPERATOR_GROUP_CLOSE') {
                                    var sub_eval = cfd_eval_condition(conds, cur_result, start_index + 1, i - 1);
                                    if(i == end_index) // end reached
                                        return sub_eval;
                                    // combine this result with rest of expression
                                    var after_eval = cfd_eval_condition(conds, sub_eval, i + 2, end_index);
                                    if(conds[i + 1] === 'OPERATOR_AND')
                                        return sub_eval && after_eval;
                                    else
                                        return sub_eval || after_eval;
                                }
                            }
                        }
                        else if(typeof conds[start_index] === 'object') { // we have a real condition; this is the only option left
                            var cond = conds[start_index];
                            var field_val = $('[name]').filter(function() { return $(this).attr('name') == cond.field }).val();
                            var cond_result = false;
                            switch(cond.operator) {
                                case 'OPERATOR_EQUALS':
                                    // right hand side can be single value or set/array
                                    if($.isArray(cond.value)) {
                                        for(var i = 0; i < cond.value.length; i++) {
                                            if(field_val == cond.value[i]) {
                                                cond_result = true;
                                                break;
                                            }
                                        }
                                    }
                                    else
                                        cond_result = (field_val == cond.value);
                                    break;
                                case 'OPERATOR_NOT_EQUALS':
                                    //  right hand side can be single value or set/array
                                    if($.isArray(cond.value)) {
                                        cond_result = true;
                                        for(var i = 0; i < cond.value.length; i++) {
                                            if(field_val == cond.value[i]) {
                                                cond_result = false;
                                                break;
                                            }
                                        }
                                    }
                                    else
                                        cond_result = (field_val != cond.value);
                                    break;
                                case 'OPERATOR_BETWEEN':
                                    cond_result = (field_val >= cond.value[0] && field_val <= cond.value[1]);
                                    break;
                                case 'OPERATOR_GREATER':
                                    cond_result = (field_val > cond.value);
                                    break;
                                case 'OPERATOR_GREATER_OR_EQUALS':
                                    cond_result = (field_val >= cond.value);
                                    break;
                                case 'OPERATOR_LOWER':
                                    cond_result = (field_val < cond.value);
                                    break;
                                case 'OPERATOR_LOWER_OR_EQUALS':
                                    cond_result = (field_val <= cond.value);
                                    break;
                                default:
                                    if(cond.operator.startsWith('OPERATOR_ARRAY')) {
                                        if(field_val === '')
                                            field_val = "[]"; // need to fake empty array
                                        var arr;
                                        try {
                                            arr = JSON.parse(field_val);
                                            if(!arr || !$.isArray(arr))
                                                throw '';
                                        }
                                        catch(e) {
                                            console.error('Array operator "' + cond.operator + '" used when the field is not an array. Use only with CARDINALITY_MULTIPLE type T_LOOKUP fields!');
                                            break;
                                        }
                                        switch(cond.operator) {
                                            // indexOf does type sensitive search, we do findIndex for type-insensitive array search
                                            case 'OPERATOR_ARRAY_CONTAINS': cond_result = arr.findIndex(e => e == cond.value) >= 0; break;
                                            case 'OPERATOR_ARRAY_NOT_CONTAINS': cond_result = arr.findIndex(e => e == cond.value) == -1; break;
                                            case 'OPERATOR_ARRAY_SIZE_EQUALS': cond_result = arr.length == cond.value; break;
                                            case 'OPERATOR_ARRAY_SIZE_NOT_EQUALS': cond_result = arr.length != cond.value; break;
                                            case 'OPERATOR_ARRAY_SIZE_GREATER': cond_result = arr.length > cond.value; break;
                                            case 'OPERATOR_ARRAY_SIZE_GREATER_OR_EQUALS': cond_result = arr.length >= cond.value; break;
                                            case 'OPERATOR_ARRAY_SIZE_LOWER': cond_result = arr.length < cond.value; break;
                                            case 'OPERATOR_ARRAY_SIZE_LOWER_OR_EQUALS': cond_result = arr.length <= cond.value; break;
                                        }
                                        break;
                                    }
                                    // should not reach this
                                    console.error('Unknown operator "' + cond.operator + '" in conditional expression!');
                                    return cur_result;
                            }

                            if(start_index == end_index) // end reached
                                return cond_result;

                            var eval_after = cfd_eval_condition(conds, cond_result, start_index + 2, end_index);
                            if(conds[start_index + 1] === 'OPERATOR_AND')
                                return cond_result && eval_after;
                            return cond_result || eval_after;
                        }
                        console.error('Evaluation of conditional field display expression reached a point that should not be reached. There is something wrong with the following settings:');
                        console.log(conds);
                        return cur_result; // shall we even reach this?
                    }

                    //------------------------------------------------------------------------------------------
                    $(document).ready(function() {
                        var controlled_fields = $controlled_fields;
                        var expressions = $expressions;

                        // attach change event handlers to controlling fields
                        for(var cf in controlled_fields) {
                            if(!controlled_fields.hasOwnProperty(cf))
                                continue;
                            $('form [name]').filter(function() { return $(this).attr('name') == cf }).on('change', function() {
                                var cf_arr = controlled_fields[cf];
                                for(var i = 0; i < cf_arr.length; i++) {
                                    var cond_result = cfd_eval_condition(expressions[cf_arr[i]]);
                                    var cf_ctrl = $('form [name]').filter(function() { return $(this).attr('name') == cf_arr[i] }).parents('div.form-group');
                                    if(cond_result == false && cf_ctrl.is(':visible'))
                                        cf_ctrl.fadeOut();
                                    else if(cond_result == true && !cf_ctrl.is(':visible'))
                                        cf_ctrl.fadeIn();
                                }
                            });
                        }

                        // initially evaluate all expressions for conditional fields
                        for(var f in expressions) {
                            if(!expressions.hasOwnProperty(f))
                                continue;
                            if(!cfd_eval_condition(expressions[f]))
                                $('form [name]').filter(function() { return $(this).attr('name') == f }).parents('div.form-group').hide();
                        }
                    });
                </script>
JS;
        }

        //------------------------------------------------------------------------------------------
        protected function __construct($field_name, &$field) {
        //------------------------------------------------------------------------------------------
            $this->field = $field;
            $this->field_name = $field_name;
            $this->is_conditional = isset($field['conditional_display']);
            if($this->is_conditional)
                $this->expr = $field['conditional_display'];
        }

        //------------------------------------------------------------------------------------------
        protected function get_field_name() {
        //------------------------------------------------------------------------------------------
            return $this->field_name;
        }

        //------------------------------------------------------------------------------------------
        protected function get_controlling_fields() {
        //------------------------------------------------------------------------------------------
            $fields = array();
            foreach($this->expr as &$a) {
                if(is_array($a) && isset($a['field']))
                    $fields[] = $a['field'];
            }
            return $fields;
        }

        //------------------------------------------------------------------------------------------
        protected function get_conditional_expression() {
        //------------------------------------------------------------------------------------------
            return $this->expr;
        }

        //------------------------------------------------------------------------------------------
        protected function is_conditional() {
        //------------------------------------------------------------------------------------------
            return $this->is_conditional;
        }
    }
?>
