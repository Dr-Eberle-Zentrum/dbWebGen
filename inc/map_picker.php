<?
    //------------------------------------------------------------------------------------------
    function render_map_picker() {
    //------------------------------------------------------------------------------------------
        require_once 'fields.php';
        $field_settings = FieldFactory::create($_GET['table'], $_GET['field']);
        if($field_settings === null)
            return proc_error('Invalid parameters.');

        add_javascript('https://unpkg.com/leaflet@1.0.3/dist/leaflet.js');
        add_stylesheet('https://unpkg.com/leaflet@1.0.3/dist/leaflet.css');
        add_javascript(ENGINE_PATH . 'inc/leaflet.draw/leaflet.draw.js');
        add_stylesheet(ENGINE_PATH . 'inc/leaflet.draw/leaflet.draw.css');
        add_javascript('https://cdn-geoweb.s3.amazonaws.com/terraformer/1.0.6/terraformer.min.js');
        add_javascript('https://cdn-geoweb.s3.amazonaws.com/terraformer-wkt-parser/1.1.1/terraformer-wkt-parser.min.js');
        add_javascript('https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.3.1/leaflet-omnivore.min.js');

        $cur_point = '';
        if(isset($_GET['val']) && trim($_GET['val']) != '') {
            $val_html = html($_GET["val"]);
            $val_unquote = json_encode($_GET['val']);
            $cur_point = "
                try {
                    omnivore.wkt.parse($val_unquote).eachLayer(function(layer) {
                        curPointLayer = layer.addTo(drawnItems);
                    });
                }
                catch(e) {
                    $('#infos').prepend($('<div/>').addClass('alert alert-danger').html(
                        '<b>Error!</b> The current value <code>$val_html</code> is invalid and cannot be displayed.'
                    ));
                }
            ";
        }

        if($script = $field_settings->get_script())
            add_javascript($script);

        echo <<<HTML
            <div class="container-fluid" style="margin-top: .5em; 0">
                <div id="infos" class="col-sm-12">
                    <div class="alert alert-info">Place the marker at the desired location. To create a marker, click the <span class='glyphicon glyphicon-map-marker'></span> icon and then place the marker on the map. To move an existing marker, click the <span class='glyphicon glyphicon-edit'></span> icon and follow the instructions. When you're done, click the <span class="glyphicon glyphicon-check"></span> Done button.</div>
                </div>
                <div id="map_picker" class="col-sm-12 fill-height"></div>
            </div>
            <script>
                // these global vars are available to the script in map_picker/script >>
                var drawnItems;
                var curPointLayer;
                var map;
                // <<
                function finish_map_picker() {
                    var layers;
                    if(!drawnItems || (layers = drawnItems.getLayers()).length != 1) {
                        alert('You need to make sure you have exactly one marker placed on the map.');
                        return;
                    }
                    var wkt = Terraformer.WKT.convert(layers[0].toGeoJSON().geometry);
                    var doc = $(window.opener.document);
                    doc.find('#{$_GET['ctrl_id']}').focus().val(wkt);
                    doc.find('input[type=checkbox]#{$_GET['ctrl_id']}__null__').prop('checked', false);
                    window.close();
                }
                $(window).load(function() {
                    map = L.map('map_picker');
                    if(typeof map_picker_init_map === 'function')
                        map_picker_init_map(); // in custom JS!
                    else
                        map.fitWorld();
                    drawnItems = new L.FeatureGroup();
                    map.addLayer(drawnItems);
                    $cur_point
                    L.Control.Watermark = L.Control.extend({
                        onAdd: function(map) {
                            var container = L.DomUtil.create('div', 'leaflet-bar');
                            container.innerHTML = '<button class="btn btn-primary" onclick="finish_map_picker()"><span class="glyphicon glyphicon-check"></span> Done</button>';
                            return container;
                        }
                    });
                    new L.Control.Watermark({ position: 'topright' }).addTo(map);
                    new L.Control.Draw({
                        position: 'topright',
                        draw: { polygon: false, circle: false, polyline: false, rectangle: false },
                        edit: { featureGroup: drawnItems }
                    }).addTo(map);
                    map.on('draw:created', function(e) { drawnItems.addLayer(e.layer); });
                    map.on('draw:drawstart', function(e) { drawnItems.clearLayers(); });
                    if(typeof map_picker_finish_init === 'function')
                        map_picker_finish_init();  // in custom JS!
                });
            </script>
HTML;
    }
?>
