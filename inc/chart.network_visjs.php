<?
	//==========================================================================================
	class dbWebGenChart_network_visjs extends dbWebGenChart {
	//==========================================================================================

		//--------------------------------------------------------------------------------------
		// returns html form for chart settings
		public /*string*/ function settings_html() {
		//--------------------------------------------------------------------------------------
			$options_help = <<<HELP
				Adjust this JSON object to reflect your custom network options (see the <a target="_blank" href="http://visjs.org/docs/network/#options">documentation</a>).
HELP;

			$nodequery_help = <<<HELPTEXT
				<p>Optionally use this field to provide an SQL query that provides information about nodes. The query should have named columns as follows:</p>
				<ol class='columns'>
					<li><code>id</code>Node ID (string or integer)</li>
					<li><code>label</code>Node label (string)</li>
					<li><code>options</code>: <a target="_blank" href="http://visjs.org/docs/network/nodes.html">Node options</a> (JSON string) - optional; define individual options for each node in JSON notation. Individual options override node options provided in the <i>Custom Options</i> box below.</li>
				</ol>
HELPTEXT;
			$nodequery_help = get_help_popup('Node Query', $nodequery_help);
			$options_help = get_help_popup('Custom Options', $options_help);

			$visjs_options = <<<OPTIONS
{
  "layout": {
    "improvedLayout": true
  },
  "interaction": {
    "dragNodes": true,
    "hover": true
  },
  "physics": {
    "solver": "forceAtlas2Based",
    "stabilization": {
      "iterations": 300,
      "updateInterval": 50
    },
    "adaptiveTimestep": true
  },
  "nodes": {
    "font": "16px arial black",
    "shape": "icon",
    "icon": {
      "size": 75,
	  "face": "Ionicons",
      "code": "\uf47e",
      "color":"darkgreen"
    },
    "scaling": {
      "label": {
        "min": 12,
        "max": 20
      }
    }
  },
  "edges": {
    "smooth": {
      "type": "dynamic"
    },
    "color": "#888888",
    "font": "11px arial #888888",
    "hoverWidth": 3,
    "selectionWidth": 3
  }
}
OPTIONS;
			return <<<SETTINGS
				<p>Displays the query result as a network graph. The query result must be an edge list with the following named columns:</p>
				<ol class='columns'>
					<li><code>source</code>: Source node ID (string or integer)</li>
					<li><code>target</code>: Target node ID (string or integer)</li>
					<li><code>weight</code>: Edge weight controlling the width in pixels of the edge (number) - optional, default = 1</li>
					<li><code>label</code>: Edge label (string) - optional</li>
					<li><code>options</code>: <a target="_blank" href="http://visjs.org/docs/network/edges.html">Edge options</a> (JSON string) - optional; define individual options for each edge in JSON notation. Individual options override edge options provided in the <i>Custom Options</i> box below.</li>
				</ol>
				<p><a target="_blank" href="http://ionicons.com/">ionicons</a> are supported as node icons.</p>
				<label class='control-label'>Node Query {$nodequery_help}</label>
				<p>{$this->page->render_textarea($this->ctrlname('nodequery'), '', 'monospace vresize')}</p>
				<div class='checkbox top-margin-zero'>
					<label>{$this->page->render_checkbox($this->ctrlname('hidenodes'), 'ON', false)}Remove nodes missing in the Node Query result</label>
				</div>
				<label class='control-label'>Custom Options {$options_help}</label>
				<p>{$this->page->render_textarea($this->ctrlname('options'), $visjs_options, 'monospace vresize')}</p>
SETTINGS;
		}

		//--------------------------------------------------------------------------------------
		// override if additional scripts are needed for this type
		public /*void*/ function add_required_scripts() {
		//--------------------------------------------------------------------------------------
			add_javascript('https://cdnjs.cloudflare.com/ajax/libs/vis/4.16.1/vis.min.js');
			add_stylesheet('https://cdnjs.cloudflare.com/ajax/libs/vis/4.16.1/vis.min.css');
			add_stylesheet('https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
		}

		//--------------------------------------------------------------------------------------
		public /*string|false*/ function cache_get_js() {
		//--------------------------------------------------------------------------------------
			global $APP;

			if(!isset($APP['cache_dir']) || isset($_GET['nocache']))
				return false;

			$network_id = $this->page->get_stored_query_id();
			$dir = $this->cache_get_dir();

			// read cache
			$t = @filemtime($dir . '/' . $network_id . '.definition');

			if($t === false) // probably does not exist yet
				return false;

			if(time() - $t > $this->cache_get_ttl()) // cache expired
				return false;

			$cache = @file_get_contents($dir . '/' . $network_id . '.definition');
			if($cache === false)
				return false;

			// check version
			if(preg_match('/^<!-- (?P<ver>\d+) -->\n/', $cache, $matches) !== 1)
				return false; // can't find version info

			if(!isset($matches['ver']))
				return false;

			if(intval($matches['ver']) < $this->cache_get_version())
				return false; // this code is newer version -> don't return cache

			$pos = @file_get_contents($dir . '/' . $network_id . '.node_positions');
			if($pos !== false)
				$cache .= $pos;

			return $cache;
		}

		//--------------------------------------------------------------------------------------
		public /*bool*/ function cache_put_js($js) {
		//--------------------------------------------------------------------------------------
			global $APP;
			if(!isset($APP['cache_dir']) || isset($_GET['nocache']))
				return false;

			$network_id = $this->page->get_stored_query_id();
			$dir = $this->cache_get_dir();

			if(!@is_dir($dir)) {
				if(!@mkdir($dir, 0777, true)) {
					$error = error_get_last();
					proc_error($error['message']);
				}
			}

			// remove stored node positions (if any)
			@unlink($dir . '/' . $network_id . '.node_positions');

			// make version
			$version = "<!-- " . $this->cache_get_version() . " -->\n";

			return @file_put_contents($dir . '/' . $network_id . '.definition', $version . $js);
		}

		//--------------------------------------------------------------------------------------
		// this is called from MODE_FUNC
		public static /*void*/ function cache_positions_async() {
		//--------------------------------------------------------------------------------------
			header('Concent-Type: text/plain; charset:utf-8');

			if(!isset($_POST['cache_dir']) || !isset($_POST['network_id']) || !isset($_POST['method'])) {
				echo 'ERROR';
				return;
			}

			$cache_file = $_POST['cache_dir'] . '/' . $_POST['network_id'] . '.node_positions';

			if($_POST['method'] === 'poll_node_cache') {
				// check whether we need to put node positions cache
				if(!@file_exists($cache_file))
					echo '1'; // need put cache
				else
					echo '0'; // no need to put cache
			}
			else if($_POST['method'] === 'put_node_cache') {
				// write node positions cache
				// check whether node positions already exist
				if(@file_exists($cache_file))
					return;

				// otherwise make file
				$node_pos = '<script>' . $_POST['node_positions'] . '</script>';
				@file_put_contents($cache_file, $node_pos);
				echo 'OK';
			}
		}

		//--------------------------------------------------------------------------------------
		// returns html/js to render page
		public /*string*/ function get_js(/*PDOStatement*/ $query_result) {
		//--------------------------------------------------------------------------------------
			global $APP;
			$options_json = trim($this->page->get_post($this->ctrlname('options'), ''));
			if($options_json === '')
				$options_json = '{}';

			$options = json_decode($options_json, true);

			$iterations = isset($options['physics']['stabilization']['iterations']) ? $options['physics']['stabilization']['iterations'] : 300;

			$nodes = array();
			$edges = array();

			while($row = $query_result->fetch(PDO::FETCH_ASSOC)) {
				$edge = array(
					'from' => $row['source'],
					'to' => $row['target']);

				if(isset($row['weight']))
					$edge['width'] = $row['weight'];

				if(isset($row['label']))
					$edge['label'] = strval($row['label']);

				if(isset($row['options'])) {
					$edge_options = json_decode($row['options'], true);
					if($edge_options !== null)
						$edge += $edge_options;
				}

				$edges[] = $edge;

				// add source and/or target nodes to node list
				foreach(array('source', 'target') as $col)
					if(!isset($nodes[$row[$col]]))
						$nodes[$row[$col]] = array('id' => $row[$col], 'label' => $row[$col]);
			}


			do { // check nodes query to define nodes list
				$nodes_sql = trim($this->page->get_post($this->ctrlname('nodequery'), ''));
				if($nodes_sql != '' && mb_substr(mb_strtolower($nodes_sql), 0, 6) !== 'select') {
					proc_error('Invalid Node Query. Only SELECT statements are allowed. Query is ignored.');
					break;
				}

				if($nodes_sql === '')
					break;

				$nodes_stmt = $this->page->db()->prepare($nodes_sql);
				if($nodes_stmt === false) {
					proc_error('Node Query produces error during preparation.', $this->page->db());
					break;
				}

				if($nodes_stmt->execute() === false) {
					proc_error('Node Query produces error during execution.', $this->page->db());
					break;
				}

				$node_query_ids = array();

				while($node = $nodes_stmt->fetch(PDO::FETCH_ASSOC)) {
					if(!isset($nodes[$node['id']]))
						continue;

					$node_query_ids[] = $node['id'];

					$nodes[$node['id']]['label'] = $node['label'];
					if(!isset($node['options']))
						continue;

					$node_options = json_decode($node['options'], true);
					if($node_options === null)
						continue;

					$nodes[$node['id']] += $node_options;
				}

				if('ON' != $this->page->get_post($this->ctrlname('hidenodes'), 'OFF'))
					continue;

				// remove nodes that are not in the node query result
				foreach($nodes as $node_id => $node_info)
					if(!in_array($node_id, $node_query_ids))
						unset($nodes[$node_id]);

			} while(false);

			$nodes_json = json_encode(array_values($nodes));
			$edges_json = json_encode($edges);

			// JS code for trying to get cached node positions
			$positions_js = '';
			if($this->page->is_stored_query() && isset($APP['cache_dir'])) {
				$cache_dir = $this->cache_get_dir();
				$network_id = $this->page->get_stored_query_id();
				$func_url = '?' . http_build_query(array(
					'mode' => MODE_FUNC,
					'target' => VISJS_NETWORK_CACHE_POSITIONS
				));

				$positions_js = <<<POS_JS
				// if cache dir is set, we first poll whether we need to update cache
				// if so, we put the current network in the cache
				if('$cache_dir' != '' && !stabilization_cancelled) {
					stabilization_cancelled = true; // we do this caching only once
					// put node cache
					$.post(
						'$func_url',
						{
							method: 'poll_node_cache',
							network_id: '$network_id',
							cache_dir: '$cache_dir',
						},
						function(data) {
							console.log('need to put node position cache? ' + data);
							if(data != '1')
								return;

							// build cache data
							var pos_js = 'function set_node_positions() { network.setOptions({physics: {enabled:false}}); stabilization_cancelled = true; var node_positions = ';
							var pos_xy = network.getPositions();
							pos_js += JSON.stringify(pos_xy);
							pos_js += '; for(var node_id in node_positions) { ';
							pos_js += 'if(!node_positions.hasOwnProperty(node_id)) continue; ';
							pos_js += 'network.moveNode(node_id, node_positions[node_id].x, node_positions[node_id].y);'
							pos_js += '} network.fit(); } ';

							$.post('$func_url', {
								method: 'put_node_cache',
								network_id: '$network_id',
								cache_dir: '$cache_dir',
								node_positions: pos_js
							},
							function(data) {
								console.log('caching returns: ' + data);
							});
						}
					);
				}
POS_JS;
			}


			$js = <<<EOT
			</script>
			<div id="network-loading-progress" class="progress">
				<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="$iterations" style="width:0%"></div>
			</div>
			<script>
			var network;
			var stabilization_cancelled = false;

			document.addEventListener('DOMContentLoaded', function()
			{
				var container = document.getElementById('chart_div');

				var data = {
					nodes: new vis.DataSet(
						$nodes_json
					),
					edges: new vis.DataSet(
						$edges_json
					)
				};

				var options = $options_json;

				var progress_bar = null;
				if(typeof set_node_positions === 'function') { // we'll come from the cache, yo!
					options.physics = false;
					options.edges['smooth'] = {type: 'continuous'}; // dynamic edges have invisible nodes that spoil the drawing
				}
				else {
					progress_bar = $('#network-loading-progress')
						.offset($(container).offset())
						.css('width', $(container).width())
						.toggle();
				}

				network = new vis.Network(container, data, options);

				network.on('stabilizationProgress', function(arg) {
					setTimeout(function() {
						progress_bar.find('div')
							.attr('aria-valuenow', arg.iterations)
							.css('width', (100 * arg.iterations / arg.total) + '%');
					}, 0);
				});

				network.once('stabilizationIterationsDone', function() {
					setTimeout(function() {
						progress_bar.find('div')
							.attr('aria-valuenow', $iterations)
							.css('width', '100%')
							.html('Network is still stabilizing, but ready to explore. <a style="" id="stop_simu" href="javascript:void(0)">Click here to freeze network</a>');

						network.fit();

						$('#stop_simu').on('click', function() {
							network.stopSimulation();
							stabilization_cancelled = true;
							network.setOptions({ physics: false });
						});
					}, 0);
				});

				network.on('doubleClick', function(arg) {
					var clicked_item = null;
					if(arg.nodes.length == 1)
						clicked_item = data.nodes.get(arg.nodes[0]);
					else if(arg.edges.length == 1)
						clicked_item = data.edges.get(arg.edges[0]);
					if(clicked_item !== null && clicked_item.hasOwnProperty('href_view'))
						window.open(clicked_item.href_view).focus();
				});


				network.on('stabilized', function(arg) {
					progress_bar.hide();

					$positions_js

					network.setOptions({ physics: false });
				});

				if(typeof set_node_positions === 'function')
					set_node_positions();
			});
			</script>
EOT;
			return $js;
		}
	};
?>
