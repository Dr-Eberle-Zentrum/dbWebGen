<?php
// ----------------------------------------------------------------------------
// called automatically when this file is included
function plugin_csv_initialize(
) {
// ----------------------------------------------------------------------------
    // add public functions to $APP callables
    global $APP;
    if(!isset($APP['additional_callable_plugin_functions'])) {
        $APP['additional_callable_plugin_functions'] = [];
    }
    $APP['additional_callable_plugin_functions'][] = 'plugin_csv_import';
}

// ----------------------------------------------------------------------------
// Requires 'table' as GET parameter
function plugin_csv_import(
) {
// ----------------------------------------------------------------------------
    global $TABLES;

    if(!isset($_GET['table'])
        || !isset($TABLES[$_GET['table']])
    ) {
        return proc_error(l10n('error.invalid-params'));
    }

    $table_name = $_GET['table'];
    $table_settings = $TABLES[$table_name];
    echo sprintf('<h2>%s</h2>', l10n('plugin.csv.heading', $table_settings['display_name']));

    if(count($_FILES) > 0) {
        plugin_csv_do_import($table_name, $table_settings);
    }
    else {
        plugin_csv_render_upload_form($table_name, $table_settings);
    }
}

// ----------------------------------------------------------------------------
function plugin_csv_render_upload_form(
    $table_name,
    $table_settings
) {
// ----------------------------------------------------------------------------
    $label_csvfile = l10n('plugin.csv.label.csvfile');
    $label_browse = l10n('upload-field.browse');
    $label_hasheader = l10n('plugin.csv.label.hasheader');
    $value_hasheader = l10n('plugin.csv.value.hasheader');
    $label_delimiter = l10n('plugin.csv.label.delimiter');
    $label_enclosure = l10n('plugin.csv.label.enclosure');
    $label_escape = l10n('plugin.csv.label.escape');
    $label_skipnull = l10n('plugin.csv.label.skipnull');
    $label_tabulator = l10n('plugin.csv.label.tabulator');
    $label_columns = l10n('plugin.csv.label.columns');
    $help_delimiter = l10n('plugin.csv.help.delimiter');
    $help_enclosure = l10n('plugin.csv.help.enclosure');
    $help_escape = l10n('plugin.csv.help.escape');
    $help_columns = l10n('plugin.csv.help.columns');
    $help_skipnull = l10n('plugin.csv.help.skipnull');
    $label_import = l10n('plugin.csv.label.import');

    $column_options = '';
    $skipnull_options = '';
    foreach($table_settings['fields'] as $field_name => $field) {
        if($field['type'] == T_LOOKUP && $field['lookup']['cardinality'] === CARDINALITY_MULTIPLE) {
            continue;
        }
        $column_options .= sprintf('<option value="%s" selected>%s</option>', $field_name, html($field['label'])) . PHP_EOL;
        
        $selected = '';
        if(!is_field_required($field)) {
            $selected = ' selected';
        }
        $skipnull_options .= sprintf('<option value="%s"%s>%s</option>', $field_name, $selected, html($field['label'])) . PHP_EOL;
    }

    $html = <<<HTML
        <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
            <fieldset>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="csvfile">
                        <span data-field="csvfile">$label_csvfile</span>
                    </label>
                    <div class="col-sm-10">
                        <span class="btn btn-default btn-file file-input">
                            <span class="glyphicon glyphicon-search"></span>
                                $label_browse
                            <input data-text="datei_text" type="file" id="csvfile" name="csvfile" required>
                        </span>
                        <span class="filename" id="datei_text"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="hasheader">
                        <span data-field="hasheader">$label_hasheader</span>
                    </label>
                    <div class="col-sm-10">
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="hasheader" value="0">
                                <input type="checkbox" name="hasheader" id="hasheader" value="1"> $value_hasheader
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="delimiter">
                        <span data-field="delimiter">$label_delimiter</span>
                    </label>                
                    <div class="col-sm-1">
                        <input type="text" maxlength="1" class="form-control" id="delimiter" name="delimiter" value="," title="$label_delimiter" required>
                    </div>
                    <div class="col-sm-9">
                        <a role="button" class="btn margin-left" onclick="javascript:$('#delimiter').val('⇥')">⇥ $label_tabulator</a>
                    </div>
                    <div class="col-sm-offset-2 col-sm-10 help-block">$help_delimiter</div>    
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="enclosure">
                        <span data-field="enclosure">$label_enclosure</span>
                    </label>
                    <div class="col-sm-1">
                        <input type="text" maxlength="1" class="form-control" id="enclosure" name="enclosure" value='"' title="$label_enclosure" required>
                    </div>
                    <div class="col-sm-offset-2 col-sm-10 help-block">$help_enclosure</div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="escape">
                        <span data-field="escape">$label_escape</span>
                    </label>
                    <div class="col-sm-1">
                        <input type="text" maxlength="1" class="form-control" id="escape" name="escape" value='\' title="$label_escape" required>
                    </div>
                    <div class="col-sm-offset-2 col-sm-10 help-block">$help_escape</div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="columns">
                        <span data-field="columns">$label_columns</span>
                    </label>
                    <div class="col-sm-10">
                        <select class="form-control" id="columns" name="columns[]" data-placeholder="" data-allow-clear="true" multiple="multiple">
                            $column_options
                        </select>							
                    </div>
                    <div class="col-sm-offset-2 col-sm-10 help-block">$help_columns</div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="skipnull">
                        <span data-field="columns">$label_skipnull</span>
                    </label>
                    <div class="col-sm-10">
                        <select class="form-control" id="skipnull" name="skipnull[]" data-placeholder="" data-allow-clear="true" multiple="multiple">
                            $skipnull_options
                        </select>							
                    </div>
                    <div class="col-sm-offset-2 col-sm-10 help-block">$help_skipnull</div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">
                            <span class="glyphicon glyphicon-upload space-right"></span> $label_import
                        </button>
                    </div>
                </div>
            </fieldset>
        </form>
        <script>
            // prevent ordering of items in dropdown
            $(document).ready(() => {
                $("#columns").on("select2:select", function (e) {
                    let ev = $(e.params.data.element);
                    ev.detach();
                    $(this).append(ev).trigger("change");
                });
            });
        </script>
HTML;
    echo $html;
}    

// ----------------------------------------------------------------------------
function plugin_csv_do_import(
    $table_name,
    $table_settings
) {
// ----------------------------------------------------------------------------
    ini_set("auto_detect_line_endings", true);
    #debug_log($_FILES);
    #debug_log($_POST);

    if($_FILES['csvfile']['error'] != UPLOAD_ERR_OK) {
		return proc_error(get_file_upload_error_msg($_FILES['csvfile']['error']));
    }
    
    if($_POST['delimiter'] === '⇥') {
        $_POST['delimiter'] = "\t";
    }
    
    $tmp_name = $_FILES['csvfile']['tmp_name'];
    $csv_file = fopen($tmp_name, 'r');
    if($csv_file === false) {
        return proc_error(l10n('plugin.csv.error-file-read'));
    }

    $columns = isset($_POST['columns']) ? $_POST['columns'] : [];
    $posted_skipnull = isset($_POST['skipnull']) ? $_POST['skipnull'] : [];
    $skipnull = [];
    for($i = 0; $i < count($columns); $i++) {
        $skipnull[$i] = in_array($columns[$i], $posted_skipnull);
    }

    if(count($columns) === 0) {
        return proc_error(l10n('plugin.csv.error-no-columns'));
    }

    $sql = sprintf(
        'insert into %s (%s) values (%s)', 
        db_esc($table_name), 
        join(', ', array_map(function ($v) { return db_esc($v); }, $columns)), 
        trim(str_repeat('?, ', count($columns)), ', ')
    );
    #debug_log($sql);
    $transactionStarted = false;
    $db = db_connect();
    $row_num = 0;

    try {
        try {
            $transactionStarted = $db->beginTransaction();
        } catch(PDOException $e) {}
        
        while(($record = fgetcsv($csv_file, 0, $_POST['delimiter'], $_POST['enclosure'], $_POST['escape'])) !== false) {
            if(++$row_num === 1 && $_POST['hasheader'] == 1) {
                continue;
            }
            // detect and remove any UTF-8 BOM from first characters of first row
            if($row_num === 1) {
                if(substr($record[0], 0, 3) == chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'))) {
                    $record[0] = substr($record[0], 3);
                }
            }
            if(count($record) === 1 && $record[0] === null) {
                continue;
            }
            if(count($record) < count($columns)) {
                proc_error(l10n('plugin.csv.error-column-count', $row_num, count($record), count($columns)));
                throw new Exception();
            }
            
            // remove unneeded columns from record, if any:
            array_splice($record, count($columns));
            
            for($i = 0; $i < count($record); $i++) {
                if($record[$i] === '' && $skipnull[$i]) {
                    $record[$i] = null;
                }
            }
            if(!db_prep_exec($sql, $record, $stmt, $db)) {
                throw new Exception('');
            }
        }

        // if the primary key field is auto-incremented, we need to set the next sequence val appropriately
        // currently only for PostgreSQL #FIXME
        global $DB;
        if($DB['type'] === DB_POSTGRESQL) {
            // identify all fields with an auto sequence and build sequence setval query
            // for each sequence field, delivers something like:
            //   select setval('table_field_seq', coalesce(max(field) + 1, 1), false) from table
            $serial_sql = <<<SQL
                select 'select setval(' 
                    || quote_literal(quote_ident(S.relname))
                    || ', coalesce(max(' 
                    || quote_ident(C.attname)
                    || ') + 1, 1), false) from ' || quote_ident(T.relname)
                from pg_class S,
                    pg_depend D,
                    pg_class T,
                    pg_attribute C
                where S.relkind = 'S'
                    and S.oid = D.objid
                    and D.refobjid = T.oid
                    and D.refobjid = C.attrelid
                    and D.refobjsubid = C.attnum
                    and T.relname = ?
SQL;

            // execute the setval queries for all sequences of this table
            if(db_prep_exec($serial_sql, [$table_name], $stmt, $db)) {
                while($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    db_prep_exec($row[0], [], $foo, $db);
                }
            }
        }

        if($transactionStarted) {
            try {
                $db->commit();
            } catch(PDOException $e) {}
        }
        $c_imported = $_POST['hasheader'] == 1 ? $row_num - 1 : $row_num;
        proc_success(l10n('plugin.csv.success-import', $c_imported));
    }
    catch(Exception $e) {
        if($transactionStarted) {
            try {
                $db->rollBack();
            } catch(PDOException $e) {}
            proc_info(l10n('plugin.csv.info.aborted', $row_num));
        }
    }
    fclose($csv_file);
    echo sprintf(
        '<a role="button" class="btn btn-default" href="?table=%s&mode=%s">%s</a>',
        $table_name,
        MODE_LIST,
        l10n('plugin.csv.label.back-to-table')
    );
}