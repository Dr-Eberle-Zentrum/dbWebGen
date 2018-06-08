<?php
    require_once 'record_renderer.php';
    require_once 'fields.php';

    //==========================================================================================
    class MergeRecordsPage extends dbWebGenPage
    //==========================================================================================
    {
        //--------------------------------------------------------------------------------------
        public function render() {
        //--------------------------------------------------------------------------------------
            if($this->get_post('do_merge'))
                echo $this->do_merge();
            else
                echo $this->display_form();
        }

        //--------------------------------------------------------------------------------------
        public function display_form() {
        //--------------------------------------------------------------------------------------
            global $TABLES;
            $table_name = $this->get_urlparam('table');
            if(!$table_name || !isset($TABLES[$table_name])) {
                proc_error('l10nize invalid table name');
                return '';
            }
            $table = $TABLES[$table_name];
            $primary_keys = array();
            $where_conditions = array();
            $exec_params = array();
            for($i = 1; $i <= 2; $i++) {
                $conds = array();
                foreach($table['primary_key']['columns'] as $pk_col) {
                    $primary_keys[$i][$pk_col] = $this->get_urlparam($i.'_'.$pk_col);
                    $conds[] = sprintf('%s = ?', db_esc($pk_col, 't'));
                    $exec_params[] = $primary_keys[$i][$pk_col];
                }
                $where_conditions[] = '(' . implode(' AND ', $conds) . ')';
            }

            $ret = '';
            $select_fields = array();
            $display_fields = array();
            $col_index = 1;
            $merge_infos = array();
            foreach($table['fields'] as $field_name => &$field) {
                $col_index++;
                $field_obj = FieldFactory::create($table_name, $field_name, $field);
                $display_fields[$field_name] = $field;
                $select_fields[] = sprintf($field_obj->sql_select_transformation(), db_esc($field_name, 't'));
                $control_type = 'checkbox';
                $check_both_default = false;
                if(in_array($field_name, $table['primary_key']['columns']))
                    $control_type = 'none';
                else switch($field_obj->get_type()) {
                    case T_TEXT_AREA:
                        $check_both_default = true;
                        break;

                    case T_POSTGIS_GEOM:
                    case T_NUMBER:
                    case T_PASSWORD:
                    case T_ENUM:
                    case T_UPLOAD:
                        $control_type = 'radio';
                        break;

                    case T_LOOKUP:
                        if($field_obj->get_cardinality() == CARDINALITY_MULTIPLE)
                            $check_both_default = true;
                        else
                            $control_type = 'radio';
                        break;
                }

                $merge_infos[] = array(
                    'field_name' => $field_name,
                    'col_index' => $col_index,
                    'control_type' => $control_type,
                    'check_both_default' => $check_both_default
                );
            }

            $sql = sprintf(
                'SELECT %s FROM %s t WHERE %s',
                implode(', ', $select_fields),
                db_esc($table_name),
                implode(' OR ', $where_conditions)
            );
            #debug_log($sql);
            #debug_log($exec_params);

            db_prep_exec($sql, $exec_params, $stmt);
            $rr = new RecordRenderer($table_name, $table, $display_fields, $stmt, false, false, null);
            $merge_infos_js = json_encode($merge_infos);
            return <<<HTML
                <style>
                    #master-slave-table input {
                        margin-right: 0.5em;
                    }
                </style>
                <h1>Merge</h1>
                <p>
                    In the table below, the record displayed in the second row (i.e. the <i>Slave</i> record) will be merged into the record displayed in the first row (i.e. the <i>Master</i> record). Review and adjust the selection boxes to define for each column which value shall be in the merged record. In case of two checked boxes for a column, the values of both rows will be merged (in case of multiple selection columns) or the slave value will be appended to the master value (in case of text values).
                </p>
                <form id='master-slave-table' class="col-sm-12" role="form">
                    {$rr->html()}
                </form>
                <script>
                    $(document).ready(function() {
                        var merge_infos = $merge_infos_js;
                        var table = $('#master-slave-table table tbody');
                        var master_row = table.find('tr:nth-child(1)').first();
                        var slave_row = table.find('tr:nth-child(2)').first();
                        for(var i = 0; i <= merge_infos.length; i++) {
                            var mi = merge_infos[i];
                            if(mi.control_type == 'none')
                                continue;
                            // find cells
                            var master_cell = master_row.find('td:nth-child('+ mi.col_index +')').first();
                            var slave_cell = slave_row.find('td:nth-child('+ mi.col_index +')').first();
                            if(master_cell.text() == '' && slave_cell.text() == '')
                                continue;
                            if(master_cell.text() == slave_cell.text())
                                continue;
                            if(mi.control_type == 'radio') {
                                var master_radio = $('<input/>').attr({
                                    type: 'radio',
                                    value: 'master',
                                    name: mi.field_name,
                                });
                                if(master_cell.text().length > 0)
                                    master_radio.attr('checked', '');
                                master_cell.prepend(master_radio);
                                var slave_radio = $('<input/>').attr({
                                    type: 'radio',
                                    value: 'slave',
                                    name: mi.field_name,
                                });
                                if(master_cell.text().length == 0)
                                    slave_radio.attr('checked', '');
                                slave_cell.prepend(slave_radio);
                            }
                            else if(mi.control_type == 'checkbox') {
                                var master_box =  $('<input/>').attr({
                                    type: 'checkbox',
                                    value: 'master',
                                    name: mi.field_name,
                                });
                                if(mi.check_both_default || master_cell.text().length > 0)
                                    master_box.attr('checked', '');
                                if(master_cell.text().length > 0)
                                    master_cell.prepend(master_box);
                                var slave_box =  $('<input/>').attr({
                                    type: 'checkbox',
                                    value: 'slave',
                                    name: mi.field_name,
                                });
                                if(slave_cell.text().length > 0 && (mi.check_both_default || master_cell.text().length == 0))
                                    slave_box.attr('checked', '');
                                if(slave_cell.text().length > 0)
                                    slave_cell.prepend(slave_box);
                            }
                        }
                    })
                </script>

HTML;
            return $ret;
        }

        //--------------------------------------------------------------------------------------
        public function do_merge() {
        //--------------------------------------------------------------------------------------
            $s = '';
            return $s;
        }
    }
?>
