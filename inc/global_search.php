<?
    //==========================================================================================
    class GlobalSearch {
    //==========================================================================================

        //--------------------------------------------------------------------------------------
        protected static function get_table_settings(&$table /*string|array*/) {
            global $TABLES;
            return is_array($table) ? $table : ($table = $TABLES[$table]);
        }

        //--------------------------------------------------------------------------------------
        public static function get_setting($setting, $default) {
            global $APP;
            return isset($APP['global_search'][$setting]) ? $APP['global_search'][$setting] : $default;
        }

        //--------------------------------------------------------------------------------------
        public static function /*bool*/ is_table_included($table /*string|array*/) {
            global $APP;
            self::get_table_settings($table);
            if(isset($table['global_search']) && isset($table['global_search']['include_table']))
                return $table['global_search']['include_table'];
            return $APP['global_search']['include_table'];
        }

        //--------------------------------------------------------------------------------------
        public static function /*bool*/ is_field_included($field) {
            if(isset($field['global_search']) && isset($field['global_search']['include_field']))
                return $field['global_search']['include_field'];
            return self::get_setting('include_field', true);
        }

        //--------------------------------------------------------------------------------------
        public static function /*bool*/ is_enabled() {
            global $APP; return isset($APP['global_search']);
        }

        //--------------------------------------------------------------------------------------
        public static function max_preview_results_per_table() {
            return self::get_setting('max_preview_results_per_table', 10);
        }

        //--------------------------------------------------------------------------------------
        public static function max_detail_results() {
            return self::get_setting('max_detail_results', 100);
        }

        //--------------------------------------------------------------------------------------
        public static function search_string_transformation($field = null) {
            if($field !== null) {
                // check for field level search_string_transformation
                if(isset($field['global_search']) && isset($field['global_search']['search_string_transformation']))
                    return $field['global_search']['search_string_transformation'];
            }

            return self::get_setting('search_string_transformation',
                isset($APP['search_string_transformation']) ? $APP['search_string_transformation'] : '%s');
        }

        //--------------------------------------------------------------------------------------
        public static function is_preview() {
            return !isset($_GET['table']);
        }

        //--------------------------------------------------------------------------------------
        public static function /*string*/ render_searchbox() {
        //--------------------------------------------------------------------------------------
            $mode = MODE_GLOBALSEARCH;

            return <<<HTML
            <form class="navbar-form" method="GET">
            	<div class="input-group">
                    <input type="hidden" name="mode" value="$mode" />
                    <input type="text" class="form-control" placeholder="Search" name="q" id="q" />
            		<div class="input-group-btn">
            			<button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search"></span></button>
            		</div>
            	</div>
        	</form>
HTML;
        }

        //--------------------------------------------------------------------------------------
        public static function /*string*/ render_result() {
        //--------------------------------------------------------------------------------------
            global $TABLES;

            if(!self::is_preview()) {
                if(!isset($TABLES[$_GET['table']]))
                    return proc_error('Invalid table');
                return self::render_table_results($_GET['table'], $TABLES[$_GET['table']]);
            }

            $s = '';
            foreach($TABLES as $table_name => &$table) {
                if(self::is_table_included($table))
                    $s .= self::render_table_results($table_name, $table);
            }
            return $s;
        }

        //--------------------------------------------------------------------------------------
        public static function /*string*/ render_table_results($table_name, &$table) {
        //--------------------------------------------------------------------------------------
            require_once 'record_renderer.php';
            require_once 'fields.php';

            $is_preview = self::is_preview();
            $max_results = $is_preview? self::max_preview_results_per_table() : self::max_detail_results();
            $param_name = 'q';

            $select_fields = array();
            $from_conditions = array();
            $relevant_fields = array();
            foreach($table['fields'] as $field_name => &$field) {
                if(!self::is_field_included($field))
                    continue;
                $field_obj = FieldFactory::create($table_name, $field_name, $field);
                if($field_obj === null)
                    continue;

                $relevant_fields[$field_name] = $field;
                $select_fields[] = db_esc($field_name, 't');
                $where_conditions[] = $field_obj->get_global_search_condition($param_name, 't');
            }

            $sql = sprintf(
                'select %s from %s t where %s limit %s',
                implode(', ', $select_fields),
                db_esc($table_name),
                implode(' or ', $where_conditions),
                $max_results
            );

            debug_log($sql);

            $db = db_connect();
            if($db === false)
                return proc_error('Cannot connect to database.');
            $stmt = $db->prepare($sql);
            if($stmt === false)
                return proc_error('Failed to prepare search query.', $db);

            $search_term = sprintf(self::search_string_transformation(), $_GET['q']);
            if(false === $stmt->execute(array(":$param_name" => $search_term)))
    			return proc_error('Executing SQL statement failed', $db);

            $rr = new RecordRenderer($table_name, $table, $relevant_fields, $stmt, false, false, null);
            if($rr->num_results() == 0)
                return ''; // don't render anything

            return <<<HTML
                <h2>{$table['display_name']}</h2>
                <div class="col-sm-12">
                    {$rr->html()}
                </div>
HTML;
        }
    }
?>
