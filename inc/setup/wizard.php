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

HTML;
            $out .= $this->render_script();
            return $out;
        }

        // -------------------------------------------------------------------------------------
        protected function render_script() {
        // -------------------------------------------------------------------------------------
            $db_schema = file_get_contents(ENGINE_PATH_HTTP . 'inc/setup/DB_schema.json');
            $app_schema = file_get_contents(ENGINE_PATH_HTTP . 'inc/setup/APP_schema.json');
            $login_schema = file_get_contents(ENGINE_PATH_HTTP . 'inc/setup/LOGIN_schema.json');
            $tables_schema = file_get_contents(ENGINE_PATH_HTTP . 'inc/setup/TABLES_schema.json');
            $db_value = json_encode($this->db_settings);
            $app_value = json_encode($this->app_settings);
            $login_value = json_encode($this->login_settings);
            $tables_value = json_encode($this->tables_settings);
            $out = <<<JS
            <script>
                function db_type_changed() {
                    var db_dropdown = $(this);
                    if(db_dropdown.val() == 'postgresql' && !window.db_editor.generate_settings_box.is(':visible')) {
                        $('#db div.form-group').first().append(window.db_editor.generate_settings_box);
                    }
                    else if(db_dropdown.val() != 'postgresql' && window.db_editor.generate_settings_box.is(':visible')) {
                        window.db_editor.generate_settings_box.remove();
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
                    window.db_editor = new JSONEditor(document.getElementById('db-editor'), $.extend(options, { schema: $db_schema }));
                    window.db_editor.setValue($db_value);
                    window.app_editor = new JSONEditor(document.getElementById('app-editor'), $.extend(options, { schema: $app_schema }));
                    window.app_editor.setValue($app_value);
                    window.login_editor = new JSONEditor(document.getElementById('login-editor'), $.extend(options, { schema: $login_schema }));
                    window.login_editor.setValue($login_value);
                    window.tables_editor = new JSONEditor(document.getElementById('tables-editor'), $.extend(options, { schema: $tables_schema }));
                    window.tables_editor.setValue($tables_value);

                    window.db_editor.generate_settings_box = $('<div/>').append(
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
