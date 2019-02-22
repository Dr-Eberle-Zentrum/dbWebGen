<?php
    //------------------------------------------------------------------------------------------
    function db_connect() {
    //------------------------------------------------------------------------------------------
        global $DB;
        switch($DB['type']) {
            case DB_POSTGRESQL:
                $conn = "pgsql:dbname={$DB['db']};host={$DB['host']};port={$DB['port']};options='--client_encoding=UTF8'";
                break;
            case DB_MYSQL:
                $conn = "mysql:dbname={$DB['db']};host={$DB['host']};port={$DB['port']};charset=utf8";
                break;
        }

        try {
            return new PDO($conn, $DB['user'], $DB['pass']);
        }
        catch(PDOException $e) {
            return FALSE;
        }
    }

    //------------------------------------------------------------------------------------------
    function db_esc($name, $qualifier = null) {
    //------------------------------------------------------------------------------------------
        global $DB;
        switch($DB['type']) {
            case DB_POSTGRESQL:
                $escape_char = '"';
                $separator_char = '.';
                break;
            case DB_MYSQL:
                $escape_char = '`';
                $separator_char = '.';
                break;
            default:
                return proc_error(l10n('error.invalid-dbtype', $DB['type']));
        }

        if($name[0] == $escape_char)
            return $name; // already escaped

        if($qualifier !== null)
            return $escape_char . $qualifier . $escape_char . $separator_char . $escape_char . $name . $escape_char;
        else
            return $escape_char . $name . $escape_char;
    }

    //------------------------------------------------------------------------------------------
    // $return_escaped:
    //   if NULL, it will return escaped only of $fieldname is already escaped, otherwise not
    //   if TRUE/FALSE, it will/will not escape the postfixed fieldname
    function db_postfix_fieldname($fieldname, $postfix, $return_escaped) {
    //------------------------------------------------------------------------------------------
        global $DB;

        switch($DB['type']) {
            case DB_POSTGRESQL:
                $escape_char = '"';
                break;

            case DB_MYSQL:
                $escape_char = '`';
                break;

            default:
                return proc_error(l10n('error.invalid-dbtype', $DB['type']));
        }

        $fieldname_unescaped = trim($fieldname, $escape_char);
        $was_escaped = ($fieldname_unescaped == $fieldname);
        $do_escape = ($return_escaped === TRUE || ($return_escaped === NULL && $was_escaped === TRUE));

        if(!$do_escape)
            $escape_char = '';

        return "{$escape_char}{$fieldname}{$postfix}{$escape_char}";
    }

    //------------------------------------------------------------------------------------------
    function db_get_single_val($sql, $params, &$retrieved_value, $db = false) {
    //------------------------------------------------------------------------------------------
        if($db === false)
            $db = db_connect();
        if($db === false)
            return proc_error(l10n('error.db-connect'));

        $stmt = $db->prepare($sql);
        if($stmt === false)
            return proc_error(l10n('error.db-prepare'), $db);

        if(false === $stmt->execute($params))
            return proc_error(l10n('error.db-execute'), $stmt);

        $retrieved_value = $stmt->fetchColumn();
        return true;
    }

    //------------------------------------------------------------------------------------------
    function db_get_single_row($sql, $params, &$row, $db = false) {
    //------------------------------------------------------------------------------------------
        if($db === false)
            $db = db_connect();
        if($db === false)
            return proc_error(l10n('error.db-connect'));

        $stmt = $db->prepare($sql);
        if($stmt === false)
            return proc_error(l10n('error.db-prepare'), $db);

        if(false === $stmt->execute($params))
            return proc_error(l10n('error.db-execute'), $stmt);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return true;
    }

    //------------------------------------------------------------------------------------------
    function db_prep_exec($sql, $params, &$stmt, $db = false) {
    //------------------------------------------------------------------------------------------
        if($db === false)
            $db = db_connect();
        if($db === false)
            return proc_error(l10n('error.db-connect'));
        $stmt = $db->prepare($sql);
        if($stmt === false)
            return proc_error(l10n('error.db-prepare'), $db);
        if(false === $stmt->execute($params))
            return proc_error(l10n('error.db-execute'), $stmt);
        return true;
    }

    //------------------------------------------------------------------------------------------
    function db_array_to_json_array_agg($expr, $cast_to_text = true) {
    //------------------------------------------------------------------------------------------
        global $DB;
        switch($DB['type']) {
            case DB_POSTGRESQL:
                return "array_to_json(array_agg($expr))";
            case DB_MYSQL:
                if($cast_to_text)
                    $expr = db_cast_text($expr);
                return "concat('[',group_concat(json_quote($expr) separator ','),']')";
        }
    }

    //------------------------------------------------------------------------------------------
    function db_array_to_string_array_agg($expr, $separator) {
    //------------------------------------------------------------------------------------------
        global $DB;
        switch($DB['type']) {
            case DB_POSTGRESQL:
                return "array_to_string(array_agg($expr), '$separator')";
            case DB_MYSQL:
                return "group_concat(($expr) separator '$separator')";
        }
    }

    //------------------------------------------------------------------------------------------
    function db_cast_text($expr) {
    //------------------------------------------------------------------------------------------
        global $DB;
        switch($DB['type']) {
            case DB_POSTGRESQL:
                return "$expr::text";
            case DB_MYSQL:
                return "cast($expr as char)";
        }
    }

    //------------------------------------------------------------------------------------------
    function db_boolean_literal($bool) {
    //------------------------------------------------------------------------------------------
        global $DB;
        switch($DB['type']) {
            case DB_POSTGRESQL:
                return $bool ? 't' : 'f';
            case DB_MYSQL:
                return $bool;
        }
    }

    //==========================================================================================
  	// To fake query results for chart building (see setting $APP/custom_query_data_provider)
  	class PDOStatementEmulator {
  	//==========================================================================================
  		protected $table = array();
      protected $column_meta = array();
  		protected $cur_row = 0;
  		protected $num_rows = 0;

      //--------------------------------------------------------------------------------------
      // $column_meta is an ordered array, each item specifying column name (key 'name') and
      // type (key 'js_type', one of { string, number, boolean, date, datetime, timeofday })
      public function __construct($column_meta) {
      //--------------------------------------------------------------------------------------
        $this->column_meta = $column_meta;
      }

      //--------------------------------------------------------------------------------------
      public function columnCount() {
      //--------------------------------------------------------------------------------------
        return count($this->column_meta);
      }

  		//--------------------------------------------------------------------------------------
      // add $row in key => value style, for PDO::FETCH_ASSOC to work
  		public function add_row($row) {
  		//--------------------------------------------------------------------------------------
  			$this->table[] = $row;
  			$this->num_rows++;
  		}

  		//--------------------------------------------------------------------------------------
      // fetch_style is ignored, returns what was fed by add_row()
  		public function fetch($fetch_style) {
  		//--------------------------------------------------------------------------------------
  			if($this->cur_row >= $this->num_rows)
  				return false;
        return $this->table[$this->cur_row++];
  		}

  		//--------------------------------------------------------------------------------------
      // sort by column
  		public function sort($column, $asc) {
  		//--------------------------------------------------------------------------------------
  			usort($this->table, function($row1, $row2) use ($column, $asc) {
  				if($row1[$column] == $row2[$column]) return 0;
  				return $row1[$column] < $row2[$column] ? ($column ? -1 : 1) : ($column ? 1 : -1);
  			});
  		}

  		//--------------------------------------------------------------------------------------
      // only call *after* $table was populated
  		public function limit($n) {
  		//--------------------------------------------------------------------------------------
  			if($n >= $this->num_rows)
  				return;
  			array_splice($this->table, $n);
  			$this->num_rows = $n;
  		}

      //--------------------------------------------------------------------------------------
      public function getColumnMeta($column) {
      //--------------------------------------------------------------------------------------
        return $this->column_meta[$column];
      }
    }
?>
