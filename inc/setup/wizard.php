<?php
    //==========================================================================================
    class SetupWizard {
    //==========================================================================================
        protected $db_settings, $login_settings, $app_settings, $tables_settings;

        // -------------------------------------------------------------------------------------
        public function __construct() {
        // -------------------------------------------------------------------------------------
            add_javascript(ENGINE_PATH_HTTP . "node_modules/@json-editor/json-editor/dist/jsoneditor.js");
            $this->load_settings();
        }

        // -------------------------------------------------------------------------------------
        public static function is_allowed() {
        // -------------------------------------------------------------------------------------
            global $APP;
            global $LOGIN;
            return count($LOGIN) == 0 || (
                isset($APP['super_users']) &&
                is_logged_in() &&
                in_array($_SESSION['user_data'][$LOGIN['username_field']], $APP['super_users'])
            );
        }

        // -------------------------------------------------------------------------------------
        protected function load_settings() {
        // -------------------------------------------------------------------------------------
            global $DB, $TABLES, $APP, $LOGIN;
            if(isset($DB)) {
                $this->db_settings = $DB;
                $this->app_settings = $APP;
                $this->login_settings = $LOGIN;
                $this->tables_settings = $TABLES;
            }
            else {
                if(isset($_SESSION['setup'])) {
                    $this->db_settings = $_SESSION['setup']['DB'];
                    $this->app_settings = $_SESSION['setup']['APP'];
                    $this->login_settings = $_SESSION['setup']['LOGIN'];
                    $this->tables_settings = $_SESSION['setup']['TABLES'];
                }
                else {
                    $_SESSION['setup'] = array(
                        'DB' => $this->db_settings = array(),
                        'APP' => $this->app_settings = array(),
                        'LOGIN' => $this->login_settings = array(),
                        'TABLES' => $this->tables_settings = array()
                    );
                }
            }
        }

        // -------------------------------------------------------------------------------------
        public function render($initial_setup = false) {
        // -------------------------------------------------------------------------------------
            if(!self::is_allowed()) {
                proc_error(l10n('error.not-allowed'));
                return '';
            }
            $out = sprintf("<h1>%s</h1>\n", l10n('setup.heading'));
            if($initial_setup)
                $out .= "<p>This dbWebGen app has not been set up yet. Here you can define all necessary settings.</p>";

            $exit_button = $initial_setup ? sprintf(
                '<button type="button" class="btn btn-default hidden" id="exit"><span class="glyphicon glyphicon-exit"></span> Exit Setup - Go To App Home</button>'
            ) : '';
            $out .= <<<HTML
                <style>
                    #setup-container { display: none }
                    .hidden { display: none }
                </style>
                <p id="loading">Loading...</p>
                <div id="setup-container">
                    <button type="button" class="btn btn-primary" id="save"><span class="glyphicon glyphicon-floppy-disk"></span> Save All Settings</button>
                    $exit_button
                    <p>
                        <ul class="margin-top nav nav-tabs">
                            <li class="active"><a data-toggle="tab" href="#db">Database Connection</a></li>
                            <li><a data-toggle="tab" href="#login">Login Method</a></li>
                            <li><a data-toggle="tab" href="#app">App Settings</a></li>
                            <li><a data-toggle="tab" href="#tables">Tables Settings</a></li>
                        </ul>
                    </p>
                    <div class="tab-content">
                      <div id="db" class="tab-pane fade in active">
                        <div id="db-editor"></div>
                      </div>
                      <div id="login" class="tab-pane fade">
                        <div id="login-editor"></div>
                      </div>
                      <div id="app" class="tab-pane fade">
                        <div id="app-editor"></div>
                      </div>
                      <div id="tables" class="tab-pane fade">
                        <button id="generate-settings" class="btn btn-success hidden"><span class="glyphicon glyphicon-import"></span> Generate Tables Settings from Database Structure</button>
                        <div id="tables-editor"></div>
                      </div>
                    </div>
                </div>

HTML;
            $out .= $this->render_script();
            return $out;
        }

        // -------------------------------------------------------------------------------------
        public static function save_settings() {
        // -------------------------------------------------------------------------------------
            $config_parts = array('db', 'app', 'login', 'tables');
            $config = "<?php\n\n";
            foreach($config_parts as $config_part) {
                $val = json_decode($_POST[$config_part], true);
                $config .= sprintf(
                    "$%s = %s;\n\n",
                    strtoupper($config_part),
                    preg_replace('/\s=>\s*array \(/', ' => array(', var_export($val, true)));
            }
            $res = @file_put_contents('settings.php', $config);
            if($res === false)
                return l10n('setup.wizard.save-error-file');
            return l10n('setup.wizard.save-success');
        }

        // -------------------------------------------------------------------------------------
        protected function render_script() {
        // -------------------------------------------------------------------------------------
            $config_parts = array('db', 'app', 'login', 'tables');
            $schemas = array();
            $values = array();
            foreach($config_parts as $what) {
                $schemas[$what] = file_get_contents(ENGINE_PATH_HTTP . "inc/setup/{$what}_schema.json");
                $values[$what] = $this->{$what . '_settings'};
            }
            $config_parts_js = json_encode($config_parts);
            $schemas_js = json_encode($schemas);
            $values_js = json_encode($values);
            $settings_generator_url = ENGINE_PATH_HTTP . 'settings.generator.php?special=tables_setup';
            $out = <<<JS
            <script>
                //var select2 = window.jQuery.fn.select2;
                window.jQuery.fn.select2 = null;
                function db_type_changed() {
                    console.log('change');
                    var db_dropdown = $(this);
                    if(db_dropdown.val() == 'postgresql' && !window.editor['db'].generate_settings_box.is(':visible')) {
                        $('#db div.form-group').first().append(window.editor['db'].generate_settings_box);
                    }
                    else if(db_dropdown.val() != 'postgresql' && window.editor['db'].generate_settings_box.is(':visible')) {
                        window.editor['db'].generate_settings_box.remove();
                    }
                }
                $(function() {
                    var db_dropdown = $('#db select[name="root[type]"]');
                    var generate_settings_btn = $('#generate-settings').click(function() {
                        var db_settings = window.editor['db'].getValue();
                        if(Object.keys(window.editor['tables'].getValue()).length > 0 && !confirm("WARNING! This will clear all tables settings on this page and then try to extract the settings from the database structure. Also the user \"" + db_settings.user + "\" must have permissions must have at least read access to the  public schema and to the information_schema catalog in your database, otherwise this may result a misconfiguration of dbWebGen. Are you sure you want to continue?"))
                            return false;

                        $('#tables-editor').hide();
                        $('#tables').append($('<span/>').attr('id', 'loading-generator').text('Loading generated settings...'));

                        var db_settings = window.editor['db'].getValue();
                        $.post("$settings_generator_url", {
                            host: db_settings.host,
                            port: db_settings.port,
                            user: db_settings.user,
                            pass: db_settings.pass,
                            name: db_settings.db,
                            schema: 'public'
                        }, function(data) {
                            if(typeof data.error === 'string')
                                alert(data.error);
                            else {
                                console.log(data.tables);
                                window.editor['tables'].setValue(data.tables);
                            }
                            $('#loading-generator').remove();
                            $('#tables-editor').show();
                        });
                    });
                    var options = {
                        theme: 'bootstrap3',
                        iconlib: 'bootstrap3',
                        disable_edit_json: true,
                        display_required_only: true,
                        show_errors: "always",
                        keep_oneof_values: false
                    };

                    var config_parts = $config_parts_js;
                    var schemas = $schemas_js;
                    var values = $values_js;

                    window.editor = {};
                    var ready_count = 0;
                    for(var i = 0; i < config_parts.length; i++) {
                        window.editor[config_parts[i]] = new JSONEditor(
                            document.getElementById(config_parts[i] + '-editor'),
                            $.extend(options, {
                                schema: JSON.parse(schemas[config_parts[i]])
                            }));
                        window.editor[config_parts[i]].on('ready', function() {
                            if(++ready_count == config_parts.length) {
                                $('#loading').remove();
                                $('#setup-container').fadeIn();
                            }
                        });
                        window.editor[config_parts[i]].setValue(values[config_parts[i]]);
                    }
                    window.editor['db'].on('change', function() {
                        var db_settings = window.editor['db'].getValue();
                        var can_connect = db_settings.type === 'postgresql'
                            && db_settings.host !== ''
                            && db_settings.port
                            && db_settings.user !== ''
                            && db_settings.pass !== ''
                            && db_settings.db !== '';
                        if(generate_settings_btn.hasClass('hidden') && can_connect)
                            generate_settings_btn.removeClass('hidden');
                        else if(!generate_settings_btn.hasClass('hidden') && !can_connect)
                            generate_settings_btn.addClass('hidden');
                    });
                    window.editor['db'].generate_settings_box = $('<div/>').append(
                        $('<label/>').addClass('space-left').append(
                            $('<input/>').attr({
                                type: 'checkbox',
                                id: 'postgres-generate-settings'
                            }).prop('checked', true)
                        ).append(
                            $('<span/>').addClass('space-left').text('Generate initial settings from database').css('font-weight', 'normal')
                        )
                    );
                    db_dropdown.on('change', db_type_changed);
                    $('#save').click(function() {
                        var post_params = {};
                        for(var i = 0; i < config_parts.length; i++) {
                            if(window.editor[config_parts[i]].validate().length > 0) {
                                alert("There are still required settings missing. Please check all tabs and fill in the required fields.");
                                return false;
                            }
                            post_params[config_parts[i]] = JSON.stringify(window.editor[config_parts[i]].getValue());
                        }
                        $.post('?mode=func&target=setupwizard_save_settings', post_params, function(data) {
                            console.log(data);
                            alert(data);
                            $('#exit').removeClass('hidden');
                        });
                    });
                    $('#exit').click(function() {
                        location.href = '?';
                    });
                });
            </script>
JS;
            return $out;
        }
    }
?>
