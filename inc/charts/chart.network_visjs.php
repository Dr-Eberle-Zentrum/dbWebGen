<?php
	//==========================================================================================
	class dbWebGenChart_network_visjs extends dbWebGenChart {
	//==========================================================================================

		//--------------------------------------------------------------------------------------
		// for downwards compatibility
		public function get_param($param_name, $default = null) {
		//--------------------------------------------------------------------------------------
			if($this->page->is_cache_enabled() && $param_name == 'cache_ttl' && null == $this->page->get_post($this->ctrlname($param_name)))
				return strval(DEFAULT_CACHE_TTL);
			else return parent::get_param($param_name, $default);
		}

		//--------------------------------------------------------------------------------------
		// for downwards compatibility
		public function get_param_checkbox($param_name, $default = false) {
		//--------------------------------------------------------------------------------------
			if($this->page->is_cache_enabled() && $param_name == 'caching' && null == $this->page->get_post($this->ctrlname($param_name)))
				return true;
			else return parent::get_param_checkbox($param_name, $default);
		}

		//--------------------------------------------------------------------------------------
		// returns html form for chart settings
		public /*string*/ function settings_html() {
		//--------------------------------------------------------------------------------------
			$visjs_options = <<<OPTIONS
{
  "layout": {
    "improvedLayout": false
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
			return l10n(
				'chart.network-visjs.settings',
				get_help_popup('Node Query', l10n('chart.network-visjs.nodequery-help')),
				$this->page->render_textarea($this->ctrlname('nodequery'), '', 'monospace vresize'),
				$this->page->render_checkbox($this->ctrlname('hidenodes'), 'ON', false),
				get_help_popup('Custom Options', l10n('chart.network-visjs.options-help')),
				$this->page->render_textarea($this->ctrlname('options'), $visjs_options, 'monospace vresize')
			);
		}

		//--------------------------------------------------------------------------------------
		// override if additional scripts are needed for this type
		public /*void*/ function add_required_scripts() {
		//--------------------------------------------------------------------------------------
			add_javascript(ENGINE_PATH_HTTP . 'node_modules/vis/dist/vis.min.js');
			add_stylesheet(ENGINE_PATH_HTTP . 'node_modules/vis/dist/vis.min.css');
			add_stylesheet(ENGINE_PATH_HTTP . 'node_modules/ionicons/css/ionicons.min.css');
		}

		//--------------------------------------------------------------------------------------
		public /*string|false*/ function cache_get_js() {
		//--------------------------------------------------------------------------------------
			$cache = parent::cache_get_js();
			if($cache === false);
				return false;
			$pos = @file_get_contents($this->cache_get_dir() . '/' . $this->page->get_stored_query_id() . '.node_positions');
			if($pos !== false)
				$cache .= $pos;
			return $cache;
		}

		//--------------------------------------------------------------------------------------
		public /*bool*/ function cache_put_js($js) {
		//--------------------------------------------------------------------------------------
			$ret = parent::cache_put_js($js);

			// remove stored node positions (if any)
			@unlink($this->cache_get_dir() . '/' . $this->page->get_stored_query_id() . '.node_positions');
			return $ret;
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
				@chmod($cache_file, 0777);
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
					proc_error(l10n('chart.network-visjs.node-query-invalid'));
					break;
				}

				if($nodes_sql === '')
					break;

				$nodes_stmt = $this->page->db()->prepare($nodes_sql);
				if($nodes_stmt === false) {
					proc_error(l10n('chart.network-visjs.node-query-prep'), $this->page->db());
					break;
				}

				if($nodes_stmt->execute() === false) {
					proc_error(l10n('chart.network-visjs.node-query-exec'), $nodes_stmt);
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

			$stab_info_js = json_encode(l10n('chart.network-visjs.stabilizing-info'));
			$stab_stop_js = json_encode(l10n('chart.network-visjs.stabilizing-stop'));

			$js = <<<EOT
			</script>
			<div id="network-loading-progress" class="progress">
				<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="$iterations" style="width:0%"></div>
			</div>
			<script>
			var network;
			var stabilization_cancelled = false;

			document.addEventListener('DOMContentLoaded', function() {
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
							.html($stab_info_js + ' <a style="" id="stop_simu" href="javascript:void(0)">' + $stab_stop_js + '</a>');
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
