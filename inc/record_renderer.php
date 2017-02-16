<?
    //==========================================================================================
    class RecordRenderer {
    //==========================================================================================
        protected $stmt;
        protected $table_name;
        protected $table;
        protected $fields;
        protected $allow_delete;
        protected $num_results;
        protected $has_fk_lookups;
        protected $render_search_sort_func;
        protected $html;

        //--------------------------------------------------------------------------------------
        public function __construct($table_name, &$table, &$relevant_fields, &$stmt, $allow_delete_icon, $has_fk_lookups, $render_search_sort_func) {
        //--------------------------------------------------------------------------------------
            $this->stmt = $stmt;
            $this->table_name = $table_name;
            $this->table = $table;
            $this->fields = $relevant_fields;
            $this->allow_delete = $allow_delete_icon;
            $this->has_fk_lookups = $has_fk_lookups;
            $this->render_search_sort_func = $render_search_sort_func;

            $this->render();
        }

        //--------------------------------------------------------------------------------------
        public function html() {
            return $this->html;
        }

        //--------------------------------------------------------------------------------------
        public function num_results() {
            return $this->num_results;
        }

        //--------------------------------------------------------------------------------------
        protected function render() {
        //--------------------------------------------------------------------------------------
            $table_body = "<tbody>\n";
            $col_longest_content = array();
            $this->num_records = 0;
            while($record = $this->stmt->fetch(PDO::FETCH_ASSOC)) {
                #debug_log($record);
                $this->num_records++;

                $id_str = '';
                foreach($this->table['primary_key']['columns'] as $pk) {
                    // field with postfixed name contains the raw value (not lookup display value) of referenced primary keys
                    $id_str .= "&amp;{$pk}=" . ($this->has_fk_lookups ? urlencode($record[db_postfix_fieldname($pk, FK_FIELD_POSTFIX, false)]) : urlencode($record[$pk]));
                }

                $table_body .= "<tr><td class='fit'>\n";
                $action_icons = array();

                if(isset($this->table['render_links']) && is_allowed($this->table, MODE_LINK)) {
                    foreach($this->table['render_links'] as $render_link) {
                        $action_icons[] = "<a href='" .
                            sprintf($render_link['href_format'], $record[$render_link['field']]) .
                            "'><span title='{$render_link['title']}' class='glyphicon glyphicon-{$render_link['icon']}'></span></a>";
                    }
                }

                if(is_allowed($this->table, MODE_VIEW)) {
                    $action_icons[] = "<a href='?table={$this->table_name}&amp;mode=".MODE_VIEW."{$id_str}' data-purpose='view'><span title='View this record' class='glyphicon glyphicon-zoom-in'></span></a>";
                }

                if(is_allowed($this->table, MODE_EDIT))
                    $action_icons[] = "<a href='?table={$this->table_name}&amp;mode=".MODE_EDIT."{$id_str}'><span title='Edit this record' class='glyphicon glyphicon-edit'></span></a>";

                if($this->allow_delete && is_allowed($this->table, MODE_DELETE))
                    $action_icons[] = "<a role='button' data-href='?table={$this->table_name}{$id_str}&amp;mode=".MODE_DELETE."' data-toggle='modal' data-target='#confirm-delete'><span title='Delete this record' class='glyphicon glyphicon-trash'></span></a>";

                if(isset($this->table['custom_actions'])) {
                    foreach($this->table['custom_actions'] as $custom_action) {
                        if($custom_action['mode'] == $_GET['mode']) {
                            // call custom action handler
                            $action_icons[] = $custom_action['handler']($this->table_name, $this->table, $record, $custom_action);
                        }
                    }
                }

                $table_body .= implode('&nbsp;&nbsp;', $action_icons) . "&nbsp;&nbsp;&nbsp</td>\n";

                $col_no = 0;
                foreach($record as $col => $val) {
                    if(!isset($this->fields[$col]))
                        continue;

                    $css = '';
                    if(isset($_GET[SEARCH_PARAM_FIELD]) && $_GET[SEARCH_PARAM_FIELD] === $col)
                        $css = 'class="bg-success"';

                    $val = prepare_field_display_val($this->table, $record, $this->fields[$col], $col, $val);
                    $table_body .= "<td $css>$val</td>\n";

                    // determine max cell len
                    $textlen = mb_strlen(strip_tags($val));
                    if(!isset($col_longest_content[$col_no]) || $textlen > $col_longest_content[$col_no])
                        $col_longest_content[$col_no] = $textlen;

                    $col_no++;
                }

                $table_body .= "</tr>\n";
            }
            $table_body .= "</tbody></table>\n";

            $table_head = "<table class='table table-hover table-striped table-condensed'>\n";
            $table_head .= "<thead><tr class='info'><th class='fit'></th>\n";

            $col_no = 0;
            for($i=0; $i<$this->stmt->columnCount(); $i++) {
                $meta = $this->stmt->getColumnMeta($i);
                $col = $meta['name'];
                if(!isset($this->fields[$col]))
                    continue;

                $minwidth = '';
                if(isset($col_longest_content[$col_no])) {
                    $mw = min(get_mincolwidth_max(), $col_longest_content[$col_no] * get_mincolwidth_pxperchar());
                    $minwidth = "style='min-width:{$mw}px'";
                }
                $col_no++;

                $table_head .= "<th $minwidth>{$this->fields[$col]['label']}";
                if($this->render_search_sort_func)
                    "<br />" . $this->render_search_sort_func($col) . "</th>";
            }
            $table_head .= "</tr></thead>\n";

            $this->html = <<<TABLE
            <div class='panel panel-default'>
                <div class='table-responsive'>
                    $table_head
                    $table_body
                </div>
            </div>
TABLE;
        }
    }
?>
