<?php
    require_once 'record_renderer.php';
    require_once 'fields.php';

    //==========================================================================================
    class MergeRecordsPage extends dbWebGenPage
    //==========================================================================================
    {
        protected $table_name, $table;

        //--------------------------------------------------------------------------------------
        public function render() {
        //--------------------------------------------------------------------------------------
            global $TABLES;
            $this->table_name = $this->get_urlparam('table');
            if(!$this->table_name || !isset($TABLES[$this->table_name])) {
                proc_error('l10nize invalid table name');
                return '';
            }
            $this->table = $TABLES[$this->table_name];

            if($this->get_post('do_merge'))
                echo $this->do_merge();

            echo $this->display_form();
        }

        //--------------------------------------------------------------------------------------
        protected function get_primary_key_value($which) {
        //--------------------------------------------------------------------------------------
            $which = $which == 'master' ? 1 : 2;
            return $this->get_urlparam($which . '_' . $this->table['primary_key']['columns'][0]);
        }

        //--------------------------------------------------------------------------------------
        protected function get_id_cond($which, &$params, $field_name_override = null) {
        //--------------------------------------------------------------------------------------
            $which = $which == 'master' ? 1 : 2;
            $params = array();
            $conds = array();
            foreach($this->table['primary_key']['columns'] as $pk_col) {
                $params[] = $this->get_urlparam($which . '_' . $pk_col);
                $conds[] = sprintf('%s = ?', db_esc($field_name_override ? $field_name_override : $pk_col));
            }
            return '(' . implode(' AND ', $conds) . ')';
        }

        //--------------------------------------------------------------------------------------
        public function do_merge() {
        //--------------------------------------------------------------------------------------
            debug_log($_POST);
            global $TABLES;

            $master_id_cond = $this->get_id_cond('master', $master_params);
            $slave_id_cond = $this->get_id_cond('slave', $slave_params);

            foreach($_POST as $post_param => $choice) {
                if($post_param[0] != ':')
                    continue;
                $field_name = mb_substr($post_param, 1);
                $field = $this->table['fields'][$field_name];
                $field_obj = FieldFactory::create($this->table_name, $field_name, $field);

                if(is_array($choice) && count($choice) == 1)
                    $choice = $choice[0];

                if($choice === 'master')
                    continue; // nothing to do here

                $exec_params = $sql = null;

                // here we know that choice is either "slave" or ["slave", "master"]
                $action = 'replace'; // default action
                if(in_array($field_obj->get_type(), array(T_TEXT_LINE, T_TEXT_AREA)) && is_array($choice))
                    $action = 'append';
                else if($field_obj->get_type() == T_LOOKUP && $field_obj->get_cardinality() == CARDINALITY_MULTIPLE && is_array($choice))
                    $action = 'merge';

                $queries = array();
                switch($action) {
                    case 'replace':
                        if($field_obj->get_type() == T_LOOKUP && $field_obj->get_cardinality() == CARDINALITY_MULTIPLE) { // single lookup
                            // first delete master associations
                            $linkage = $field_obj->get_linkage_info();
                            $link_table_fk_cond = $this->get_id_cond(
                                'master',
                                $linkage_table_params,
                                $linkage['fk_self']
                            );
                            $queries[] = array(
                                'sql' => sprintf(
                                    "delete from %s where %s",
                                    db_esc($linkage['table']),
                                    $link_table_fk_cond
                                ),
                                'params' => $linkage_table_params
                            );
                            // then copy slave associations
                            $linkage['other_fields'] = '';
                            $other_fields = array();
                            if(isset($TABLES[$linkage['table']])) {
                                $linkage_table = $TABLES[$linkage['table']];
                                foreach($linkage_table['fields'] as $lf_name => $lf_settings) {
                                    if($lf_name != $linkage['fk_self'])
                                        $other_fields[] = db_esc($lf_name);
                                }
                            }
                            if(!in_array(db_esc($linkage['fk_other']), $other_fields))
                                $other_fields[] = db_esc($linkage['fk_other']);
                            if(count($other_fields))
                                $linkage['other_fields'] .= ', ' . implode(', ', $other_fields);
                            $queries[] = array(
                                'sql' => sprintf(
                                    "insert into %s (%s%s) select ?%s from %s where %s = ?",
                                    db_esc($linkage['table']),
                                    db_esc($linkage['fk_self']),
                                    $linkage['other_fields'],
                                    $linkage['other_fields'],
                                    db_esc($linkage['table']),
                                    db_esc($linkage['fk_self'])
                                ),
                                'params' => array(
                                    $this->get_primary_key_value('master'),
                                    $this->get_primary_key_value('slave')
                                )
                            );
                        }
                        else { // all others: simple replacement
                            $queries[] = array(
                                'sql' => sprintf(
                                    "update %s set %s = (select %s from %s where %s) where %s",
                                    db_esc($this->table_name),
                                    db_esc($field_name),
                                    db_esc($field_name),
                                    db_esc($this->table_name),
                                    $slave_id_cond,
                                    $master_id_cond
                                ),
                                'params' => array_merge($slave_params, $master_params)
                            );
                        }
                        break;

                    case 'append':
                        $queries[] = array(
                            'sql' => sprintf(
                                "update %s set %s = concat_ws(' ', %s, (select %s from %s where %s)) where %s",
                                db_esc($this->table_name),
                                db_esc($field_name),
                                db_esc($field_name),
                                db_esc($field_name),
                                db_esc($this->table_name),
                                $slave_id_cond,
                                $master_id_cond
                            ),
                            'params' => array_merge($slave_params, $master_params)
                        );
                        break;

                    case 'merge': // multiple lookup
                        $linkage = $field_obj->get_linkage_info();
                        $linkage['other_fields'] = '';
                        $other_fields = array();
                        if(isset($TABLES[$linkage['table']])) {
                            $linkage_table = $TABLES[$linkage['table']];
                            foreach($linkage_table['fields'] as $lf_name => $lf_settings) {
                                if($lf_name != $linkage['fk_self'])
                                    $other_fields[] = db_esc($lf_name);
                            }
                        }
                        if(!in_array(db_esc($linkage['fk_other']), $other_fields))
                            $other_fields[] = db_esc($linkage['fk_other']);
                        if(count($other_fields))
                            $linkage['other_fields'] .= ', ' . implode(', ', $other_fields);
                        $queries[] = array(
                            'sql' => sprintf(
                                "insert into %s (%s%s)
                                select ?%s
                                from %s
                                where %s = ?
                                and %s not in (
                                    select %s
                                    from %s
                                    where %s = ?
                                )",
                                db_esc($linkage['table']),
                                db_esc($linkage['fk_self']),
                                $linkage['other_fields'],
                                $linkage['other_fields'],
                                db_esc($linkage['table']),
                                db_esc($linkage['fk_self']),
                                db_esc($linkage['fk_other']),
                                db_esc($linkage['fk_other']),
                                db_esc($linkage['table']),
                                db_esc($linkage['fk_self'])
                            ),
                            'params' => array(
                                $this->get_primary_key_value('master'),
                                $this->get_primary_key_value('slave'),
                                $this->get_primary_key_value('master')
                            )
                        );
                        break;
                }

                $db = db_connect();
                foreach($queries as $query) {
                    debug_log($query['sql']);
                    debug_log($query['params']);
                    db_prep_exec($query['sql'], $query['params'], $stmt, $db);
                }
            }

            $ret = '';
            return $ret;
        }

        //--------------------------------------------------------------------------------------
        public function display_form() {
        //--------------------------------------------------------------------------------------
            $primary_keys = array();
            $where_conditions = array();
            $exec_params = array();
            for($i = 1; $i <= 2; $i++) {
                $conds = array();
                foreach($this->table['primary_key']['columns'] as $pk_col) {
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
            foreach($this->table['fields'] as $field_name => &$field) {
                $col_index++;
                $field_obj = FieldFactory::create($this->table_name, $field_name, $field);
                $display_fields[$field_name] = $field;
                $select_fields[] = sprintf($field_obj->sql_select_transformation(), db_esc($field_name, 't'));
                $control_type = 'checkbox';
                $check_both_default = false;
                if(in_array($field_name, $this->table['primary_key']['columns']))
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
                "SELECT %s, 1 ___master_slave_order___ FROM %s t WHERE %s
                UNION SELECT %s, 2 FROM %s t WHERE %s
                ORDER BY ___master_slave_order___",
                implode(', ', $select_fields),
                db_esc($this->table_name),
                $where_conditions[0],
                implode(', ', $select_fields),
                db_esc($this->table_name),
                $where_conditions[1]
            );

            db_prep_exec($sql, $exec_params, $stmt);
            $rr = new RecordRenderer($this->table_name, $this->table, $display_fields, $stmt, true, false, null);
            $merge_infos_js = json_encode($merge_infos);
            $ret = <<<HTML
                <style>
                    #master-slave-table input {
                        margin-right: 0.5em;
                    }
                </style>
                <h1>Merge</h1>
                <p>
                    In the table below, the record displayed in the second row (i.e. the <i>Slave</i> record) will be merged into the record displayed in the first row (i.e. the <i>Master</i> record). Review and adjust the selection boxes to define for each column which value shall be in the merged record. In case of two checked boxes for a column, the values of both rows will be merged (in case of multiple selection columns) or the slave value will be appended to the master value (in case of text values).
                </p>
                <form id='master-slave-table' class="col-sm-12" role="form" method="post">
                    {$rr->html()}
                    <div class="form-group">
                        <button type="submit" name="do_merge" value="1" class="btn btn-primary"><span class="glyphicon glyphicon-transfer"> Merge</button>
                    </div>
                </form>
                <script>
                    $(document).ready(function() {
                        var merge_infos = $merge_infos_js;
                        var table = $('#master-slave-table table tbody');
                        var master_row = table.find('tr:nth-child(1)').first();
                        var slave_row = table.find('tr:nth-child(2)').first();
                        for(var i = 0; i < merge_infos.length; i++) {
                            var mi = merge_infos[i];
                            if(mi.control_type == 'none')
                                continue;
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
                                    name: ':' + mi.field_name,
                                });
                                if(master_cell.text().length > 0)
                                    master_radio.attr('checked', '');
                                master_cell.prepend(master_radio);
                                var slave_radio = $('<input/>').attr({
                                    type: 'radio',
                                    value: 'slave',
                                    name: ':' + mi.field_name,
                                });
                                if(master_cell.text().length == 0)
                                    slave_radio.attr('checked', '');
                                slave_cell.prepend(slave_radio);
                            }
                            else if(mi.control_type == 'checkbox') {
                                var master_box =  $('<input/>').attr({
                                    type: 'checkbox',
                                    value: 'master',
                                    //id: mi.field_name,
                                    name: ':' + mi.field_name + '[]',
                                });
                                if(mi.check_both_default || master_cell.text().length > 0)
                                    master_box.attr('checked', '');
                                if(master_cell.text().length > 0 && slave_cell.text().length > 0)
                                    master_cell.prepend(master_box);
                                var slave_box =  $('<input/>').attr({
                                    type: 'checkbox',
                                    value: 'slave',
                                    //id: mi.field_name,
                                    name: ':' + mi.field_name + '[]',
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
            enable_delete($del);
            $ret .= $del;
            return $ret;
        }
    }
?>
