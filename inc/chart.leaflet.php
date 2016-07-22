<?
	//==========================================================================================
	class dbWebGenChart_leaflet extends dbWebGenChart {
	//==========================================================================================
		// select st_y(st_transform(coordinates,3857)), st_x(st_transform(coordinates,3857)), name_translit "Name (translit.)", name_arabic "Name (Arabic)", name "Country/Region", type "Type", information "Information", p.id "ID", st_astext(coordinates) "Coordinates" from places p, countries_and_regions c where c.id = p.country_region and not coordinates is null
		
		//--------------------------------------------------------------------------------------
		// returns html form for chart settings		
		public /*string*/ function settings_html() {
		//--------------------------------------------------------------------------------------
			return <<<SETTINGS
				<p><a target="_blank" href="http://leafletjs.com/">Leaflet</a> offers mobile-friendly interactive maps.</p>
				<p>The first two columns in the query must be the latitude (<i>y</i>) and longitude (<i>x</i>) of each record. All additional columns will be put in the marker popups as a table.</p>				
				<label class="control-label">Base Map Provider</label>
				<div class='form-group'>
					{$this->page->render_select('leaflet-basemap', 'OpenStreetMap.BlackAndWhite', array(
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
						/*'HERE.normalDay' => 'HERE normalDay',
						'HERE.normalDayCustom' => 'HERE normalDayCustom',
						'HERE.normalDayGrey' => 'HERE normalDayGrey',
						'HERE.normalDayMobile' => 'HERE normalDayMobile',
						'HERE.normalDayGreyMobile' => 'HERE normalDayGreyMobile',
						'HERE.normalDayTransit' => 'HERE normalDayTransit',
						'HERE.normalDayTransitMobile' => 'HERE normalDayTransitMobile',
						'HERE.normalNight' => 'HERE normalNight',
						'HERE.normalNightMobile' => 'HERE normalNightMobile',
						'HERE.normalNightGrey' => 'HERE normalNightGrey',
						'HERE.normalNightGreyMobile' => 'HERE normalNightGreyMobile',
						'HERE.basicMap' => 'HERE basicMap',
						'HERE.mapLabels' => 'HERE mapLabels',
						'HERE.trafficFlow' => 'HERE trafficFlow',
						'HERE.carnavDayGrey' => 'HERE carnavDayGrey',
						'HERE.hybridDay' => 'HERE hybridDay',
						'HERE.hybridDayMobile' => 'HERE hybridDayMobile',
						'HERE.pedestrianDay' => 'HERE pedestrianDay',
						'HERE.pedestrianNight' => 'HERE pedestrianNight',
						'HERE.satelliteDay' => 'HERE satelliteDay',
						'HERE.terrainDay' => 'HERE terrainDay',
						'HERE.terrainDayMobile' => 'HERE terrainDayMobile',*/
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
					))}
				</div>
				<div class="form-group">
					<label class="control-label">Display Options</label>
					<div class='checkbox top-margin-zero'>
						<label>{$this->page->render_checkbox('leaflet-scale', 'ON', true)}Show Scale</label>
					</div>
					<div class='checkbox'>
						<label>{$this->page->render_checkbox('leaflet-minimap', 'ON', false)}Show Overview Map</label>
					</div>
					<label class="control-label">Spatial Reference System</label>
					<p>Select the coordinate system that Leaflet should use. The source data needs to be transformed to this projection.</p>
					<div class='form-group'>
						{$this->page->render_select('leaflet-crs', 'L.CRS.EPSG3857', array(
							'L.CRS.EPSG3857' => 'EPSG:3857 Pseudo-Mercator (Leaflet default)',
							'L.CRS.EPSG4326' => 'EPSG:4326 WGS 84',
							'L.CRS.EPSG3395' => 'EPSG:3395 World Mercator',
							'L.CRS.Simple' => 'Direct projection'
						))}
					</div>
				</div>
				<script>
					function add_basemaps() {
						var dropdown = $('#leaflet-basemap');
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
SETTINGS;
		}
		
		//--------------------------------------------------------------------------------------
		// override if additional scripts are needed for this type
		public /*void*/ function add_required_scripts() {
		//--------------------------------------------------------------------------------------
			add_javascript('https://npmcdn.com/leaflet@0.7.7/dist/leaflet.js');
			add_stylesheet('https://npmcdn.com/leaflet@0.7.7/dist/leaflet.css');
			add_javascript('https://leaflet-extras.github.io/leaflet-providers/leaflet-providers.js');
			
			if($this->page->get_post('leaflet-minimap') === 'ON') {
				add_javascript('https://norkart.github.io/Leaflet-MiniMap/Control.MiniMap.js');
				add_stylesheet('https://norkart.github.io/Leaflet-MiniMap/Control.MiniMap.css');
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
				
				foreach($row as $k => $v)
					if($v === null) $row[$k] = '';
				
				$data_table .= json_encode(array_values($row), JSON_NUMERIC_CHECK) . ",\n";
			}
			
			$js = <<<JS
			var data_table = [
				{$data_table}
			];
			
			var data_headers = {$data_headers};
			
			var data_markers = [];
			
			document.addEventListener("DOMContentLoaded", function() {
				$('#chart_div').css('overflow', 'hidden');
				
				if(data_table.length == 0) {
					$('#chart_div').html('<div class="alert alert-warning"><b>Note:</b> Your query did not return any records.</div>');
					return;
				}
				
				var map = L.map('chart_div', {
					crs: {$this->page->get_post('leaflet-crs', 'L.CRS.EPSG3857')},
					zoomControl: true,
					// minZoom: 1,
					// maxZoom: 21, 
				});
				
				var basemap = L.tileLayer.provider('{$this->page->get_post('leaflet-basemap')}');
				basemap.addTo(map);
				
				// we store the row index of the marker in the popup. Only when opened, it will display the whole data stored in the record
				for(var m=0; m<data_table.length; m++) {
					data_markers[m] = L.marker([ 
						data_table[m][0], 
						data_table[m][1]
					]).bindPopup(m.toString()).addTo(map);
				}
				
				if('{$this->page->get_post('leaflet-scale')}' === 'ON') {
					new L.control.scale({ 
						position: 'bottomleft',					
						maxWidth: 150,				
						imperial: false
					}).addTo(map);
				}
				
				map.fitBounds(L.featureGroup(data_markers).getBounds());
				
				if('{$this->page->get_post('leaflet-minimap')}' === 'ON') {
					new L.Control.MiniMap(L.tileLayer.provider('{$this->page->get_post('leaflet-basemap')}'), { 
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
				}
				
				map.on('popupopen', function(e) {
					var marker = e.popup._source;
					var table = marker.getPopup().getContent();
					if(table.substring(0, 8) == '<!--X-->')
						return;
					
					var nr = parseInt(table);
					table = '<!--X--><table>';
					for(var i=2; i<data_headers.length; i++) {
						if(data_table[nr][i].toString() === '') continue;
						table += '<tr><th>' + data_headers[i] + '</th><td>' + data_table[nr][i] + '</td></tr>';
					}
					table += '</table>';
					marker.getPopup().setContent(table);
					
					$('.leaflet-popup-content').css('margin', '1em .5em .5em .5em');
				});
			});
JS;
			return $js;
		}
	};
?>