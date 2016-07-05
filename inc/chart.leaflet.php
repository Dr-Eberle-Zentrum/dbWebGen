<?
	//==========================================================================================
	class dbWebGenChart_leaflet extends dbWebGenChart {
	//==========================================================================================
		
		//--------------------------------------------------------------------------------------
		// returns html form for chart settings		
		public /*string*/ function settings_html() {
		//--------------------------------------------------------------------------------------
			return <<<SETTINGS
				<p><a target="_blank" href="http://leafletjs.com/">Leaflet</a> is the leading open-source JavaScript library for mobile-friendly interactive maps.</p>
				<p>TODO: Settings for scale, base map, etc., see http://ecenter.uni-tuebingen.de/lostaircraft/en/js/custom.js</p>
SETTINGS;
		}
		
		//--------------------------------------------------------------------------------------
		// override if additional scripts are needed for this type
		public /*void*/ function add_required_scripts() {
		//--------------------------------------------------------------------------------------
			add_javascript('https://npmcdn.com/leaflet@0.7.7/dist/leaflet.js');
			add_stylesheet('https://npmcdn.com/leaflet@0.7.7/dist/leaflet.css');
		}
		
		//--------------------------------------------------------------------------------------
		// returns html/js to render page
		public /*string*/ function get_js(/*PDOStatement*/ $query_result) {
		//--------------------------------------------------------------------------------------
			$data_table = '';
			$data_headers = '';
			
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
				var map = L.map('chart_div', {
					zoomControl: true, maxZoom: 21, minZoom: 1
				});
				var basemap = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
					minZoom: 1, maxZoom: 20, attribution: 'OpenStreetMap Contributors'
				});
				basemap.addTo(map);
				
				for(var m=0; m<data_table.length; m++) {
					data_markers[m] = L.marker([data_table[m][0], data_table[m][1]]).bindPopup(m + '').addTo(map);
				}
				
				map.fitBounds(L.featureGroup(data_markers).getBounds());
				
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