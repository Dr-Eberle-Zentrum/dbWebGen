<?php
    $_L10N = array(
        'boolean-field.default.yes' => 'Yes',
        'boolean-field.default.no' => 'No',

        'chart-type.annotated-timeline' => 'Annotated Timeline',
        'chart-type.bar' => 'Bar Chart',
        'chart-type.candlestick' => 'Candlestick Chart',
        'chart-type.geo' => 'Geo Chart',
        'chart-type.leaflet' => 'Leaflet Map',
        'chart-type.network-visjs' => 'Network (vis.js)',
        'chart-type.pie' => 'Pie Chart',
        'chart-type.sankey' => 'Sankey Chart',
        'chart-type.table' => 'Table',
        'chart-type.timeline' => 'Timeline',
        'chart-type.plaintext' => 'Plain Text',
        'chart-type.sna' => 'Social Network Analysis',

        'chart.plaintext.settings' => <<<HTML
            <p>Displays the first column of the first row of the query result as plain text.</p>
HTML
        ,
        'chart.annotated-timeline.settings' => <<<HTML
            <p>Allows producing an interactive time series line chart with annotations. The first column must be a date, all subsequent columns numeric (<a href="https://developers.google.com/chart/interactive/docs/gallery/annotationchart#data-format" target="_blank">see here</a>).</p>
            <div class="form-group">
                <label class="control-label">Options</label>
                <div class='checkbox top-margin-zero'>
                    <label>$1 Show separate scale for second data series</label>
                </div>
            </div>
HTML
        ,
        'chart.bar.settings' => <<<HTML
            <p>Renders data as a bar chart. Put group labels in the 1st column, followed by one column per group listing the data (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/barchart#data-format">see details</a>).</p>
            <div class="form-group">
                <label class="control-label">Bar Direction</label>
                <div>
                    <label class="radio-inline">$1 Horizontal</label>
                    <label class="radio-inline">$2 Vertical</label>
                </div>
            </div>
            <!-- STACKED DOES NOT WORK !
            <div class="form-group">
                <label class="control-label">Stacking of Values</label>
                <div>$3</div>
            </div>-->
HTML
        ,
        'chart.candlestick.settings' => <<<HTML
            <p>A candlestick chart is used to show an opening and closing value overlaid on top of a total variance. It requires <a target=_blank href="https://developers.google.com/chart/interactive/docs/gallery/candlestickchart#data-format">four columns</a> in the query result.</p>
HTML
        ,
        'chart.geo.region-helptext' => <<<HELPTEXT
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
HELPTEXT
        ,
        'chart.geo.settings' => <<<HTML
            <p>Renders a map of a country, a continent, or a region with markers or colored areas depending on the display mode.</p>
            <div class="form-group">
            <label class="control-label">Display Mode</label>
                <div class="radio"  style="margin-top:0">
                    <label class="">$1 <i>Markers</i> &mdash; uses circles to designate regions that are scaled according to a specified value (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#markers-mode-format">required columns</a>)</label>
                </div>
                <div class="radio">
                    <label class="">$2 <i>Regions</i> &mdash; colors whole regions, such as countries, provinces, or states (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#regions-mode-format">required columns</a>)</label>
                </div>
                <div class="radio">
                    <label class="">$3 <i>Text</i> &mdash; labels the regions with identifiers (e.g., "Russia" or "Asia") (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#text-mode-format">required columns</a>)</label>
                </div>
            </div>
            <div class="form-group">
                <label for="$4" class="control-label">Displayed Region $5</label> $6
            </div>
HTML
        ,
        'chart.leaflet.settings' => <<<HTML
            <p><a target="_blank" href="http://leafletjs.com/">Leaflet</a> offers mobile-friendly interactive maps.</p>
            <div class='form-group'>
                <label class="control-label">Data Format</label>
                <div class="radio"  style="margin-top:0">
                    <label class="">$1 <i>Point Coordinates</i> &mdash; the first two columns in the query result are latitude (<i>y</i>) and longitude (<i>x</i>) of each record</label>
                </div>
                <div class="radio"  style="margin-top:0">
                    <label class="">$2 <i>Well-Known-Text</i> &mdash; the first column in the query result is the <a target="_blank" href="https://en.wikipedia.org/wiki/Well-known_text">WKT representation</a> of each record (this allows arbitrary shapes like polygons, multilines, etc. in addition to points)</label>
                </div>
                <p>All additional columns will be put in the marker popups as a table. Only records with non-<code>NULL</code> geometries are included in the result visualization.</p>
            </div>
            <div class='form-group'>
                <label class="control-label">Base Map Tiles Provider</label>
                <p>$3</p>
                <div>Custom URL template (optional; overrides the above selection):</div>
                <div>$4</div>
            </div>
            <div class="form-group">
                <label class="control-label">Display Options</label>
                <div class='checkbox top-margin-zero'><label>$5 Show Scale</label></div>
                <div class='checkbox'><label>$6 Show Overview Map</label></div>
                <div class='checkbox'>Maximum Zoom Level (leave empty to tile provider&apos;s default): $7</div>
                <div class='checkbox'>Attribution (HTML) to display in bottom-right corner: $8</div>
                <label class="control-label">Spatial Reference System</label>
                <p>Select the coordinate system that Leaflet should use. The source data needs to be transformed to this projection.</p>
                <div class='form-group'>$9</div>
            </div>
            <div class='form-group'>
                <label class="control-label">Additional JavaScript Code</label>
                <p>$10</p>
            </div>
HTML
        ,
        'chart.leaflet.no-data' => '<b>Note:</b> Your query did not return any records.',
        'chart.network-visjs.options-help' => 'Adjust this JSON object to reflect your custom network options (see the <a target="_blank" href="http://visjs.org/docs/network/#options">documentation</a>).',
        'chart.network-visjs.nodequery-help' => <<<HTML
            <p>Optionally use this field to provide an SQL query that provides information about nodes. The query should have named columns as follows:</p>
            <ol class='columns'>
                <li><code>id</code>Node ID (string or integer)</li>
                <li><code>label</code>Node label (string)</li>
                <li><code>options</code>: <a target="_blank" href="http://visjs.org/docs/network/nodes.html">Node options</a> (JSON string) - optional; define individual options for each node in JSON notation. Individual options override node options provided in the <i>Custom Options</i> box below.</li>
            </ol>
HTML
        ,
        'chart.network-visjs.settings' => <<<HTML
            <p>Displays the query result as a network graph. The query result must be an edge list with the following named columns:</p>
            <ol class='columns'>
                <li><code>source</code>: Source node ID (string or integer)</li>
                <li><code>target</code>: Target node ID (string or integer)</li>
                <li><code>weight</code>: Edge weight controlling the width in pixels of the edge (number) - optional, default = 1</li>
                <li><code>label</code>: Edge label (string) - optional</li>
                <li><code>options</code>: <a target="_blank" href="http://visjs.org/docs/network/edges.html">Edge options</a> (JSON string) - optional; define individual options for each edge in JSON notation. Individual options override edge options provided in the <i>Custom Options</i> box below.</li>
            </ol>
            <p><a target="_blank" href="http://ionicons.com/">ionicons</a> are supported as node icons.</p>
            <label class='control-label'>Node Query $1</label>
            <p>$2</p>
            <div class='checkbox top-margin-zero'>
                <label>$3 Remove nodes missing in the Node Query result</label>
            </div>
            <label class='control-label'>Custom Options $4</label>
            <p>$5</p>
HTML
        ,
        'chart.network-visjs.node-query-invalid' => 'Invalid Node Query. Only SELECT statements are allowed. Query is ignored.',
        'chart.network-visjs.node-query-prep' => 'Node Query produces error during preparation.',
        'chart.network-visjs.node-query-exec' => 'Node Query produces error during execution.',
        'chart.network-visjs.stabilizing-info' => 'Network is still stabilizing, but ready to explore.',
        'chart.network-visjs.stabilizing-stop' => 'Click here to freeze network.',

        'chart.pie.settings' => <<<HTML
            <p>Creates a pie chart. Labels for pie slices must be in the first column. The second column must contain numerical values (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/piechart#data-format">details</a>).</p>
            <div class="form-group">
                <label class="control-label">Display Options</label>
                <div class='checkbox top-margin-zero'><label>$1 3D Pie</label></div>
                <div class='checkbox'><label>$2 Donut Style (ignored when 3D activated)</label></div>
            </div>
            <div class="form-group">
                <label class="control-label">Labeling of pie slices</label>
                <div>$3</div>
            </div>
            <div class="form-group">
                <label class="control-label">Position of Legend</label>
                <div>$4</div>
            </div>
HTML
        ,
        'chart.pie.pie-slice-text.percentage' => 'Percentage of total',
        'chart.pie.pie-slice-text.label' => 'Label (1st result column)',
        'chart.pie.pie-slice-text.value' => 'Absolute value (2nd result column)',
        'chart.pie.pie-slice-text.none' => 'None',
        'chart.pie.legend-position.bottom' => 'Below the chart',
        'chart.pie.legend-position.labeled' => 'Lines connect to outside labels',
        'chart.pie.legend-position.left' => 'Left of the chart',
        'chart.pie.legend-position.none' => 'Hide legend',
        'chart.pie.legend-position.right' => 'Right of the chart',
        'chart.pie.legend-position.top' => 'Above the chart',

        'chart.sankey.settings' => <<<HTML
            <p>A sankey diagram is a visualization used to depict a flow (links) from one set of values (nodes) to another. Sankeys are best used when you want to show a many-to-many mapping between two domains or multiple paths through a set of stages.</p>
            <p><a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/sankey#data-format">Required columns</a>:
            <ul class='columns'>
                <li>1. Source node (string)</li>
                <li>2. Target node (string)</li>
                <li>3. Weight (number)</li>
            </ul>
            </p>

HTML
        ,

        'chart.table.settings' => <<<HTML
            <p>The query result will be visualized as a table.</p>
            <div class="form-group">
                <label class="control-label">Options</label>
                <div class='checkbox top-margin-zero'>
                    <label>$1 Allow HTML inside cells</label>
                </div>
            </div>
HTML
        ,

        'chart.sna.settings' => <<<HTML
            <p>Displays an overview table of node centralities in a network graph.</p>
            <p>The query results needs to be a node list with the following columns:</p>
            <ol class='columns'>
                <li><code>source</code>: Source node ID (string or integer)</li>
                <li><code>target</code>: Target node ID (string or integer)</li>
            </ol>
            <label class='control-label'>Node Query $1</label>
            <p>$2</p>
            <!--<div class='checkbox top-margin-zero'>
                <label>$3 Entferne Knoten, die nicht im Ergebnis der Knotenabfrage vorkommen</label>
            </div>-->
            <div class="form-group">
                <label class="control-label">Options</label>
                <div class='checkbox top-margin-zero'>
                    <label>$3 Allow HTML in node labels</label>
                </div>
            </div>
            <div class="form-group">
                <label>Caption of node column in result table:</label>
                $4
            </div>
            <div class="form-group">
                <label>Sort result table by:</label>
                $5
            </div>
            <div class="form-group">
                <label>Limit number of results</label>
                <div>Enter a number to limit results or leave empty to show all results:</div>
                $6
            </div>
HTML
        ,

        'chart.sna.nodequery-help' => <<<HTML
            <p>SQL query that yields the node labels to display in the result table instead of the node IDs (optional). The following columns are required:</p>
            <ol class='columns'>
                <li><code>id</code>Node ID (string or integer), corresponding to <code>source</code>/<code>target</code> node IDs in the network query above</li>
                <li><code>label</code>Node label (string)</li>
            </ol>
HTML
        ,

        'chart.sna.help-link' => 'Click here to show an explanation of the columns',
        'chart.sna.help-content' => <<<HTML
            <li><b>Betweenness Centrality</b>:
                This number indicates how many paths through the network pass through the node. The highest value is 1, and the lowest value is 0. Nodes with a high betweenness centrality have relatively higher influence on the network as more paths pass through the node and therefore these nodes have higher control over information flow.</li>

            <li><b>Clustering Coefficient</b>:
                The clustering coefficient of a node reflects how well connected its direct neighbor nodes are. The value expresses the share of edges among direct neighbors in relation to the maximum possible number of edges among its neighbors. Nodes with a high clustering coefficient can be used reveal to cliques within the network. Note that the interpretation of the coefficient is more meaningful for nodes with high degree centrality.</li>

            <li><b>Degree Centrality</b>:
                The size of the neighborhood of a node, that is, the number of immediately connected nodes.</li>
HTML
        ,

        'chart.sna.node-column-label' => 'Node',
        'chart.sna.result.betweenness-centrality' => 'Betweenness Centrality',
        'chart.sna.result.clustering-coefficient' => 'Clustering Coefficient',
        'chart.sna.result.degree-centrality' => 'Degree Centrality',
        'chart.sna.sort-cb' => 'Betweenness Centrality (descending)',
        'chart.sna.sort-cc' => 'Clustering Coefficient (descending)',
        'chart.sna.sort-cd' => 'Degree Centrality (descending)',
        'chart.sna.sort-node' => 'Node Label (ascending)',

        'chart.timeline.settings' => <<<HTML
        <p>Plots date and time ranges as bars on a scrollable timeline. The query result columns must comply with the <a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/timeline#data-format">specified data format</a>.</p>
        <div class="form-group">
            <label class="control-label">Options</label>
            <div class='checkbox top-margin-zero'>
                <label>$1 Show row labels</label>
            </div>
            <div class='checkbox'>
                <label>$2 Single color for all bars: $3</label>
            </div>
            <div class='checkbox'>
                <label>$4 Show tooltips</label>
            </div>
        </div>
HTML
        ,
        'delete.success' => 'Record successfully deleted.',
        'delete.confirm-head' => 'Confirm Delete',
        'delete.confirm-msg' => 'Please confirm that you want to delete this record. This action cannot be undone. Note the deletion will only work if the record is not referenced by some other record.',
        'delete.button-cancel' => 'Cancel',
        'delete.button-delete' => 'Delete',

        'error.db-connect' => 'Could not establish connection to the database.',
        'error.db-prepare' => 'Error preparing database query.',
        'error.db-execute' => 'Error executing database query.',
        'error.delete-exec' => 'Delete operation failed. Most likely this is because the item you intend to delete is being referenced by some other object.',
        'error.delete-count' => 'Record was not deleted, probably because it was already deleted. Try reloading this page.',
        'error.delete-file-warning' => 'However, one or more files could not be deleted from the storage folder.',
        'error.edit-obj-not-found' => 'Requested object was not found.',
        'error.exception' => 'Exception: $1',
        'error.field-value-missing' => 'No value for field $1 provided.',
        'error.field-required' => 'Please fill in required field <b>$1</b>',
        'error.field-multi-required' => 'Please provide at least one value for required field <b>$1</b>',
        'error.password-too-short' => 'Password is too short. Minimum length is $1.',
        'error.password-hash-missing' => 'Password hash function $1 does not exist. Inform your admin.',
        'error.upload-err-ini-size' => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
        'error.upload-err-form-size' => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        'error.upload-err-partial' => "The uploaded file was only partially uploaded",
        'error.upload-err-no-file' => "No file was uploaded",
        'error.upload-err-no-tmp-dir' => "Missing a temporary folder",
        'error.upload-err-cant-write' => "Failed to write file to disk",
        'error.upload-err-extension' => "File upload stopped by extension",
        'error.upload-err-unknown' => "Unknown upload error",
        'error.upload-filesize' => 'Uploaded file exceeds size limitation of $1 bytes.',
        'error.upload-invalid-ext' => "File extension '$1' is not allowed. The following extensions are allowed: $2",
        'error.upload-location' => 'Target location for uploaded files not set. Contact your admin.',
        'error.upload-create-dir' => 'Could not create target directory.',
        'error.upload-file-exists' => 'Cannot upload file, because a file with the same name already exists at the storage location.',
        'error.upload-move-file' => 'Could not store uploaded file.',
        'error.upload-store-db' => 'Storing files in the DB is not supported yet. Contact your admin.',
        'error.upload-no-file-provided' => 'No file provided for mandatory upload <b>$1</b>.',
        'error.invalid-dbtype' => "Invalid database type '$1' specified in configuration.",
        'error.invalid-display-expression' => 'The configured display expression is invalid.',
        'error.invalid-function' => "Invalid function '$1'.",
        'error.invalid-login' => 'Invalid $1 and/or $2.',
        'error.invalid-mode' => "Invalid mode '$1'.",
        'error.invalid-params' => 'One or more supplied parameters are invalid or missing.',
        'error.invalid-pk-value' => "Invalid primary key value '$1'.",
        'error.invalid-lookup-table' => "Invalid lookup table '$1'.",
        'error.invalid-lookup-field' => "Invalid lookup field '$1'.",
        'error.invalid-table' => "Invalid table '$1'.",
        'error.invalid-wkt-input' => 'Invalid WKT input.',
        'error.missing-pk-value' => "Missing value for primary key '$1'.",
        'error.no-plugin-functions' => 'There are no registered plugin functions to call.',
        'error.no-values' => 'No values to store in database.',
        'error.not-allowed' => 'You are not allowed to perform this action.',
        'error.query-withouth-qualifier' => 'Query without table qualifier',
        'error.missing-login-data' => 'Please provide $1 and $2.',
        'error.map-picker-wkt' => '<b>Error:</b> The current value <code>$1</code> is invalid and cannot be displayed.',
        'error.map-picker-single-marker' => 'You need to make sure you have exactly one marker placed on the map.',
        'error.edit-inline-form-id-missing' => 'Parent form identifier not provided.',
        'error.sequence-name' => 'Configured value for <code>id_sequence_name</code> appears invalid.',
        'error.edit-update-rels-prep' => 'Preparing the updating of relationships failed for field $1 (step $2).',
        'error.edit-update-rels-exec' => 'Executing the updating of relationships failed for field $1 (step $2).',
        'error.sql-linkage-defaults' => 'SQL linkage statement with default values preparation failed.',
        'error.update-record-gone' => 'Something went wrong when retrieving the updated record. It may have been deleted in the meantime.',
        'error.storedquery-fetch' => 'Could not retrieve the stored query',
        'error.storedquery-config-table' => 'In $APP the querypage_stored_queries_table setting is missing in settings.php',
        'error.storedquery-create-table' => 'Could not create table',
        'error.storedquery-exec-params' => 'Failed to execute query with $1',
        'error.storedquery-invalid-sql' => 'Invalid SQL query. Only SELECT statements are allowed!',
        'error.storedquery-invalid-params' => 'Invalid table or field in parameterized query',
        'error.chart-duplicate-cols' => 'There is a problem with your query, most likely you have duplicate column names in your query result. Please use aliases in your query to disambiguate column names.',
        'error.lookup-async.invalid-params' => 'Error during search: Invalid search parameters; please try again.',
        'error.lookup-async.connect-db' => 'Error during search: Connecting to database failed.',
        'error.lookup-async.stmt-error' => 'Error during search: Database could not be queried.',
        'error.lookup-async.query-whitespace' => 'Error during search: The search term includes too many whitespace characters.',
        'error.merge-primary-key-setting-missing' => 'Undefined primary key setting for table <code>$1</code>! Merging was aborted. Contact your admin!',

        'geom-field.placeholder' => 'Enter WKT value or click "$1"',
        'geom-field.map-picker-button-label' => 'Map',
        'geom-field.map-picker-button-tooltip' => 'Assign the location from a popup map',
        'geom-field.map-picker-view-tooltip' => 'Click to show this location on a popup map',

        'global-search.cache-notice' => '<b>Note:</b> The search results for this search term were retrieved from the cache. Fresh results for this search term will be available after the cache expires in $1 minutes.',
        'global-search.input-placeholder' => 'Search',
        'global-search.results-for' => 'Search Results for',
        'global-search.term-too-short' => '<p>This search term is too short, it must contain at least $1 characters.</p>',
        'global-search.no-results' => 'No search results in any table.',
        'global-search.one-result' => 'One search result found.',
        'global-search.results-info' => 'Found search results in $1 $2. $3',
        'global-search.results-one' => 'one',
        'global-search.results-table-singular' => 'table',
        'global-search.results-table-plural' => 'tables',
        'global-search.results-jump' => 'Click to jump to table',
        'global-search.results-found-detail' => '$1 search results found.',
        'global-search.show-more-preview' => 'Display All Results',
        'global-search.show-more-detail' => 'To narrow down search results please adapt your search term.',
        'global-search.limited-results-hint' => 'Only the first $1 search results are shown here.',
        'global-search.goto-top' => 'Go to Top',

        'helper.html-text-clipped' => 'Text clipped due to length. Click to show clipped text.',
        'helper.help-popup-title' => 'Help',

        'info.new-edit-update-rels-prep-problems' => 'Failed to prepare update of details of relation to record $1 in field $2.',
        'info.new-edit-update-rels-exec-problems' => 'Failed to execute update of details of relation to record $1 in field $2.',
        'info.new-edit-update-rels-inline-defaults' => 'Record was stored, but could not set related record $1 for field $2.',
        'info.new-edit-update-rels-inline-prep' => 'Preparing the updating of association details in field $1 with record #$2 failed.',
        'info.new-edit-update-rels-inline-exec' => 'Executing of updating of association details in field $1 with record #$2 failed.',

        'info-box.success-head' => 'Success',
        'info-box.error-head' => 'Error',
        'info-box.sql-codes' => 'Error Codes',
        'info-box.info-head' => 'Information',

        'list.num-indicator' => 'Displaying records <b>$1</b>&ndash;<b>$2</b> of <b>$3</b>',
        'list.total-indicator' => 'total in table: <b>$1</b>',
        'list.button-new' => 'New $1',
        'list.jump-to-page' => 'Jump to page',

        'login.button' => 'Log In',
        'login.head' => 'Login',
        'login.logout-navbar-label' => 'Logout',
        'login.guest-access' => 'Guest Access',

        'lookup-field.record' => 'Record',
		'lookup-field.create-new-button' => 'Create New',
        'lookup-field.create-new-tooltip' => 'If you cannot find the record in the dropdown box, click this button to create a new record.',
        'lookup-field.placeholder' => 'Click to select',
        'lookup-field.linkage-details-edit-tooltip' => 'Edit The Details Of This Association',
        'lookup-field.linkage-assoc-edit-tooltip' => 'Edit The Associated $1',
        'lookup-field.linkage-assoc-delete-tooltip' => 'Remove The Association With This $1',
        'lookup-field.linked-records-clipped-tooltip' => 'Text clipped due to length. Click to show clipped text.',
        'lookup-field.linked-record-no-display-value' => 'There is no display value for this referenced record, so its identifier is displayed here',
        'lookup-field.max-async' => 'Limiting the dropdown box to the first $1 options. Please use a more restrictive search term.',
        'lookup-field.linkage-details-missing' => 'For all linked records in the following list, which are highlighted with a red <span class="glyphicon glyphicon-th-list"></span> icon, required linkage details must be provided by clicking the icon!',

        'main-page.html' => '<p>Choose an action from the top menu.</p>',

        'map-picker.done-button' => 'Done',
        'map-picker.edit-instructions' => "Place the shape at the desired location. To create a shape, click any of the shape icons (e.g. the <span class='glyphicon glyphicon-map-marker'></span> marker icon) and then draw the shape on the map. To edit an existing shape, click the <span class='glyphicon glyphicon-edit'></span> icon and follow the instructions. When you're done, click the <span class='glyphicon glyphicon-check'></span> Done button. Double click to move an overlay image to the front.",

        'menu.browse+edit' => 'Browse & Edit',
        'menu.new' => 'New',

        'merge.page-heading' => 'Merge $1',
        'merge.intro' => 'In the table below, the $1 displayed in the second row (i.e. the <i>Slave</i> record) will be merged into the $1 displayed in the first row (i.e. the <i>Master</i> record). Review and adjust the selection boxes in the table below to define for each column which value shall be in the merged $1. In case of two checked boxes for a column, the values of both $1 records will be merged (in case of multiple selection columns) or the slave value will be appended to the master value (in case of text values). To merge the records, click the "Merge" button.',
        'merge.button-merge' => 'Merge',
        'merge.button-merge-again' => 'Merge Again',
        'merge.info-rollback' => 'The merge operation was rolled back because errors occurred.',
        'merge.success' => 'The merge operation was completed successfully. Please review the merged record in the first table row. If necessary, you can merge these records again or you may also delete the Slave record in the second table row, if it is not required any more.',
        'merge.fail' => 'The merge operation could not be completed. Constraints defined within the database could be responsible for this.',
        'merge.button-cancel' => 'Cancel',
        'merge.nothing-to-do' => 'Using the selected values in the table, the master record would remain unchanged. Therefore the merge operation is obsolete.',
        'merge.record-pushed' => 'This $1 was selected for merging with another $1. Please select the other $1 to be merged with this one by navigating to the other $1 and clicking the "Merge" button there. You will then be able to define exactly which parts of the two records shall be merged.',
        'merge.list-of-referencing-records' => 'The slave record is referenced from the following records in other tables. If you check this box, the references from these records will be changed to reference the master record in the above table.',
        'merge.delete-slave-if-master-referenced' => 'If references from one of the above records to the master record already exist, the references to the slave record can either be deleted or kept. Check this box if you want the references to the slave record deleted in such cases. (This option is only relevant if the above box was checked)',
        'merge.button-swap' => 'Swap Slave & Master',

        'new-edit.heading-new' => 'New $1',
        'new-edit.heading-edit' => 'Edit $1',
        'new-edit.heading-edit-inline' => 'Edit Details of $1',
        'new-edit.save-button' => 'Save',
        'new-edit.clear-button' => 'Clear Form',
        'new-edit.intro-help' => "Fill the form fields and then press <span class='glyphicon glyphicon-floppy-disk'></span> <b>Save</b>. Fields indicated with <span class='required-indicator'>&#9733;</span> are required.",
        'new-edit.save-inline-hint' => 'Note that your edits will only be stored in the database if the original form is also submitted',
        'new-edit.field-optional-tooltip' => 'This field is optional',
        'new-edit.field-required-tooltip' => 'This field is required',
        'new-edit.success-new' => 'Record stored in the database.',
        'new-edit.success-edit' => 'Record updated in the database.',
        'new-edit.validation-error' => 'The form includes errors! Please correct the values entered in the fields highlighted in red.',
        'new-edit.form-submitting' => 'Form is being submitted. Please wait ...',
        'new-edit.form-loading' => 'Form Loading ...',

        'querypage.sql-label' => 'SQL Query',
        'querypage.exec-button' => 'Execute',
        'querypage.sql-help-head' => 'SQL Query Help',
        'querypage.sql-help-text' => <<<HTML
            <p>
                Enter here the SQL query to execute. Only <code>SELECT</code> queries are possible.
            </p>
            <p>
                <b>Parameterized Queries</b>: It is possible to use named placeholders with default values instead of literal values in the where clause. A parameter looks like <code>#{my_param|default_val}</code>, where <code>my_param</code> is the name of the paramenter, and <code>default_val</code> is the default value. The default value can be empty. The separator <code>|</code> is mandatory even for an empty default value. By default all parameters are optional. To define a required parameter, which prevents the query from being executed when it's empty, you put an exclamation mark <code>!</code> at the beginning signature of the parameter, e.g. <code>#!{...}</code>
            </p>
            <p>
                <b>Example</b>: <code>select * from users where lastname = #{Name|Norris}</code>
            </p>
            <p>
                Every parameter can optionally be given a label. The label is then displayed in the query display and is defined as follows: <code>#{my_param:label|default_val}</code>, for instance <code>#{a:Minimum Age of Person|18}</code>. Note that if you use a parameter multiple times in the query, only the last occurrence of the parameter fully defines the parameter.
            </p>
            <p>
                For experts: additionally one can define to display a dropdown box in the query view, by referencing a table field setting in the project settings. This exemplary works as follows: <code>#{Name||table:person,field:fullname}</code>. This will display a dropdown box as configured in the table <code>person</code> for the field <code>fullname</code>.
            </p>
            <p>
                <b>Dropdown Parameters</b>: It is possible to use one of the lookup fields defined for the database to generate a dropdown box, so the user can choose the parameter value by picking. The signature of a dropdown parameter is: <code>#{p||table:persons,field:relationship|flags:multi|expr:pers_rel|op:in}</code>. This example defines a parameter <code>p</code> without default value. It is fed by the lookup field relationship in table persons. <code>flags:multi</code> enables multiple selection. <code>expr:pers_rel</code> defines the SQL expression (typically the field) to test the picked values against, and <code>op:in</code> defines that the SQL operator used is <code>in</code>. Other set comparison operators can be used like <code>not in</code>, <code>>= any</code>, etc.
            </p>
HTML
        ,
        'querypage.store-settings-cache-expires' => 'Enable caching of data. Cache expires after (seconds)',
        'querypage.store-settings-allow-public' => 'This visualization is publicly accessible (use with caution).',
        'querypage.store-button-save' => 'Save',
        'querypage.store-button-new' => 'Save as New',
        'querypage.store-button-update' => 'Update Existing',
        'querypage.store-description-placeholder' => 'Description',
        'querypage.store-title-placeholder' => 'Title',
        'querypage.store-intro' => 'Please provide a title and a description for the stored query (optional):',
        'querypage.store-success' => 'Query stored successfully. The live visualization of this query is now available at:',
        'querypage.store-error' => 'An error occurred while storing the query.',
        'querypage.store-button-label' => 'Save Query & Get Live Visualization URL',
        'querypage.settings-head' => 'Result Visualization Settings',
        'querypage.settings-viz-label' => 'Visualization Type',
        'querypage.param-query-refresh' => 'Refresh Results',
        'querypage.results-head' => 'Result Visualization',
        'querypage.param-required' => 'This query field must be provided before the query can be executed',
        'querypage.param-hint' => 'Query parameters marked with ★ are mandatory to fill in.',

        'record-renderer.view-icon' => 'View This $1',
        'record-renderer.edit-icon' => 'Edit This $1',
        'record-renderer.delete-icon' => 'Delete This $1',
        'record-renderer.sort-asc' => 'Sort Ascending',
        'record-renderer.sort-desc' => 'Sort Descending',
        'record-renderer.search-icon' => 'Search',

        'search.transformation-invalid' => 'Configuration error: $APP[search_string_transformation] does not include a placeholder for the value, i.e. %s',
        'search.popover-option-any' => 'Field contains',
        'search.popover-option-word' => 'Field contains word',
        'search.popover-option-exact' => 'Field is exactly',
        'search.popover-option-start' => 'Field starts with',
        'search.popover-option-end' => 'Field ends with',
        'search.popover-placeholder' => 'Enter search text',
        'search.infotext-any' => "Searching all records where <b>$1</b> contains <span class='bg-success'><strong>$2</strong></span>",
        'search.infotext-word' => "Searching all records where <b>$1</b> contains the word <span class='bg-success'><strong>$2</strong></span>",
        'search.infotext-exact' => "Searching all records where <b>$1</b> is exactly <span class='bg-success'><strong>$2</strong></span>",
        'search.infotext-start' => "Searching all records where <b>$1</b> starts with <span class='bg-success'><strong>$2</strong></span>",
        'search.infotext-end' => "Searching all records where <b>$1</b> ends with <span class='bg-success'><strong>$2</strong></span>",
        'search.button-clear' => 'Clear Search',
        'search.no-results' => 'No records found.',
        'search.num-indicator' => 'Displaying search results <b>$1</b>&ndash;<b>$2</b> of <b>$3</b>',

        'setup.wizard.save-success' => 'Settings were successfully saved to the settings file.',
        'setup.wizard.save-error-file' => 'Settings could not be saved to settings file. Make sure the file is creatable and writable by the web server process.',
        'setup.heading' => 'Settings',

        'text-field.remaining-chars' => 'character(s) remaining.',

        'upload-field.browse' => 'Browse',
        'upload-field.hint-empty' => 'Note: If you don\'t want to replace the existing file on the server, you may leave this field empty',

        'view.invalid-record' => 'This record cannot be viewed. It might have been deleted.',
        'view.add-related-data-button' => 'Add Related Data',
        'view.edit-icon' => 'Edit This $1',
        'view.edit-button' => 'Edit',
        'view.delete-icon' => 'Delete This $1',
        'view.delete-button' => 'Delete',
        'view.list-icon' => 'List All $1',
        'view.list-button' => 'List All',
        'view.new-icon' => 'Create New $1',
        'view.new-button' => 'Create New',
        'view.related-icon' => 'List Related Records (Click For Dropdown Menu)',
        'view.related-button' => 'List Related',
        'view.related-menu-item' => '$1 (via $2)',
        'view.hidden-fields-hint-1' => 'This $1 has one emtpy field.',
        'view.hidden-fields-hint-N' => 'This $1 has $2 emtpy fields.',
        'view.show-hidden-field-1' => 'Show This Field',
        'view.show-hidden-field-N' => 'Show These Fields',
        'view.merge-icon' => 'Merge This $1 With Another $1',
        'view.merge-button' => 'Merge',
    );
?>
