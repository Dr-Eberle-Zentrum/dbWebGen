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
				<p>{$this->page->render_textarea('network_visjs-nodequery', '', 'monospace vresize')}</p>
				<div class='checkbox top-margin-zero'>
					<label>{$this->page->render_checkbox('network_visjs-hidenodes', 'ON', false)}Remove nodes missing in the Node Query result</label>
				</div>				
				<label class='control-label'>Custom Options {$options_help}</label>				
				<p>{$this->page->render_textarea('network_visjs-options', $visjs_options, 'monospace vresize')}</p>
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
		// returns html/js to render page
		public /*string*/ function get_js(/*PDOStatement*/ $query_result) {
		//--------------------------------------------------------------------------------------
			global $APP;			
			$options_json = trim($this->page->get_post('network_visjs-options', ''));
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
				$nodes_sql = trim($this->page->get_post('network_visjs-nodequery', ''));
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

				if('ON' != $this->page->get_post('network_visjs-hidenodes', 'OFF')) 
					continue;
				
				// remove nodes that are not in the node query result
				foreach($nodes as $node_id => $node_info)
					if(!in_array($node_id, $node_query_ids))
						unset($nodes[$node_id]);
					
			} while(false);
			
			$nodes_json = json_encode(array_values($nodes));
			$edges_json = json_encode($edges);
			
			$js = <<<EOT
			</script>
			<div id="network-loading-progress" class="progress">
				<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="$iterations" style="width:0%"></div>
			</div>		
			<script>
			var network;				
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
				
				var progress_bar = $('#network-loading-progress')
					.offset($(container).offset())
					.css('width', $(container).width())
					.toggle();
				
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
							.html('Network is still stabilizing, but ready to explore. <a style="" id="stop_simu" href="javascript:void(0)">Stop stabilization</a>');
							
						$('#stop_simu').on('click', function() {
							network.stopSimulation();							
						});
						
						network.fit();
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
				});
			});
			</script>
EOT;
			return $js;
		}
	};
?>