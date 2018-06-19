<?php
    //------------------------------------------------------------------------------------------
    function render_map_picker() {
    //------------------------------------------------------------------------------------------
        require_once 'fields/fields.php';
        $field_settings = FieldFactory::create($_GET['table'], $_GET['field']);
        if($field_settings === null)
            return proc_error('Invalid parameters.');

        add_javascript(ENGINE_PATH_HTTP . 'node_modules/leaflet/dist/leaflet.js');
        add_stylesheet(ENGINE_PATH_HTTP . 'node_modules/leaflet/dist/leaflet.css');
        add_javascript(ENGINE_PATH_HTTP . 'node_modules/leaflet-draw/dist/leaflet.draw.js');
        add_stylesheet(ENGINE_PATH_HTTP . 'node_modules/leaflet-draw/dist/leaflet.draw.css');
        add_javascript(ENGINE_PATH_HTTP . 'node_modules/terraformer/terraformer.min.js');
        add_javascript(ENGINE_PATH_HTTP . 'node_modules/terraformer-wkt-parser/dist/terraformer-wkt-parser.min.js');
        add_javascript(ENGINE_PATH_HTTP . 'node_modules/leaflet-omnivore/leaflet-omnivore.min.js');

        $cur_geom = '';
        if(isset($_GET['val']) && trim($_GET['val']) != '') {
            $val_html = html($_GET["val"]);
            if(!postgis_transform_wkt($_GET['val'], $field_settings->get_srid(), 4326, $geom_wkt))
                $geom_wkt = '';
            $val_unquote = json_encode($geom_wkt);
            $cur_geom = "
                try {
                    omnivore.wkt.parse($val_unquote).eachLayer(function(layer) {
                        curLayer = layer;
                    });
                }
                catch(e) {
                    $('#infos').prepend($('<div/>').addClass('alert alert-danger').html(
                        '". l10n('error.map-picker-wkt', $val_html) ."'
                    ));
                }
            ";
        }

        if($script = $field_settings->get_script()) {
            if(!is_array($script))
                $script = array($script);
            foreach($script as $js)
                add_javascript($js);
        }
        $map_options = json_encode((object) $field_settings->get_map_options());
        $draw_options = json_encode((object) $field_settings->get_draw_options());

        $is_readonly = isset($_GET['readonly']) && $_GET['readonly'] == 'true';
        $is_readonly_js = json_encode($is_readonly);
        $edit_info = '';
        if(!$is_readonly) {
            $instr = l10n('map-picker.edit-instructions');
            $edit_info = <<<HTML
                <div id="infos" class="col-sm-12">
                    <div class="alert alert-info">$instr</div>
                </div>
HTML;
        }

        $msg_single_marker = json_encode(l10n('error.map-picker-single-marker'));
        $done_button = l10n('map-picker.done-button');

        echo <<<HTML
            <div class="container-fluid" style="margin-top: .5em; 0">
                $edit_info
                <div id="map_picker" class="col-sm-12 fill-height" data-margin-bottom="7" style="border: 1px solid gray"></div>
            </div>
            <script>
                // these global vars are available to the script in map_picker/script >>
                var drawnItems;
                var curLayer;
                var map;
                var field_srid = {$field_settings->get_srid()};
                // <<
                function transform_wkt(geom_wkt, source_srid, target_srid, result_func) {
                    if(source_srid == target_srid) {
                        result_func(geom_wkt);
                        return;
                    }
                    $.post('?mode=func&target=postgis_transform_wkt', {
                        geom_wkt: geom_wkt,
                        source_srid: source_srid,
                        target_srid: target_srid
                    }, result_func, 'text');
                }
                function finish_map_picker() {
                    var layers;
                    if(!drawnItems || (layers = drawnItems.getLayers()).length != 1) {
                        alert($msg_single_marker);
                        return;
                    }
                    var wkt = Terraformer.WKT.convert(layers[0].toGeoJSON().geometry);
                    transform_wkt(wkt, 4326, field_srid, function(wkt) {
                        var doc = $(window.opener.document);
                        doc.find('#{$_GET['ctrl_id']}').focus().val(wkt);
                        doc.find('input[type=checkbox]#{$_GET['ctrl_id']}__null__').prop('checked', false);
                        window.close();
                    });
                }
                $(window).load(function() {
                    map = L.map('map_picker', $map_options);
                    $cur_geom
                    if(typeof map_picker_init_map === 'function')
                        map_picker_init_map(); // in custom JS!
                    else if(curLayer)
                        map.setView(curLayer.getLatLng(), 10);
                    else
                        map.fitWorld();
                    drawnItems = new L.FeatureGroup();
                    map.addLayer(drawnItems);
                    if(curLayer)
                        curLayer.addTo(drawnItems);
                    if(!$is_readonly_js) {
                        L.Control.DoneButton = L.Control.extend({
                            onAdd: function(map) {
                                var container = L.DomUtil.create('div', 'leaflet-bar');
                                container.innerHTML = '<button class="btn btn-primary" onclick="finish_map_picker()"><span class="glyphicon glyphicon-check space-right"></span> {$done_button}</button>';
                                return container;
                            }
                        });
                        new L.Control.DoneButton({
                            position: 'topright'
                        }).addTo(map);
                        new L.Control.Draw({
                            position: 'topright',
                            draw: $draw_options,
                            edit: { featureGroup: drawnItems }
                        }).addTo(map);
                        map.on('draw:created', function(e) {
                            drawnItems.addLayer(e.layer);
                        });
                        map.on('draw:drawstart', function(e) {
                            drawnItems.clearLayers();
                        });
                    }
                    if(typeof map_picker_finish_init === 'function')
                        map_picker_finish_init();  // in custom JS!
                });
            </script>
HTML;
    }
?>
