<?php

// ----------------------------------------------------------------------------
function plugin_csv_initialize(
) {
// ----------------------------------------------------------------------------
    // add public functions to $APP callables
    global $APP;
    if(!isset($APP['additional_callable_plugin_functions'])) {
        $APP['additional_callable_plugin_functions'] = [];
    }
    array_merge($APP['additional_callable_plugin_functions'], [
        'plugin_csv_import'
    ]);
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

    if(count($_POST) > 0) {
        plugin_csv_process_form();
    }
    else {
        plugin_csv_render_form();
    }
}
