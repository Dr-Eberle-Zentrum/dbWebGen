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
        protected $has_search_sort;
        protected $html;
        protected $html_highlighter;

        //--------------------------------------------------------------------------------------
        public function __construct(
            $table_name,
            &$table,
            &$relevant_fields,
            &$stmt,
            $allow_delete_icon,
            $has_fk_lookups,
            $has_search_sort,
            $html_highlighter)
        {
        //--------------------------------------------------------------------------------------
            $this->stmt = $stmt;
            $this->table_name = $table_name;
            $this->table = $table;
            $this->fields = $relevant_fields;
            $this->allow_delete = $allow_delete_icon;
            $this->has_fk_lookups = $has_fk_lookups;
            $this->has_search_sort = $has_search_sort;
            $this->html_highlighter = $html_highlighter;
            $this->build();
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
        protected function build() {
        //--------------------------------------------------------------------------------------
            $table_body = "<tbody>\n";
            $col_longest_content = array();
            $this->num_results = 0;
            while($record = $this->stmt->fetch(PDO::FETCH_ASSOC)) {
                #debug_log($record);
                $this->num_results++;

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

                    $val = prepare_field_display_val($this->table, $record, $this->fields[$col], $col, $val, $this->html_highlighter);
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

                $table_head .= sprintf(
                    '<th %s>%s%s</th>',
                    $minwidth,
                    $this->fields[$col]['label'],
                    $this->has_search_sort ? '<br />' . $this->render_search_sort($col) : ''
                );
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

        //------------------------------------------------------------------------------------------
    	protected function render_search_sort($field_name) {
    	//------------------------------------------------------------------------------------------
    		$sort_field = isset($_GET['sort']) ? $_GET['sort'] : '';
    		$sort_dir = isset($_GET['dir']) ? $_GET['dir'] : 'asc';

    		$t = "<div class='sort-search'>";

    		if($field_name == $sort_field && $sort_dir == 'asc')
    			$t .= "<span class='glyphicon glyphicon-arrow-up'></span>";
    		else
    			$t .= "<a href='". build_get_params(array('sort'=>$field_name,'dir'=>'asc')) ."' title='Sort Ascending'><span class='glyphicon glyphicon-arrow-up'></span></a>";

    		if($field_name == $sort_field && $sort_dir == 'desc')
    			$t .= "<span class='glyphicon glyphicon-arrow-down'></span>";
    		else
    			$t .= "<a href='". build_get_params(array('sort'=>$field_name,'dir'=>'desc')) ."' title='Sort Descending'><span class='glyphicon glyphicon-arrow-down'></span></a>";

    		$search_val = (isset($_GET[SEARCH_PARAM_QUERY]) && isset($_GET[SEARCH_PARAM_FIELD]) && $_GET[SEARCH_PARAM_FIELD] == $field_name ? unquote($_GET[SEARCH_PARAM_QUERY]) : '');

    		$search_option = isset($_GET[SEARCH_PARAM_OPTION]) ? $_GET[SEARCH_PARAM_OPTION] : SEARCH_ANY;

    		$t .= " <a href='javascript:void(0)' title='Search' data-value='{$search_val}' data-field='{$field_name}' data-option='{$search_option}' data-purpose='search' data-toggle='popover' data-container='body' data-placement='top'><span class='glyphicon glyphicon-search'></span></a>";

    		$t .= "</div>";

    		return $t;
    	}
    }
?>
