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
            $relevant_fields = array();
            foreach($table['fields'] as $field_name => &$field) {
                if(!($field_obj = FieldFactory::create($table_name, $field_name, $field)))
                    continue;
                $relevant_fields[$field_name] = $field;
                $select_fields[] = sprintf($field_obj->sql_select_transformation(), db_esc($field_name, 't'));
            }

            $sql = sprintf(
                'SELECT %s FROM %s t WHERE %s',
                implode(', ', $select_fields),
                db_esc($table_name),
                implode(' OR ', $where_conditions)
            );
            debug_log($sql);
            debug_log($exec_params);

            $db = db_connect();
            if($db === false)
                return proc_error(l10n('error.db-connect'));
            $stmt = $db->prepare($sql);
            if($stmt === false)
                return proc_error(l10n('error.db-prepare'), $db);
            if(false === $stmt->execute($exec_params))
    			return proc_error(l10n('error.db-execute'), $stmt);

            $rr = new RecordRenderer($table_name, $table, $relevant_fields, $stmt, false, false, null);
            return <<<HTML
                <h1>l10nize Merge</h2>
                <div class="col-sm-12">
                    {$rr->html()}
                </div>
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
