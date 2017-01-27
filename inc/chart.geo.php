<?
	//==========================================================================================
	class dbWebGenChart_geo extends dbWebGenChart_Google {
	//==========================================================================================

		//--------------------------------------------------------------------------------------
		// form field @name must be prefixed with exact charttype followed by dash
		public function settings_html() {
		//--------------------------------------------------------------------------------------
			$region_helptext = <<<HELPTEXT
				Can be one of the following:<ul style="padding-left:1.25em">
					<li><code>world</code> - A geochart of the entire world.</li>
					<li>
					  A continent or a sub-continent, specified by its
					  <a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#Continent_Hierarchy">3-digit code</a>, e.g., <code>011</code> for Western Africa.
					</li>
					<li>
					  A country, specified by its
					  <a target="_blank" href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2">ISO 3166-1 alpha-2</a> code,
					  e.g., <code>AU</code> for Australia.
					</li>
					<li>
					  A state in the United States, specified by its
					  <a target="_blank" href="http://en.wikipedia.org/wiki/ISO_3166-2:US">ISO 3166-2:US</a> code, e.g.,
					  <code>US-AL</code> for Alabama.
					</li>
				</ul>
HELPTEXT;
			$region_helphtml = get_help_popup('Region', $region_helptext);

			return <<<HTML
			<p>Renders a map of a country, a continent, or a region with markers or colored areas depending on the display mode.</p>

			<div class="form-group">
			<label class="control-label">Display Mode</label>
				<div class="radio"  style="margin-top:0">
					<label class="">{$this->page->render_radio($this->ctrlname('mode'), 'markers', true)}<i>Markers</i> &mdash; uses circles to designate regions that are scaled according to a specified value (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#markers-mode-format">required columns</a>)</label>
				</div>
				<div class="radio">
					<label class="">{$this->page->render_radio($this->ctrlname('mode'), 'regions')}<i>Regions</i> &mdash; colors whole regions, such as countries, provinces, or states (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#regions-mode-format">required columns</a>)</label>
				</div>
				<div class="radio">
					<label class="">{$this->page->render_radio($this->ctrlname('mode'), 'text')}<i>Text</i> &mdash; labels the regions with identifiers (e.g., "Russia" or "Asia") (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#text-mode-format">required columns</a>)</label>
				</div>
			</div>

			<div class="form-group">
				<label for="{$this->ctrlname('region')}" class="control-label">Displayed Region {$region_helphtml}</label>
				{$this->page->render_textbox($this->ctrlname('region'), 'world')}
			</div>
HTML;
		}

		//--------------------------------------------------------------------------------------
		public function add_required_scripts() {
		//--------------------------------------------------------------------------------------
			parent::add_required_scripts();
			add_javascript('https://www.google.com/jsapi');
		}

		//--------------------------------------------------------------------------------------
		// need to override this because of material options conversion
		public function before_draw_js() {
		//--------------------------------------------------------------------------------------
			return '';
		}

		//--------------------------------------------------------------------------------------
		protected function options() {
		//--------------------------------------------------------------------------------------
			return parent::options() + array(
				'region' => $this->page->get_post($this->ctrlname('region')),
				'displayMode' => $this->page->get_post($this->ctrlname('mode')),
				#'sizeAxis' => array('minSize' =>  1,  'maxSize' => 10),
			);
		}

		//--------------------------------------------------------------------------------------
		// return google charts class name to instantiate
		public function class_name() {
		//--------------------------------------------------------------------------------------
			return 'google.visualization.GeoChart';
		}

		//--------------------------------------------------------------------------------------
		// return google charts packages to include
		public function packages() {
		//--------------------------------------------------------------------------------------
			return array('geochart');
		}
	};
?>
