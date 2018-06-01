<?php
	//==========================================================================================
	// needed to make table display work
	class FakePDOStatement {
	//==========================================================================================
		protected $table = array();
		protected $cur_row = 0;
		protected $num_rows = 0;

		//--------------------------------------------------------------------------------------
		public function add_row($row) {
		//--------------------------------------------------------------------------------------
			$this->table[] = $row;
			$this->num_rows++;
		}

		//--------------------------------------------------------------------------------------
		public function fetch($boo /*ignored, assumed PDO::FETCH_ASSOC*/) {
		//--------------------------------------------------------------------------------------
			if($this->cur_row >= $this->num_rows)
				return false;
			return $this->table[$this->cur_row++];
		}

		//--------------------------------------------------------------------------------------
		public function sort($key, $asc) {
		//--------------------------------------------------------------------------------------
			usort($this->table, function($a, $b) use ($key, $asc) {
				if($a[$key] == $b[$key]) return 0;
				return $a[$key] < $b[$key] ? ($asc ? -1 : 1) : ($asc ? 1 : -1);
			});
		}

		//--------------------------------------------------------------------------------------
		public function limit($n) {
		//--------------------------------------------------------------------------------------
			if($n >= $this->num_rows)
				return;
			array_splice($this->table, $n);
			$this->num_rows = $n;
		}
	}

	//==========================================================================================
	class dbWebGenChart_sna extends dbWebGenChart_Table {
	//==========================================================================================

		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() {
		//--------------------------------------------------------------------------------------
			return l10n(
				'chart.sna.settings',
				get_help_popup('Node Query', l10n('chart.sna.nodequery-help')),
				$this->page->render_textarea($this->ctrlname('nodequery'), '', 'monospace vresize'),
				$this->page->render_checkbox($this->ctrlname('allowHtml'), 'ON', true),
				$this->page->render_textbox($this->ctrlname('nodeColumnLabel'), l10n('chart.sna.node-column-label')),
				$this->page->render_select($this->ctrlname('sort'), 'cb', array(
					'cb' => l10n('chart.sna.sort-cb'),
					'cd' => l10n('chart.sna.sort-cd'),
					'node' => l10n('chart.sna.sort-node')
				)),
				$this->page->render_textbox($this->ctrlname('limit'), '')
			);
		}

		//--------------------------------------------------------------------------------------
		// shall we subtract scrollbar from visualization width? default true
		protected function shall_subtract_scrollbar() {
		//--------------------------------------------------------------------------------------
			return false;
		}

		//--------------------------------------------------------------------------------------
		protected function options() {
		//--------------------------------------------------------------------------------------
			return parent::options() + array(
				'allowHtml' => ($this->page->get_post($this->ctrlname('allowHtml')) === 'ON')
			);
		}

		//--------------------------------------------------------------------------------------
		// return google charts class name to instantiate
		public function class_name() {
		//--------------------------------------------------------------------------------------
			return 'google.visualization.Table';
		}

		//--------------------------------------------------------------------------------------
		// return google charts packages to include
		public function packages() {
		//--------------------------------------------------------------------------------------
			return array('table');
		}

		//--------------------------------------------------------------------------------------
		public /*array*/ function get_columns(&$stmt) {
		//--------------------------------------------------------------------------------------
			// we need to fake column infos
			$this->column_infos = array(
				array('js_type' => 'string'),
				array('js_type' => 'number'),
				array('js_type' => 'number')
			);

			return array(
				array(
					'type' => 'string',
					'id' => 'node',
					'label' => $this->page->get_post($this->ctrlname('nodeColumnLabel'))
				),
				array(
					'type' => 'number',
					'id' => 'cb',
					'label' => l10n('chart.sna.result.betweenness-centrality')
				),
				array(
					'type' => 'number',
					'id' => 'cd',
					'label' => l10n('chart.sna.result.degree-centrality')
				),
			);
		}

		//--------------------------------------------------------------------------------------
		// returns js code to fill the chart div
		public function get_js($query_result) {
		//--------------------------------------------------------------------------------------
			// $query_result holds the edge list
			$E = array();
			while($e = $query_result->fetch(PDO::FETCH_ASSOC))
				$E[] = array($e['source'], $e['target']);

			$sna = new NetworkSNA;
			$sna->init($E);
			$sna->calc_degree_centralities($Cd, false);
			$sna->calc_betweenness_centralities($Cb, false, true);

			$V = $sna->get_vertex_list();
			//asort($V);
			$allow_html = ($this->page->get_post($this->ctrlname('allowHtml')) === 'ON');

			$node_labels = array();
			// fetch node labels:
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
				while($node = $nodes_stmt->fetch(PDO::FETCH_ASSOC))
					$node_labels[$node['id']] = strval($node['label']);
			} while(false);

			$query_result = new FakePDOStatement;
			foreach($V as $v) {
				$label = isset($node_labels[$v]) ? $node_labels[$v] : strval($v);
				$query_result->add_row(array(
					'node' => $allow_html ? $label : html($label),
					'cb' => isset($Cb[$v]) ? $Cb[$v] : 0,
					'cd' => isset($Cd[$v]) ? $Cd[$v] : 0
				));
			}
			$sort_key = $this->page->get_post($this->ctrlname('sort'), 'cb');
			$sort_asc = $sort_key == 'node' ? true : false;
			$query_result->sort($sort_key, $sort_asc);
			$limit = trim($this->page->get_post($this->ctrlname('limit'), ''));
			if(preg_match('/^\d+$/', $limit) && ($limit = intval($limit)) > 0)
				$query_result->limit($limit);
			return parent::get_js($query_result);
		}
	};

	//==========================================================================================
	class NetworkSNA {
	//==========================================================================================
		protected $E = null; // edge list
		protected $N = null; // neighbor list
		protected $V = null; // vertex list
		protected $directed = false;

		//--------------------------------------------------------------------------------------
		public function init(&$edge_list) {
		//--------------------------------------------------------------------------------------
			$this->E = $edge_list;
			$this->build_vertex_and_neighbor_list();
		}

		//--------------------------------------------------------------------------------------
		public function get_vertex_list() {
		//--------------------------------------------------------------------------------------
			return $this->V;
		}

		//--------------------------------------------------------------------------------------
		protected function build_vertex_and_neighbor_list() {
		//--------------------------------------------------------------------------------------
			$this->N = array();
			$V = array();

			foreach($this->E as $e) {
				$V[$e[0]] = 1;
				$V[$e[1]] = 1;

				if(!isset($this->N[$e[0]]))
					$this->N[$e[0]] = array($e[1]);
				else
					$this->N[$e[0]][] = $e[1];
				if(!$this->directed) {
					if(!isset($this->N[$e[1]]))
						$this->N[$e[1]] = array($e[0]);
					else
						$this->N[$e[1]][] = $e[0];
				}
			}
			$this->V = array_keys($V);
		}

		//--------------------------------------------------------------------------------------
		public function calc_degree_centralities(&$Cd, $sort) {
		//--------------------------------------------------------------------------------------
			$Cd = array();
			foreach($this->N as $v => $a)
				$Cd[$v] = count($a);
			if($sort)
				arsort($Cd);
		}

		//--------------------------------------------------------------------------------------
		public function calc_betweenness_centralities(&$Cb, $sort, $normalize) {
		//--------------------------------------------------------------------------------------
			// init Cb assoc
			$Cb = array();
			foreach($this->V as $v)
				$Cb[$v] = 0.;

			foreach($this->V as $s) {
				$S = new SplStack;
				$P = $sigma = $d = array();
				foreach($this->V as $w) {
					$P[$w] = array();
					$sigma[$w] = 0.;
					$d[$w] = -1;
				}
				$sigma[$s] = 1.;
				$d[$s] = 0.;
				$Q = new SplQueue;
				$Q->enqueue($s);
				while(!$Q->isEmpty()) {
					$v = $Q->dequeue();
					$S->push($v);
					if(isset($this->N[$v])) {
						foreach($this->N[$v] as $w) {
							if($d[$w] < 0) {
								$Q->enqueue($w);
								$d[$w] = $d[$v] + 1;
							}
							if($d[$w] == $d[$v] + 1) {
								$sigma[$w] += $sigma[$v];
								$P[$w][] = $v;
							}
						}
					}
				}

				$delta = array();
				foreach($this->V as $v)
					$delta[$v] = 0.;

				while(!$S->isEmpty()) {
					$w = $S->pop();
					foreach($P[$w] as $v)
						$delta[$v] = $delta[$v] + ($sigma[$v] / $sigma[$w]) * (1 + $delta[$w]);
					if($w != $s)
						$Cb[$w] += $delta[$w];
				}
			}

			if($sort)
				arsort($Cb);

			if($normalize) {
				$min = false; $max = 0;
				foreach($Cb as $v => $c) {
					if($min === false)
						$min = $max = $c;
					else {
						if($c > $max) $max = $c;
						if($c < $min) $min = $c;
					}
				}
				foreach($Cb as $v => $c)
					$Cb[$v] = ($c - $min) / ($max - $min);
			}
		}
	}
?>
