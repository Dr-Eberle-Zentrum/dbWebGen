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
        public function render() {
        // -------------------------------------------------------------------------------------
            $out = '';
            if(false) {
                $out .= <<<HTML
                    <h1>dbWebGen Setup Wizard</h1>
                    <p>This dbWebGen app has not been set up yet. As a first step, you need to provide connection details of the database you would like work with.</p>
HTML;
            }
            $out .= <<<HTML
                <style>
                    #setup-container { display: none }
                </style>
                <p id="loading">Loading...</p>
                <div id="setup-container">
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
                        <div id="tables-editor"></div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-primary" id="save"><span class="glyphicon glyphicon-floppy-disk"></span> Save</button>
                </div>

HTML;
            $out .= $this->render_script();
            return $out;
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
            $out = <<<JS
            <script>
                function db_type_changed() {
                    var db_dropdown = $(this);
                    if(db_dropdown.val() == 'postgresql' && !window.editor['db'].generate_settings_box.is(':visible')) {
                        $('#db div.form-group').first().append(window.editor['db'].generate_settings_box);
                    }
                    else if(db_dropdown.val() != 'postgresql' && window.editor['db'].generate_settings_box.is(':visible')) {
                        window.editor['db'].generate_settings_box.remove();
                    }
                }
                $(function() {
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
                                $('#loading').fadeOut();
                                $('#setup-container').fadeIn();
                            }
                        });
                        window.editor[config_parts[i]].setValue(values[config_parts[i]]);
                    }

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
                    $('#db select[name="root[type]"]').on('change', db_type_changed);
                });
            </script>
JS;
            return $out;
        }
    }
?>
