<?
	/* ========================================================================================================
		$APP defines application specific settings:

		- title: string
			App title as displayed in header menu. If page_title is not set, this is also used as HTML <title> for the page
		- page_title: string (optional)
			What to put in the HTML title element. If not provided, the 'title' setting is used.
		- lang: string (optional) (default: 'en')
			Localization (where applicable)
		- bootstrap_css: string (optional) (default: https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css)
			Specify a bootstrap CSS theme, if you do not want to use the default theme.
		- page_icon: string (optional)
			Specify a path to an web page icon (aka 'favicon').
		- plugins: array (optional)
			Custom PHP files that will be included using require_once(). These plugins can contain functions that can be referenced anywhere in this file where external procedures can be provided (e.g. $LOGIN/initializer_proc). The index of the array can be any key that can be used to access the actual file name of the plugin from within the code.
		- cache_dir: string (optional, but recommended)
			Path to the directory where this app and its plugin can store their cache files. The path must not end with a trailing slash. The process that runs the php scripts on your machine must have read & write access to this directory. If this setting is undefined, there will be no caching.		
		- page_size: int
			Pagination setting for MODE_LIST: max. number of items per page
		- pages_prevnext: int
			Pagination setting for MODE_LIST: max. number of pages to display left and right to the current one
		- max_text_len: int
			Max number of characters to display before text gets clipped in MODE_LIST
		- mainmenu_tables_autosort: bool
			Whether to sort the main menu entries alphabetically
		- view_display_null_fields: bool
			In MODE_VIEW, whether fields that have NULL value should be displayed (true) or omitted (false)
		- search_lookup_resolve: bool
			whether to lookup foreign key values instead of the FKs themselves
		- search_string_transformation: string (optional) (default: '%s')
			SQL expression used to define what transformation should happen both with the search query and the field before they are compared with each other. The placeholder for the argument is %s. This setting will typically be something like 'lower(%s)', or if languages with non latin characters are used 'unaccent(lower(%s))'.
		- null_label: string
			label for the checkbox that allows explicit setting of a NULL value for a non-required field
		- render_main_page_proc: string (optional)
			called to render the main page after login.
			No parameters.
		- menu_complete_proc: string (optional)
			procedure called when default main menu has been built.
			function parameters: &$menu (assoc. array with complete menu for modification prior to rendering)
		- list_mincolwidth_max: int (optional) (default: 300)
			In MODE_LIST, column width is determined by cell content and set using CSS min-width. Specify here the maximum min-width that should be assigned
		- list_mincolwidth_pxperchar: int (optional) (default: 6)
			When determining min-width columns in MODE_LIST, how many pixels should be calculated for each character
		- custom_related_list_proc: string (optional)
			Name of a plugin function to call for extending the "List Related" dropdown in MODE_VIEW. This list of related records uses the search function to produce a list of records in a foreign table referencing the current one-
			Function parameters: $table_name (name of the current table), const &$table (table info - not to be modified), const &$pk_vals (hash with primary key values of currently viewed record), &$rel_list (current array of related list links).
			$rel_list can be extended with hash arrays containing values for the following keys: table_name (name of the related table), field_name (name of the field to search in the related table), display_label (item label to display in the dropdown menu)
		- preprocess_html_func: string (optional)
			Name of a function in a plugin to preprocesses any HTML output before it is written to the output buffer.
			Argument: $html (string with current text), Return: preprocessed string
		- additional_callable_plugin_functions: array (optional)
			List of function names that are allowed to be called via MODE_PLUGIN.
		- querypage_stored_queries_table: string (optional)
			If this is set, users are allowed to store and share queries via the database in MODE_QUERY. This setting controls the name of the table that will be created for this purpose in the database. If this is not set, users will not be able to store/share queries.
		- querypage_permissions_func: string (optional)
			Name of a function that returns a boolean value indicating whether the current user is allowed to interact with the page
		- register_custom_chart_type_func : string (optional)
			Name of a function that is called when the app asks for custom query visualizations. Typically this function will be in a plugin. In this function the app should call the QueryPage::register_custom_chart_type() with three parameters:
			* string $handle: used for 2 purposes: filename must be chart.$handle.php and class name must be dbWebGenChart_$handle
			* string $label: to be displayed in the dropdown box in the query editor
			* string $directory (optional, default = ''): location of the file relative to the app directory
		- lookup_allow_edit_default: boolean (optional) (default: false)
			Determines the default value for the setting field/lookup/allow_edit for CARDINALITY_MULTIPLE lookup fields.
		- preprocess_func: string (optional)
			Name of a function that will be called brefore any processing is performed by dbWebGen. Check engine.php about available includes
	======================================================================================================== */
	$APP = array(
		'plugins' => array(),
		'title' => '',
		'view_display_null_fields' => false,
		'page_size'	=> 10,
		'max_text_len' => 250,
		'pages_prevnext' => 2,
		'mainmenu_tables_autosort' => true,
		'search_lookup_resolve' => true,
		'search_string_transformation' => 'lower((%s)::text)',
		'null_label' => "<span class='nowrap' title='If you check this box, no value will be stored for this field. This may reflect missing, unknown, unspecified or inapplicable information. Note that no value (missing information) is different to providing an empty value: an empty value is a value.'>No Value</span>"
	);

	/* ========================================================================================================
		$DB has database connection details

		- type: {DB_POSTGRESQL}
			Database type, currently only postgres supported
		- host: string
			Database server (IP oder hostname)
		- port: int
			Post number
		- user: string
			User name
		- pass: string
			Password
		- db: string
			Database to connect to. Note: currently only works with tables in the default schema.
			TODO: extend to allow for support of multiple schemas
	======================================================================================================== */
	$DB = array(
		'type' => DB_POSTGRESQL,
		'host' => 'localhost',
		'port' => 5432,
		'user' => 'XXX',
		'pass' => 'XXX',
		'db'   => 'XXX'
	);

	/* ========================================================================================================
		$LOGIN defines how authentication is done.

		If this array is empty, everyone can do everything.

		Login works with user records found in a database table or with any provided associative array.
		In any case, fields are required for primary_key, username_field, password_field, name_field
		and 'form' settings.

		- users_table: string | array
			If a string is provided, it must reflect the name of the table in the DB that contains the user records.
			If an array is provided, it must contain a list of associative arrays representing the user records.
		- primary_key: string
			Name of field in users_table that has the primary key, which identifies the record
		- username_field: string
			Name of the username field in users_table. Usernames must be unique.
		- password_field: string
			Name of the password field in users_table
		- name_field: string
			Name of the field containing the user's name in users_table
		- password_hash_func: string (optional)
			Name of the function to be used for password hashing, e.g. 'md5', 'sha1', etc.
			Works only with functions that take a single mandatory argument (the password)
		- password_verify_func: string (optional)
			Name of the function that compares the hash value with the plain text password.
			If not set, this will simply compare for equality of hash values.
			If a function is provided here, it needs to return true (password correct) or false (password incorrect). Signature:
				bool password_verify_func($plain, $hash);
		- form: array
			Field labels to display to the user in the login form
			- username: string
			- password: string
		- login_success_proc: string (optional)
			Called after successful login.
			No arguments.
		- initializer_proc: string (optional)
			Name of a function called at the very beginnging of processing. The function is called only when there is a logged in user.
			No arguments. NOTE: in some cases this function is NOT allowed to write anything to the output buffer!
		- allow_change_password: bool (optional) (default: true)
			Whether or not to allow each user to change their password. This is only applicable if the users are stored in the database, otherwise this setting is ignored.
	======================================================================================================== */
	$LOGIN = array(
		'users_table' => 'users',
		'primary_key' => 'id',
		'username_field' => 'login',
		'password_field' => 'password',
		'name_field' => 'name',
		'password_hash_func' => 'md5',
		'allow_change_password' => true,
		'form' => array('username' => 'Username', 'password' => 'Password'),
	);

	/* ========================================================================================================
		$TABLES defines settings for all tables in the DB that play a role in the app

		Field names and table names MUST NOT be escaped in this settings file except in the 'sort' field setting.
		Each key reflects a table in the DB. The value is an array containing the following settings:

		- actions: array
			List of MODE_* actions that are allowed for the table (see config/constants.php)
		- hide_from_menu: array (optional)
			If set, the array shall include the MODE_* that identify the menu from which this table shall be hidden even if the action is allowed. Currently only menus for MODE_NEW and MODE_LIST are generated automatically, so these may be in the array.
		- display_name: string
			Text label for the table
		- description: string
			Text to display below the table heading in MODE_*
		- item_name: string
			Label for items stored in the table
		- primary_key: array
			- auto: bool
				Whether or not the key for this table is set automatically or entered manually.
				If auto = true, the primary key should consist of only one column (not sure about consequences otherwise!).
				If multiple columns, auto should be set to false (not sure about consequences otherwise!)
			- columns: array
				List of primary key fields. also if there is only one PK field, this needs to be an array
			- sequence_name: string (required only if auto=true)
				if auto = true, this becomes required and must reflect the sequence name that generates the new primary key value.
				If auto=false, this setting is ignored.
		- fields: array
			Associative array with settings for each field. The key reflects the column name in the DB. The value is an array with several settings:
			- label: string
				Display label for this field, e.g. used as table head in MODE_LIST or form field label in MODE_NEW, etc.
			- type: any T_* defined in config/constants.php
				Type of the field.
				If type=T_LOOKUP: this is a pseudo type that reflects foreign keys from either 1:n (CARDINALITY_SINGLE) or m:n relationships (CARDINALITY_MULTIPLE). Settings for 'lookup' must be provided if this type is assigned (see below).
				If type=T_ENUM, 'values' must be set (see below)
			- len: int
				Maximal number of characters that the input control can hold. Relevant only for T_TEXT_LINE, T_PASSWORD.
			- required: bool (default: false)
				Whether or not a value is required for this field (= NOT NULL)
			- editable: bool (default: true)
				Whether or not this field should be offered in the new/edit forms. For an automatically generated primary key field or some computed field, this should be set to false. Even with editable=false, the field will be displayed in MODE_LIST and MODE_VIEW. If it is desired to completely hide this field from the user, the field itself should not be set in the fields array.
			- width_columns: int (optional) (default: calculated automatically for T_TEXT_LINE, otherwise 7)
				Specifies the number of columns in the bootstrap grid occupied by the input control.
			- height_rows: int (optional) (default: 5)
				Specifies the number of rows to be offered by the input control. Only applicable with T_TEXT_AREA.
			- resizeable: boolean (optional) (default: true)
				Controls whether a control is vertically resizeable. Only applies to T_TEXT_AREA.
			- help: string (optional)
				Help text to display in the new/edit forms. Can contain HTML.
			- show_setnull: bool (default: false)
				Whether or not to show a checkbox allowing to explicitly set a NULL value for a non-required field (applies to input and textarea). Even if the checkbox is not shown, it is there and automatically checked/unchecked upon user input.
			- default: string (optional)
				Default value to set. All occurrences of REPLACE_DYNAMIC_* strings (see config/constants.php) are replaced with the current values.
			- values: array (required only if type=T_ENUM)
				Array of values for a T_ENUM type. An associative array with key reflecting the actual DB value, and value representing the label to display to the user, e.g. array(1 => 'January', 2 => 'February', ...)
			- allow_create: boolean (default: true)
				If set to false, suppresses the "Create New" button that is displayed next to T_LOOKUP boxes. Note: this setting might conflict with lookup/hide_dropdown setting
			- lookup: array	(required only if type=T_LOOKUP)
				Details of the foreign key relationship with another table
				- cardinality: {CARDINALITY_SINGLE, CARDINALITY_MULTIPLE}
					Whether this field is a foreign key in this very table (CARDINALTY_SINGLE), reflecting an 1:n relationship, or whether this relationship is actually represented in a separate table reflecting an m:n relationship (CARDINALITY_MULTIPLE). In the latter case, 'linkage' settings must be provided (see below)
				- table: string
					Name of the table referenced by this foreign key field
				- field: string
					Field in the table referenced by this foreign key field (typically the primary key of the other table)
				- display: string
					Since the foreign key is typically a numeric key, this setting can be used to define what to display to the user. Can be either a string literal representing a field name in the referenced table, e.g.: 'display' => 'lastname'
					Or it can be a hash array with 'columns' => array of columns, which is used in 'expression' => expression referring to indexes in 'columns' as %1, %2, etc., e.g.: 'display' => array('columns' => array('firstname', 'lastname'), 'expression' => "concat_ws(' ', %1 %2)" )
				- sort: boolean (optional) (default: true)
					In case of multiple lookup values, this setting determines whether the linked records are sorted using the display expression. Only relevant for CARDINALITY_MULTIPLE.
				- default: any type (optional)
					Default option for this foreign key reference to select in MODE_NEW. The type should be automatically convertible to string
				- related_label: string (optional)
					Text to display in the "List Related" dropdown in MODE_VIEW of any record of the table referenced by this field (e.g. "Cars Sold By This Agent")
					If missing, the label will be constructed automatically from the table's display name and the field's label. (e.g. "Cars Sales (As Agent)")
				- async: array (optional)
					Hash array that, when provided, specifies how to dynamically fetch records into the dropdown box based on keyboard input. This is handy if the linked table holds too many records. Keys:
					- min_input_len: integer
						Minimum number of characters the user has to type before matching records are fetched
					- delay: integer (optional) (default: 0)
						If set, this specifies the number of milliseconds to wait before sending an Ajax request after a user keystroke
				- label_display_expr_only: boolean (optional) (default: false)
					By default the label for lookup values is composed of the display expression and in parentheses the respective primary key name and value. If you wish to suppress the parentheses, set label_display_expr_only = true
				- hide_dropdown: boolean (optional) (default: false)
					In some cases it might be useful to allow only the creation of new associations using "Create New" button, and not through the dropdown box. If hide_dropdown is set to true, the dropdown box will not be shown. If the field is CARDINALITY_SINGLE and disabled for some reason, the dropdown will be shown regardless of the hide_dropdown setting. Note: this setting might conflict with the allow_create setting of the field. If hide_dropdown is set to true, allow_create must be set to true as well, otherwise nothing will be shown.
				- create_new_label: string (optional) (default: "Create New")
					Label for the button next to the dropdown box, which allows users to add new records to the linked table
				- allow_edit: boolean (optional) (default: see $APP/allow_lookup_edit_default)
					Whether to allow the user to edit the associated record from the lookup table. Note that setting this to true will only have an effect if the lookup table is configured in $TABLES, and MODE_EDIT is an allowed action there.
			- linkage: array
				If cardinality=CARDINALITY_MULTIPLE, we need to define here the m:n relationship table that links records from this table (via fk_self) with records of the other table (via fk_other)
				- table: string
					Table name
				- fk_self: string
					Foreign key field referencing this table
				- fk_other: string
					Foreign key field referencing the other table
				- defaults: array (optional)
					Array of field defaults for non-key fields in the form FIELD_NAME => VALUE. For each field, occurrences of REPLACE_DYNAMIC_* strings (see config/constants.php) in VALUE are replaced with the current values.
				- render_func: string (optional)
					By default, multiple records are simple separated by MULTIPLE_RECORDS_SEPARATOR. By providing a custom function name here, you can change the default behavior for rendering the linkage to be applied in MODE_VIEW (in MODE_LIST this does not work because of potential clipping via $APP/max_text_len setting). The function signature:
						function render_func(&$field, $item_html, $index, $total_num_items, &$params)
						* &$field is the field settings array
						* $item_html is the HTML code for the linked item
						* $index is the item's index in the list
						* $total_num_items is the total number of items in the list
						* &$params is reserved for future use; currently the engine will pass a reference to an empty array
						The function must return the HTML to be rendered for the current item.
			- SRID: int (required only for type=T_POSTGIS_GEOM)
				Spatial Reference ID of the Postgis geometry
			- min_len: int (optional)
				This setting is only ueful for type=T_PASSWORD. If it is set, users will have to provide a password of this minimum length
			- max_size: int (optional)
				Only evaluated for type=T_UPLOAD, to limit the file size for uploads
			- location: string (required for type=T_UPLOAD && store=STORE_FOLDER)
				Only evaluated for type=T_UPLOAD, to specify the server side subfolder where to store uploaded files.
			- store: bitwise combination of {STORE_FOLDER, STORE_DB} (required for type=T_UPLOAD)
				Only evaluated for type=T_UPLOAD, to specify where the uploaded file shall be stored.
				If store & STORE_DB, then the file will be stored binary in this field (TODO: not implemented yet)
				If store & STORE_FOLDER, then the file will be stored in a folder specified by 'location'
			- allowed_ext: array (optional)
				If type=T_UPLOAD, the file extension of the uploaded file will be checked against this array
			- reset: bool (default: false)
				reset=true means that when editing a record (MODE_EDIT) its current value is not fetched into the form control.
			- min: number (optional)
				for T_NUMBER, define the minimum value for the field
			- max: number (optional)
				for T_NUMBER, define the maximum value for the field
			- step: number or string (optional) (default: 1)
				for T_NUMBER, define the step size for up/down (e.g. 3 or 0.01). If not restricted, use 'any'
			- conditional_form_label: array (optional)
				In MODE_NEW & MODE_EDIT forms, this controls field labels based on the value of another field (currently the other field must be T_ENUM or T_LOOKUP).
				- controlled_by: string
					The field within the same table that controls the label
				- mapping: array
					Hash array with key := value of the other field, and value := corresponding label of this field.
					If the othe field has a value that is not represented in this mapping hash, the label will be the default field label
			- list_hide: boolean (optional) (default: false)
				Determines whether this field is hidden from the table in MODE_LIST
			- sort_expr: string (optional) (default: '%s')
				If a table is sorted in MODE_LIST, this expression defines how the field to sort is handled in the ORDER BY clause using sprintf(). The default '%s' expression results in a "normal" sorting using ORDER BY with simply the fieldname. Use this setting e.g. for custom sorting using functions. Example: 'naturalsort(%s)'
			- max_decimals: int|array (optional)
				Allows specifying the maximum number of digits to display for T_NUMBER fields (ignored for other field types)
				If an integer is provided, this will be the maximum number of digits (e.g. 'max_decimals' => 5 will limit to 5 decimal digits)
				If an array is provided, the array keys map the MODE_* to the maximum number of digits to show in this view (e.g. 'max_decimals' => array(MODE_VIEW => 5) will limit the display to 5 decimal digits only in MODE_VIEW)
				If this setting is not provided, any T_NUMBER value will be rendered after it has been cast to float
			- tab: string (optional)
				If the form_tabs setting was provided for this table, refer the identifier of the tab to which this field belongs. In a tabbed form, if the field's tab identifier is missing, it is assumed that the field belongs to the first tab.
			- map_picker: array (optional)
				For T_POSTGIS_GEOM fields, if this array is set, the user will be able to pick a point from a map that appears in a popup window.
				Provide the following settings:
					- map_options: array (optional)
						An optional hash array that will be passed on as a Map_options object to the Leaflet L.Map constructor (see http://leafletjs.com/reference#map-options)
					- draw_options: array (optional)
						An optional hash array that will be passed on as a DrawOptions object to the Leaflet.Draw L.Control.Draw constructor (see https://leaflet.github.io/Leaflet.draw/docs/leaflet-draw-latest.html#drawoptions).
					- script: string|array (optional)
						Any javascript file (string) or files (array of strings) to include. Two javascript functions will be called form the map picker, if these functions exist:
						* map_picker_init_map() immediately after leaflet has occupied the map div
						* map_picker_finish_init() immediately after the map picker initialization is completed
						In the script, the following global variables will be available:
						* map (L.Map) - the Leaflet map
						* curPointLayer (L.Layer) - the Leaflet layer that contains the pre-existing point
						* drawnItems (L.FeatureGroup) - the feature group object that Leaflet.Draw uses to capture the user-drawn layers
		- sort: array (optional)
			Used for default sorting of tables in MODE_LIST. Associative array with key := fieldname (or SQL expression) and value := {'asc', 'desc'}
			e.g. [ 'lastname' => 'asc, 'firstname' => 'asc' ]
		- hooks: array (optional)
			Functions to call before/after certain events. Associative array with key reflecting the event, and value reflecting the function to call.
			Possible keys:
			- after_insert: string (optional), after_update: string (optional)
				Function to call after insertion/update of a record.
				Arguments: (1) table name (2) table settings and (3) primary key/value array.
			- before_insert: string (optional), before_update: string (optional)
				Function to call before insertion/update of a record.
				Arguments: (1) table name (2) table settings (3) column names array reference, (4) column values array reference
		- additional_steps: array (optional)
			Steps offered to be performed after a new record was created to link this record with others via separate linkage tables. The key reflects the linked table, the value is an array with these values:
				- label: string
					Link label for this action
				- foreign_key: document
					Foreign key field that links to this table from the n:m table
			e.g. in table customers:
			'additional_steps' => array('orders' => array('label' => 'Orders of this customer', 'foreign_key' => 'customer_id'))
		- custom_actions: array (optional)
			Array of arrays, each representing a custom action that is offered in particular viewing modes through particular buttons. Each custom action is an associative array consisting at least of the following keys:
				- mode: string
					Any of the MODE_* modes to which this action applies (currently only MODE_LIST implemented)
				- handler: string
					Name of a handler function that will be called for the current record. Arguments: (1) name of the current table (2) table info from this settings file (3) the record retrieved from the database using PDO::FETCH_ASSOC and (4) this very custom action hash, which means that any additional key/value pairs you add to this array will get passed to the handler function.
		- list_in_related: bool (optional) (default: true)
			In MODE_VIEW, there is a dropdown linking to tables where the current record is linked through a foreign key (T_LOOKUP). If you do not want this table to appear in this list at all, set this to true.
		- render_links: array (optional)
			If MODE_LINK and MODE_LIST are allowed in this table, then there will be an extra icon in the table in MODE_LIST for each record that allows to view the actual object represented by the record. An associative array has to be provided for each entry in this array:
				- icon: string
					The name of a glyphicon that works with bootstrap, e.g. "eye-open" or "trash". See a list here: http://www.w3schools.com/bootstrap/bootstrap_ref_comp_glyphs.asp
				- href_format: string
					A URL template that will be used in a sprintf() call, with the 'field' key of this array (see next). Hence this string should contain one %s
				- field: string
					Name of the field whose value will be used to replace %s in href_format
				- title: string
					Title (tooltip) shown when hovering over the link icon in MODE_LIST
			Example: 'render_links' => array(
					array('icon' => 'eye-open', 'href_format' => 'uploads_images/%s', 'field' => 'filename', title => 'Show the damn file')
				)
		- form_tabs: array (optional)
			If a tabbed MODE_NEW / MODE_EDIT / MODE_VIEW form is desired, fill this array with the following settings:
				- tabs: array
					An ordered set of arrays, where each array reflects a tab. Settings:
					- id: string
						Identifier of the tab, to be used in the 'tab' setting of the table's fields.
					- label: string
						Label to display on the tab
				- restrict_modes: array (optional)
					If tabs are only desired in subset of {MODE_NEW, MODE_EDIT, MODE_VIEW}, specify this subset as an array here.


	======================================================================================================== */
	$TABLES = array(
	);
?>
