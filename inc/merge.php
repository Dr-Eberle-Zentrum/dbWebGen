<?php
    require_once 'record_renderer.php';
    require_once 'fields/fields.php';

    //==========================================================================================
    class MergeRecordsPage extends dbWebGenPage
    //==========================================================================================
    {
        protected $table_name, $table, $merge_success = false;
        protected $is_table_relevant_for_merge_func = null;
        protected $db;

        //--------------------------------------------------------------------------------------
        public function render() {
        //--------------------------------------------------------------------------------------
            global $TABLES;
            global $APP;
            if(isset($APP['is_table_relevant_for_merge_func']) && function_exists($APP['is_table_relevant_for_merge_func']))
                $this->is_table_relevant_for_merge_func = $APP['is_table_relevant_for_merge_func'];
            $this->db = db_connect();
            $this->table_name = $this->get_urlparam('table');
            if(!$this->table_name || !isset($TABLES[$this->table_name]))
                return proc_error(l10n('error.invalid-table', $this->table_name ? $this->table_name : ''));
            $this->table = $TABLES[$this->table_name];
            if(!is_allowed($this->table, MODE_MERGE))
                return proc_error(l10n('error.not-allowed'));
            if($this->get_post('do_merge'))
                echo $this->do_merge();
            echo $this->display_form();
        }

        //--------------------------------------------------------------------------------------
        protected function is_relevant_referenced_table($referenced_table) {
        //--------------------------------------------------------------------------------------
            $func = $this->is_table_relevant_for_merge_func;
            return !$func || $func($this->table_name, $referenced_table);
        }

        //--------------------------------------------------------------------------------------
        public static function get_push_key_values(&$table, &$keys) {
        //--------------------------------------------------------------------------------------
            $keys = array();
            foreach($table['primary_key']['columns'] as $col_name) {
                if(!get_request('key_' . $col_name, $key))
                    return proc_error(l10n('error.invalid-params'));
                $keys[$col_name] = $key;
            }
            return true;
        }

        //--------------------------------------------------------------------------------------
        public static function get_merge_button_js() {
        //--------------------------------------------------------------------------------------
            return <<<JS
                <script>
                    $(document).ready(function() {
                        $('[data-merge-push]').click(function() {
                            $.get($(this).data('merge-push'), function(data) {
                                location.reload();
                            });
                        });
                    });
                </script>
JS;
        }

        //--------------------------------------------------------------------------------------
        public static function get_cancel_button_js() {
        //--------------------------------------------------------------------------------------
            return <<<JS
                <script>
                    $(document).ready(function() {
                        $('[data-merge-reset]').click(function() {
                            $.get($(this).data('merge-reset'), function(data) {
                                if(data != '')
                                    window.location = data;
                                else
                                    location.reload;
                            });
                        });
                    });
                </script>
JS;
        }

        //--------------------------------------------------------------------------------------
        public static function process_ajax() {
        //--------------------------------------------------------------------------------------
            get_session_var('merge', $merge_info);
            if(!isset($_GET['action']))
                return false;
            if($_GET['action'] == 'push') {
                MergeRecordsPage::push_record();
                return true;
            }
            if($_GET['action'] == 'reset') {
                MergeRecordsPage::reset();
                return true;
            }
            if($_GET['action'] == 'debug') {
                MergeRecordsPage::debug();
                return true;
            }
            return false;
        }

        //--------------------------------------------------------------------------------------
        public static function push_record() {
        //--------------------------------------------------------------------------------------
            global $TABLES;
            header('Content-Type: text/plain; charset=utf-8');
            if(!get_request('table', $table_name))
                return proc_error(l10n('error.invalid-params'));
            if(!isset($TABLES[$table_name]))
                return proc_error(l10n('error.invalid-table', $table_name));
            $table = $TABLES[$table_name];
            if(!is_allowed($table, MODE_MERGE))
                return proc_error(l10n('error.not-allowed'));
            if(get_session_var('merge', $merge_info) && $merge_info !== null) {
                // second merge click
                if(!MergeRecordsPage::get_push_key_values($table, $keys))
                    return false;
                // check if same table as before. if so, push as second and redirect to table
                if($merge_info[0]['table'] == $table_name) {
                    if(count($merge_info) > 1) {
                        // through weird user navigation more than 2 records can accumulate. Clear those first.
                        array_splice($_SESSION['merge'], 1);
                    }
                    $_SESSION['merge'][] = array(
                        'keys' => $keys,
                        'view_url' => '?' . http_build_query(array(
                            'table' => $table_name,
                            'mode' => MODE_VIEW
                        )) . '&' . http_build_query($keys)
                    );
                    $url_params = array(
                        'mode' => MODE_MERGE,
                        'table' => $table_name
                    );
                    for($i = 0; $i < 2; $i++) {
                        foreach($_SESSION['merge'][$i]['keys'] as $col => $val)
                            $url_params[($i + 1) . '_' . $col] = $val;
                    }
                    $_SESSION['redirect'] = '?' . http_build_query($url_params);
                    return true;
                }
                // fall through to push as first...
            }

            // first merge click
            if(!MergeRecordsPage::get_push_key_values($table, $keys))
                return false;
            $_SESSION['merge'] = array(
                array(
                    'table' => $table_name,
                    'keys' => $keys,
                    'view_url' => '?' . http_build_query(array(
                        'table' => $table_name,
                        'mode' => MODE_VIEW
                    )) . '&' . http_build_query($keys)
                )
            );
            proc_info(l10n('merge.record-pushed', $table['item_name']));
            return true;
        }

        //--------------------------------------------------------------------------------------
        public static function reset() {
        //--------------------------------------------------------------------------------------
            header('Content-Type: text/plain; charset=utf-8');
            if(get_session_var('merge', $merge_info) && $merge_info !== null) {
                $i = count($merge_info) == 2 ? 1 : 0;
                echo $merge_info[$i]['view_url'];
            }
            $_SESSION['merge'] = null;
            return true;
        }

        //--------------------------------------------------------------------------------------
        public static function debug() {
        //--------------------------------------------------------------------------------------
            header('Content-Type: text/plain; charset=utf-8');
            if(get_session_var('merge', $merge_info) && $merge_info !== null)
                var_dump($merge_info);
            else
                echo 'No records selected for merge.';
            return true;
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
            global $TABLES;

            $master_id_cond = $this->get_id_cond('master', $master_params);
            $slave_id_cond = $this->get_id_cond('slave', $slave_params);

            $queries = array();
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
            }

            if(isset($_POST['rewire-references']) && $_POST['rewire-references'] == 1) {
                $delete_slave_reference_if_master_reference_already_exists = isset($_POST['rewire-delete-slave']) && $_POST['rewire-delete-slave'] == 1;
                $related_records = $this->get_related_records_of_slave();
                foreach($related_records as $table_name => $related) {
                    foreach($related['records'] as $referencing_record) {
                        // check whether the foreign key is part of a composite primary key;
                        // if so, we need to check whether the new foreign key (to the master)
                        // does not already exist
                        $table_pk = $TABLES[$table_name]['primary_key']['columns'];
                        if(count($table_pk) > 1 && in_array($related['fk_field'], $table_pk)) {
                            $where_conds = array();
                            $params = array();
                            foreach($table_pk as $pk) {
                                $where_conds[] = sprintf("%s = ?", db_esc($pk));
                                if($pk == $related['fk_field'])
                                    $params[] = $this->get_primary_key_value('master');
                                else
                                    $params[] = $referencing_record[$pk];
                            }
                            $sql = sprintf(
                                'select count(*) from %s where %s',
                                db_esc($table_name),
                                join(' and ', $where_conds)
                            );
                            #debug_log($sql, $params);
                            if(db_get_single_val($sql, $params, $count, $this->db) && $count > 0) {
                                // the master is already referenced
                                if(!$delete_slave_reference_if_master_reference_already_exists)
                                    continue;
                                // delete record
                                $where_conds = array();
                                $params = array();
                                foreach($referencing_record as $key_name => $key_val) {
                                    $where_conds[] = sprintf('%s = ?', db_esc($key_name));
                                    $params[] = $key_val;
                                }
                                $queries[] = array(
                                    'sql' => sprintf(
                                        'delete from %s where %s',
                                        db_esc($table_name),
                                        join(' and ', $where_conds)
                                    ),
                                    'params' => $params
                                );
                                continue;
                            }
                        }
                        $where_conds = array();
                        $params = array();
                        foreach($referencing_record as $key_name => $key_val) {
                            $where_conds[] = sprintf('%s = ?', db_esc($key_name));
                            $params[] = $key_val;
                        }
                        $queries[] = array(
                            'sql' => sprintf(
                                'update %s set %s = ? where %s',
                                db_esc($table_name),
                                db_esc($related['fk_field']),
                                join(' and ', $where_conds)
                            ),
                            'params' => array_merge(
                                array($this->get_primary_key_value('master')),
                                $params
                            )
                        );
                    }
                }
            }

            #debug_log($queries);
            #return;

            if(count($queries) == 0) {
                proc_info(l10n('merge.nothing-to-do'));
                return '';
            }
            $begin_trans = false;
            try {
                $begin_trans = $this->db->beginTransaction();
            } catch(Exception $e) {
            }
            foreach($queries as $query) {
                if(!db_prep_exec($query['sql'], $query['params'], $stmt, $this->db)) {
                    try {
                        if($begin_trans && $this->db->rollBack()) {
                            proc_info(l10n('merge.info-rollback'));
                        }
                    } catch(Exception $e) {
                    }
                    return '';
                }
            }
            try {
                if($begin_trans && !$this->db->commit())
                    throw new Exception('');
            } catch(Exception $e) {
                proc_error(l10n('merge.fail'));
                return '';
            }

            proc_success(l10n('merge.success'));
            unset($_SESSION['merge']);
            $this->merge_success = true;
            return '';
        }

        //--------------------------------------------------------------------------------------
        protected function get_related_records_of_slave() {
        //--------------------------------------------------------------------------------------
            global $TABLES;
            $related_records = array();
            foreach($TABLES as $table_name => $table_settings) {
                if($table_name == $this->table_name || !$this->is_relevant_referenced_table($table_name))
                    continue;
                foreach($table_settings['fields'] as $field_name => $field_settings) {
                    if($field_settings['type'] != T_LOOKUP)
                        continue;
                    if($field_settings['lookup']['table'] != $this->table_name)
                        continue;
                    // now we have a lookup field in another table that references this table
                    if($field_settings['lookup']['cardinality'] === CARDINALITY_SINGLE) {
                        $pk_fields = array();
                        if(!isset($table_settings['primary_key']))
                            return proc_error(l10n('error.merge-primary-key-setting-missing', $table_name));
                        foreach($table_settings['primary_key']['columns'] as $pk_field)
                            $pk_fields[] = db_esc($pk_field);
                        $pk_fields = join(', ', $pk_fields);
                        $sql = sprintf(
                            "select %s from %s where %s = ?",
                            $pk_fields,
                            db_esc($table_name),
                            db_esc($field_name)
                        );
                        $params = array(
                            $this->get_primary_key_value('slave')
                        );
                    }
                    else { // CARDINALITY_MULTIPLE
                        // check whether this N:M field is not also linked from this table; if so, ignore!
                        $ignore = false;
                        foreach($this->table['fields'] as $fn => $f) {
                            if($f['type'] == T_LOOKUP
                                && $f['lookup']['cardinality'] == CARDINALITY_MULTIPLE
                                && $f['linkage']['table'] == $field_settings['linkage']['table']
                                && $f['linkage']['fk_self'] == $field_settings['linkage']['fk_other']
                                && $f['linkage']['fk_other'] == $field_settings['linkage']['fk_self']
                            ) {
                                $ignore = true;
                                break;
                            }
                        }
                        if($ignore)
                            continue;
                        $sql = sprintf(
                            "select %s from %s where %s = ?",
                            db_esc($field_settings['linkage']['fk_self']),
                            db_esc($field_settings['linkage']['table']),
                            db_esc($field_settings['linkage']['fk_other'])
                        );
                        $params = array(
                            $this->get_primary_key_value('slave')
                        );
                    }
                    $related_records[$table_name] = array(
                        'fk_field' => $field_name,
                        'records' => array()
                    );
                    db_prep_exec($sql, $params, $stmt, $this->db);
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC))
                        $related_records[$table_name]['records'][] = $row;
                    if(count($related_records[$table_name]['records']) == 0)
                         unset($related_records[$table_name]);
                }
            }
            return $related_records;
        }

        //--------------------------------------------------------------------------------------
        public function display_form() {
        //--------------------------------------------------------------------------------------
            global $TABLES;
            #debug_log($_POST);
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

            db_prep_exec($sql, $exec_params, $stmt, $this->db);
            $rr = new RecordRenderer($this->table_name, $this->table, $display_fields, $stmt, true, false, null);
            $merge_infos_js = json_encode($merge_infos);

            get_session_var('merge', $merge_session_data);
            $str_page_heading = l10n('merge.page-heading', $this->table['display_name']);
            $str_intro = l10n('merge.intro', $this->table['item_name']);
            $str_button_cancel = $this->merge_success || !is_array($merge_session_data) || count($merge_session_data) != 2 ? '' : sprintf(
                '<button data-merge-reset="?%s" class="btn btn-basic"><span class="glyphicon glyphicon-remove"> %s</button>',
                http_build_query(array('mode' => MODE_MERGE, 'action' => 'reset')),
                l10n('merge.button-cancel')
            );
            $str_button_merge = $this->merge_success ? l10n('merge.button-merge-again') : l10n('merge.button-merge');
            $str_button_swap = l10n('merge.button-swap');

            $related_records = $this->get_related_records_of_slave();
            if($related_records === false)
                return '';
            #debug_log($related_records);
            $total_related = 0;
            $related_records_div = '';
            foreach($related_records as $table_name => $related) {
                $related_records_div .= sprintf(
                    "<li>%s: %s</li>\n",
                    $TABLES[$table_name]['display_name'], count($related['records'])
                );
            }
            if($related_records_div != '') {
                $checkbox_text = l10n('merge.list-of-referencing-records');
                $delete_text = l10n('merge.delete-slave-if-master-referenced');
                $related_records_div = <<<HTML
                    <div class="form-group">
                        <input type='hidden' name='rewire-references' value='0' />
                        <input type='hidden' name='rewire-delete-slave' value='0' />
                        <label class='normal-font-weight'>
                            {$this->render_checkbox('rewire-references', '1', true)}
                            $checkbox_text
                            <ul>
                                $related_records_div
                            </ul>
                        </label>
                        <ul><label class='normal-font-weight'>
                            {$this->render_checkbox('rewire-delete-slave', '1', true)}
                            $delete_text
                        </label></ul>
                    </div>
HTML;
            }

            $swappy = array();
            foreach($_GET as $p => $v) {
                if(preg_match('/^(?<N>1|2)_(?<L>.+)$/', $p, $match))
                    $p = ($match['N'] == '1' ? '2' : '1') . '_' . $match['L'];
                $swappy[$p] = $v;
            }
            $swapped_url = json_encode('?' . http_build_query($swappy));

            $ret = <<<HTML
                <style>
                    #master-slave-table input {
                        margin-right: 0.5em;
                    }
                </style>
                <h1>$str_page_heading</h1>
                <p>$str_intro</p>
                <form id='master-slave-table' class="col-sm-12" role="form" method="post">
                    {$rr->html()}
                    $related_records_div
                    <div class="form-group">
                        <button type="submit" name="do_merge" value="1" class="btn btn-primary"><span class="glyphicon glyphicon-transfer"> $str_button_merge</button>
                        <button id='swap-master-slave' class="btn btn-basic"><span class="glyphicon glyphicon-sort"> $str_button_swap</button>
                        $str_button_cancel
                    </div>
                </form>
                <script>
                    function sync_rewire_checkboxes() {
                        $('#rewire-delete-slave').prop('disabled', !$('#rewire-references').is(':checked'));
                    }
                    $(document).ready(function() {
                        $('#rewire-references').change(sync_rewire_checkboxes);
                        sync_rewire_checkboxes();
                        $('#swap-master-slave').click(function(e) {
                            e.preventDefault();
                            location = $swapped_url;
                        });
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
            $ret .= MergeRecordsPage::get_cancel_button_js();
            enable_delete($del);
            $ret .= $del;
            return $ret;
        }
    }
?>
