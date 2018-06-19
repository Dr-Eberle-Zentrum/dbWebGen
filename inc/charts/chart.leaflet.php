<?php
	//==========================================================================================
	class dbWebGenChart_leaflet extends dbWebGenChart {
	//==========================================================================================
		//--------------------------------------------------------------------------------------
		// returns html form for chart settings
		public /*string*/ function settings_html() {
		//--------------------------------------------------------------------------------------
			$basemap_script = <<<JS
				<script>
					function add_basemaps() {
						var dropdown = $('#{$this->ctrlname("basemap")}');
						for(var map_provider in L.TileLayer.Provider.providers) {
							if(!L.TileLayer.Provider.providers.hasOwnProperty(map_provider))
								continue;
							for(var map_variant in L.TileLayer.Provider.providers[map_provider].variants)
								dropdown.append($('<option/>', { value: map_provider + '.' + map_variant }).text(map_provider + ' ' + map_variant));
						}
					}
					// call only if there is an update
					// add_basemaps();
				</script>
JS;

			return l10n(
				'chart.leaflet.settings',
				$this->page->render_radio($this->ctrlname('data_format'), 'point', true),
				$this->page->render_radio($this->ctrlname('data_format'), 'wkt'),
				$this->page->render_select($this->ctrlname('basemap'), 'OpenStreetMap.BlackAndWhite', array(
                    'OpenStreetMap.Mapnik' => 'OpenStreetMap Mapnik',
                    'OpenStreetMap.BlackAndWhite' => 'OpenStreetMap BlackAndWhite',
                    'OpenStreetMap.DE' => 'OpenStreetMap DE',
                    'OpenStreetMap.France' => 'OpenStreetMap France',
                    'OpenStreetMap.HOT' => 'OpenStreetMap HOT',
                    'Thunderforest.OpenCycleMap' => 'Thunderforest OpenCycleMap',
                    'Thunderforest.Transport' => 'Thunderforest Transport',
                    'Thunderforest.TransportDark' => 'Thunderforest TransportDark',
                    'Thunderforest.SpinalMap' => 'Thunderforest SpinalMap',
                    'Thunderforest.Landscape' => 'Thunderforest Landscape',
                    'Thunderforest.Outdoors' => 'Thunderforest Outdoors',
                    'Thunderforest.Pioneer' => 'Thunderforest Pioneer',
                    'Hydda.Full' => 'Hydda Full',
                    'Hydda.Base' => 'Hydda Base',
                    'Hydda.RoadsAndLabels' => 'Hydda RoadsAndLabels',
                    'MapQuestOpen.OSM' => 'MapQuestOpen OSM',
                    'MapQuestOpen.Aerial' => 'MapQuestOpen Aerial',
                    'MapQuestOpen.HybridOverlay' => 'MapQuestOpen HybridOverlay',
                    'Stamen.Toner' => 'Stamen Toner',
                    'Stamen.TonerBackground' => 'Stamen TonerBackground',
                    'Stamen.TonerHybrid' => 'Stamen TonerHybrid',
                    'Stamen.TonerLines' => 'Stamen TonerLines',
                    'Stamen.TonerLabels' => 'Stamen TonerLabels',
                    'Stamen.TonerLite' => 'Stamen TonerLite',
                    'Stamen.Watercolor' => 'Stamen Watercolor',
                    'Stamen.Terrain' => 'Stamen Terrain',
                    'Stamen.TerrainBackground' => 'Stamen TerrainBackground',
                    'Stamen.TopOSMRelief' => 'Stamen TopOSMRelief',
                    'Stamen.TopOSMFeatures' => 'Stamen TopOSMFeatures',
                    'Esri.WorldStreetMap' => 'Esri WorldStreetMap',
                    'Esri.DeLorme' => 'Esri DeLorme',
                    'Esri.WorldTopoMap' => 'Esri WorldTopoMap',
                    'Esri.WorldImagery' => 'Esri WorldImagery',
                    'Esri.WorldTerrain' => 'Esri WorldTerrain',
                    'Esri.WorldShadedRelief' => 'Esri WorldShadedRelief',
                    'Esri.WorldPhysical' => 'Esri WorldPhysical',
                    'Esri.OceanBasemap' => 'Esri OceanBasemap',
                    'Esri.NatGeoWorldMap' => 'Esri NatGeoWorldMap',
                    'Esri.WorldGrayCanvas' => 'Esri WorldGrayCanvas',
                    'OpenWeatherMap.Clouds' => 'OpenWeatherMap Clouds',
                    'OpenWeatherMap.CloudsClassic' => 'OpenWeatherMap CloudsClassic',
                    'OpenWeatherMap.Precipitation' => 'OpenWeatherMap Precipitation',
                    'OpenWeatherMap.PrecipitationClassic' => 'OpenWeatherMap PrecipitationClassic',
                    'OpenWeatherMap.Rain' => 'OpenWeatherMap Rain',
                    'OpenWeatherMap.RainClassic' => 'OpenWeatherMap RainClassic',
                    'OpenWeatherMap.Pressure' => 'OpenWeatherMap Pressure',
                    'OpenWeatherMap.PressureContour' => 'OpenWeatherMap PressureContour',
                    'OpenWeatherMap.Wind' => 'OpenWeatherMap Wind',
                    'OpenWeatherMap.Temperature' => 'OpenWeatherMap Temperature',
                    'OpenWeatherMap.Snow' => 'OpenWeatherMap Snow',
                    'CartoDB.Positron' => 'CartoDB Positron',
                    'CartoDB.PositronNoLabels' => 'CartoDB PositronNoLabels',
                    'CartoDB.PositronOnlyLabels' => 'CartoDB PositronOnlyLabels',
                    'CartoDB.DarkMatter' => 'CartoDB DarkMatter',
                    'CartoDB.DarkMatterNoLabels' => 'CartoDB DarkMatterNoLabels',
                    'CartoDB.DarkMatterOnlyLabels' => 'CartoDB DarkMatterOnlyLabels',
                    'HikeBike.HikeBike' => 'HikeBike HikeBike',
                    'HikeBike.HillShading' => 'HikeBike HillShading',
                    'BasemapAT.basemap' => 'BasemapAT basemap',
                    'BasemapAT.grau' => 'BasemapAT grau',
                    'BasemapAT.overlay' => 'BasemapAT overlay',
                    'BasemapAT.highdpi' => 'BasemapAT highdpi',
                    'BasemapAT.orthofoto' => 'BasemapAT orthofoto',
                    'NASAGIBS.ModisTerraTrueColorCR' => 'NASAGIBS ModisTerraTrueColorCR',
                    'NASAGIBS.ModisTerraBands367CR' => 'NASAGIBS ModisTerraBands367CR',
                    'NASAGIBS.ViirsEarthAtNight2012' => 'NASAGIBS ViirsEarthAtNight2012',
                    'NASAGIBS.ModisTerraLSTDay' => 'NASAGIBS ModisTerraLSTDay',
                    'NASAGIBS.ModisTerraSnowCover' => 'NASAGIBS ModisTerraSnowCover',
                    'NASAGIBS.ModisTerraAOD' => 'NASAGIBS ModisTerraAOD',
                    'NASAGIBS.ModisTerraChlorophyll' => 'NASAGIBS ModisTerraChlorophyll'
                )),
				$this->page->render_textbox($this->ctrlname('custom_tile_url'), ''),
				$this->page->render_checkbox($this->ctrlname('scale'), 'ON', true),
				$this->page->render_checkbox($this->ctrlname('minimap'), 'ON', false),
				$this->page->render_textbox($this->ctrlname('max_zoom'), ''),
				$this->page->render_textbox($this->ctrlname('attribution'), ''),
				$this->page->render_select($this->ctrlname('crs'), 'L.CRS.EPSG3857', array(
					'L.CRS.EPSG3857' => 'EPSG:3857 Pseudo-Mercator (Leaflet default)',
					'L.CRS.EPSG4326' => 'EPSG:4326 WGS 84',
					'L.CRS.EPSG3395' => 'EPSG:3395 World Mercator',
					'L.CRS.Simple' => 'Direct projection'
				)),
				$this->page->render_textarea($this->ctrlname('additional_js'), '')
			)
			. $basemap_script;
		}

		//--------------------------------------------------------------------------------------
		// override if additional scripts are needed for this type
		public /*void*/ function add_required_scripts() {
		//--------------------------------------------------------------------------------------
			add_javascript(ENGINE_PATH_HTTP . 'node_modules/leaflet/dist/leaflet.js');
			add_stylesheet(ENGINE_PATH_HTTP . 'node_modules/leaflet/dist/leaflet.css');
			add_javascript(ENGINE_PATH_HTTP . 'node_modules/leaflet-providers/leaflet-providers.js');

			if($this->page->get_post($this->ctrlname('data_format')) === 'wkt')
				add_javascript(ENGINE_PATH_HTTP . 'node_modules/leaflet-omnivore/leaflet-omnivore.min.js');

			if($this->page->get_post($this->ctrlname('minimap')) === 'ON') {
				add_javascript(ENGINE_PATH_HTTP . 'node_modules/leaflet-minimap/dist/Control.MiniMap.min.js');
				add_stylesheet(ENGINE_PATH_HTTP . 'node_modules/leaflet-minimap/dist/Control.MiniMap.min.css');
			}
		}

		//--------------------------------------------------------------------------------------
		public /*string*/ function data_to_js(&$row, $row_nr) {
		//--------------------------------------------------------------------------------------
			$r = '';
			if($row_nr === 0) // first row => render headers
				$r .= json_encode(array_keys($row)) . ",\n";

			return $r . json_encode(array_values($row), JSON_NUMERIC_CHECK) . ",\n";
		}

		//--------------------------------------------------------------------------------------
		// check whether we want to ignore this record (if coordinates are null)
		// might get overridden
		protected function is_valid_record(&$record) {
		//--------------------------------------------------------------------------------------
			// if coordinates are null => invalidate
			$num_coord_cols = $this->page->get_post($this->ctrlname('data_format')) == 'wkt' ? 1 : 2;
			$col_index = 0;
			foreach($record as $col_name => &$value) {
				if($col_index < $num_coord_cols && $value === null)
					return false;
				if(++$col_index >= $num_coord_cols)
					return true;
			}
			return true;
		}

		//--------------------------------------------------------------------------------------
		// override in subclass if custom map bounds fitting is desired
		protected /*bool*/ function shall_fit_bounds_to_markers() {
		//--------------------------------------------------------------------------------------
			return true;
		}

		//--------------------------------------------------------------------------------------
		// returns html/js to render page
		public /*string*/ function get_js(/*PDOStatement*/ $query_result) {
		//--------------------------------------------------------------------------------------
			$data_table = '';
			$data_headers = '[]';

			$first = true;
			while($row = $query_result->fetch(PDO::FETCH_ASSOC)) {
				if($first) {
					$data_headers = json_encode(array_keys($row));
					$first = false;
				}

				if(!$this->is_valid_record($row))
					continue;

				foreach($row as $k => $v)
					if($v === null) $row[$k] = '';

				$data_table .= json_encode(array_values($row), JSON_NUMERIC_CHECK) . ",\n";
			}

			$attribution_js = json_encode(trim($this->page->get_post($this->ctrlname('attribution'), '')));
			$has_attribution_js = $attribution_js !== '' ? 'true' : 'false';
			$fit_bounds = $this->shall_fit_bounds_to_markers() ? 'true' : 'false';
			$nodata_warning_js = json_encode(l10n('chart.leaflet.no-data'));

			$js = <<<JS
			var data_table = [
				{$data_table}
			];
			var data_headers = {$data_headers};
			var data_markers = [];
			var markers_layer = L.layerGroup();
			var map;
			var basemap;
			var chart_div;
			var coord_data_format = '{$this->page->get_post($this->ctrlname('data_format'))}';

			function get_base_tilelayer() {
				var custom_tile_url = '{$this->page->get_post($this->ctrlname('custom_tile_url'), '')}';
				var tile_options = {};
				if('{$this->page->get_post($this->ctrlname('max_zoom'), '')}' != '')
					tile_options.maxZoom = parseInt({$this->page->get_post($this->ctrlname('max_zoom'))});
				var tile_layer;
				if(custom_tile_url != '')
					tile_layer = L.tileLayer(custom_tile_url, tile_options);
				else
					tile_layer = L.tileLayer.provider('{$this->page->get_post($this->ctrlname('basemap'))}', tile_options);
				tile_layer.getAttribution = function() { return ''; };
				return tile_layer;
			}

			document.addEventListener('DOMContentLoaded', function() {
				chart_div = $('#chart_div');
				chart_div.css('overflow', 'hidden');

				if(data_table.length == 0) {
					chart_div.html('<div class="alert alert-warning">' + $nodata_warning_js + '</div>');
					return;
				}

				map = L.map('chart_div', {
					crs: {$this->page->get_post($this->ctrlname('crs'), 'L.CRS.EPSG3857')},
					zoomControl: true,
					attributionControl: false
				});

				if($has_attribution_js)
					L.control.attribution({ prefix: '', position: 'bottomright' }).addAttribution($attribution_js).addTo(map);

				basemap = get_base_tilelayer();
				basemap.addTo(map);

				// we store the row index of the marker in the popup. Only when opened, it will display the whole data stored in the record
				for(var m=0; m<data_table.length; m++) {
					var layer;
					if(coord_data_format === 'point'
						|| coord_data_format === '' // legacy: default
					) {
						layer = L.marker([
							data_table[m][0],
							data_table[m][1]
						]);
					}
					else if(coord_data_format === 'wkt') {
						layer = omnivore.wkt.parse(data_table[m][0]).getLayers()[0];
					}
					data_markers[m] = layer;
					layer.bindPopup(m.toString()).addTo(markers_layer);
				}
				markers_layer.addTo(map);

				if('{$this->page->get_post($this->ctrlname('scale'))}' === 'ON') {
					new L.control.scale({
						position: 'bottomleft',
						maxWidth: 150,
						imperial: false
					}).addTo(map);
				}

				if($fit_bounds)
					map.fitBounds(L.featureGroup(data_markers).getBounds());

				// invalidate map after resize to make leaflet fetch missing tiles
				chart_div.deferredResize(function() {
					map.invalidateSize(false);
				}, 500);

				if('{$this->page->get_post($this->ctrlname('minimap'))}' === 'ON') {
					map.on('load', function() {
						new L.Control.MiniMap(get_base_tilelayer(), {
							position: 'bottomright',
							zoomAnimation: true,
							toggleDisplay: true,
							autoToggleDisplay: true,
							minimized: false,
							width: 250,
							aimingRectOptions: {
								color: 'blue',
								weight: 5,
								fillColor: 'lightblue',
								fillOpacity: 0.4
							}
						}).addTo(map);
					});
				}

				map.on('popupopen', function(e) {
					var marker = e.popup._source;
					var table = marker.getPopup().getContent();
					if(table.substring(0, 8) == '<!--X-->')
						return;

					var nr = parseInt(table);
					table = '<!--X--><table>';

					// point coordinates: third column starts data
					// wkt: second column starts data
					var col_data_start = (coord_data_format === 'wkt' ? 1 : 2);

					for(var i = col_data_start; i < data_headers.length; i++) {
						if(data_table[nr][i].toString() === '') continue;
						table += '<tr><th>' + data_headers[i] + '</th><td>' + data_table[nr][i] + '</td></tr>';
					}
					table += '</table>';
					marker.getPopup().setContent(table);

					$('.leaflet-popup-content').css('margin', '1em .5em .5em .5em');
				});

				{$this->page->get_post($this->ctrlname('additional_js'))}
			});
JS;
			return $js;
		}
	};
?>
