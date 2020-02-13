<?php
	//==========================================================================================
	class dbWebGenChart_graph3d_visjs extends dbWebGenChart {
	//==========================================================================================
        const DEFAULT_CHART_STYLE = 'bar';

		//--------------------------------------------------------------------------------------
		public /*string*/ function settings_html() {
		//--------------------------------------------------------------------------------------
            $charTypes = [];
            foreach([
                'bar', 'bar-color', 'bar-size',
                'dot', 'dot-line', 'dot-color', 'dot-size',
                'line', 'grid', 'surface'
            ] as $type) {
                $chartTypes[$type] = l10n("chart.graph3d-visjs.style.$type");
            }
            
            return l10n(
                'chart.graph3d-visjs.settings',
                $this->page->render_select($this->ctrlname('style'), self::DEFAULT_CHART_STYLE, $chartTypes),
                get_help_popup(l10n('chart.graph3d-visjs.options.help-head'), l10n('chart.graph3d-visjs.options.help-body')),
                $this->page->render_textarea($this->ctrlname('options'), '', 'monospace vresize')
			);
		}

		//--------------------------------------------------------------------------------------
		public /*void*/ function add_required_scripts() {
		//--------------------------------------------------------------------------------------
			add_javascript(ENGINE_PATH_HTTP . 'node_modules/vis-graph3d/dist/vis-graph3d.min.js');
		}
        
        //--------------------------------------------------------------------------------------
		public /*string*/ function get_js(/*PDOStatement*/ $query_result) {
		//--------------------------------------------------------------------------------------
			$options_js = trim($this->page->get_post($this->ctrlname('options'), ''));
			if($options_js === '') {
                $options_js = '{}';
            }

            $axisTitles = [];
            for($i = 0; $i < 3; $i++) {
                $axisTitles[$i] = json_encode($query_result->getColumnMeta($i)['name']);
            }

            $axes = ['x', 'y', 'z'];
            $valueLabels = [];
            foreach($axes as $a) {
                $valueLabels[$a] = 'v';
            }

            $count = 0;
            $dataArray = [];
            $axisValueMap = [];
            while($row = $query_result->fetch(PDO::FETCH_NUM)) {
                if($count++ === 0) { // check for text discrete axes
                    foreach($axes as $i => $a) {
                        if(is_string($row[$i])) {
                            $axisValueMap[$a] = [];
                            $valueLabels[$a] = "axisValueMap.{$a}[v]";
                        }
                    }
                }
                $dataObj = [];
                foreach($axes as $c => $a) {
                    if(isset($axisValueMap[$a])) {
                        $num = array_search($row[$c], $axisValueMap[$a]);
                        $dataObj[$a] = $num === false ? array_push($axisValueMap[$a], $row[$c]) - 1 : $num;
                    }
                    else {
                        $dataObj[$a] = $row[$c];
                    }
                }
                if(isset($row[3]) && $row[3] !== null)
                    $dataObj['style'] = $row[3];
                if(isset($row[4]) && $row[4] !== null)
                    $dataObj['tooltip'] = $row[4];
                if(isset($row[5]) && $row[5] !== null)
                    $dataObj['filter'] = $row[5];

                $dataArray[] = $dataObj;
            }

            if($count === 0) {
                proc_info(l10n('chart.empty-result'));
                return '';
            }

            $style_js = json_encode($this->page->get_post($this->ctrlname('style'), self::DEFAULT_CHART_STYLE));
            $dataArray_js = json_encode($dataArray);
            foreach($axes as $a) {
                if(!isset($axisValueMap[$a]))
                    $axisValueMap[$a] = [];
            }
            $axisValueMap_js = json_encode($axisValueMap);
            
            $js = <<<EOT
            var graph3d;
            var dataArray = $dataArray_js;
            document.addEventListener('DOMContentLoaded', () => {
				let container = $('#chart_div');
                let data = new vis.DataSet(dataArray);
                let axisValueMap = $axisValueMap_js;           
                let options = {
                    width: '100%',
                    height: '100%',
                    style: $style_js,
                    showPerspective: false,
                    verticalRatio: 1.0,
                    xValueLabel: v => {$valueLabels['x']},
                    yValueLabel: v => {$valueLabels['y']},
                    zValueLabel: v => {$valueLabels['z']},
                    xLabel: {$axisTitles[0]},
                    yLabel: {$axisTitles[1]},
                    zLabel: {$axisTitles[2]},
                    tooltip: d => d.data.tooltip
                };
                $.extend(options, $options_js);
                graph3d = new vis.Graph3d(container[0], data, options);
                container.deferredResize(() => graph3d.redraw(), 100);
            });
EOT;
			return $js;
		}
	};