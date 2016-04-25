<?	
	/* ========================================================================================================	
		$APP defines application specific settings:
		
		- title: string
			Displayed one main pageadd			
		- plugins: array (optional)
			Custom PHP files that will be included using require_once(). These plugins can contain functions that can be referenced anywhere in this file where external procedures can be provided (e.g. $LOGIN/initializer_proc) 			
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
		'search_string_transformation' => 'lower(%s)',
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
	
		If this array is empty, everyone can do everything
	
		Login works currently only with user records found in a database table (users_table). 
		Minimum required are fields for primary_key, username_field, password_field, name_field 
		and 'form' settings.
		
		- users_table: string
			Name of the table in the DB that contains user records
		- primary_key: string
			Name of field in users_table that has the primary key
		- username_field: string
			Name of the username field in users_table
		- password_field: string
			Name of the password field in users_table
		- name_field: string
			Name of the field containing the user's name in users_table
		- password_hash_func: string (optional)
			Name of the function to be used for password hashing, e.g. 'md5', 'sha1', etc.
			Works only with functions that take a single mandatory argument (the password)
		- form: array
			Field labels to display to the user in the login form
			- username: string
			- password: string
		- login_success_proc: string (optional)
			Called after successful login.
			No arguments.			
		- initializer_proc: string (optional)
			Name of a function called at the very beginnging of processing. The function is called only when there is a logged in user.
			No arguments.
	======================================================================================================== */
	$LOGIN = array(
		'users_table' => 'users',
		'primary_key' => 'id',
		'username_field' => 'login',
		'password_field' => 'password',
		'name_field' => 'name',
		'password_hash_func' => 'md5',
		'form' => array('username' => 'Username', 'password' => 'Password'),
	);
	
	/* ========================================================================================================
		$TABLES defines settings for all tables in the DB that play a role in the app	
	
		Field names and table names MUST NOT be escaped in this settings file except in the 'sort' field setting. 
		Each key reflects a table in the DB. The value is an array containing the following settings:
		
		- actions: array
			List of MODE_* actions that are allowed for the table (see config/constants.php)
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
				Length of the type. Relevant for T_TEXT_LINE.
			- required: bool (default: false)
				Whether or not a value is required for this field (= NOT NULL)
			- editable: bool (default: true)
				Whether or not this field should be offered in the new/edit forms. For an automatically generated primary key field or some computed field, this should be set to false. Even with editable=false, the field will be displayed in MODE_LIST and MODE_VIEW. If it is desired to completely hide this field from the user, the field itself should not be set in the fields array
			- help: string (optional)
				Help text to display in the new/edit forms. Can contain HTML.
			- default: string (optional)
				Default value to set. All occurrences of REPLACE_DYNAMIC_* strings (see config/constants.php) are replaced with the current values.			
			- values: array (required only if type=T_ENUM)
				Array of values for a T_ENUM type. An associative array with key reflecting the actual DB value, and value representing the label to display to the user, e.g. array(1 => 'January', 2 => 'February', ...)
			- lookup: array	(required only if type=T_LOOKUP)
				Details of the foreign key relationship with another table
				- cardinality: {CARDINALITY_SINGLE, CARDINALITY_MULTIPLE}
					Whether this field is a foreign key in this very table (CARDINALTY_SINGLE), reflecting an 1:n relationship, or whether this relationship is actually represented in a separate table reflecting an m:n relationship (CARDINALITY_MULTIPLE). In the latter case, 'linkage' settings must be provided (see below)
				- table: string
					Name of the table referenced by this foreign key field
				- field : string
					Field in the table referenced by this foreign key field (typically the primary key of the other table)
				- display : string
					Since the foreign key is typically a numeric key, this setting can be used to define what to display to the user. Can be either a string literal representing a field name in the referenced table, e.g.: 'display' => 'lastname' 
					Or it can be a hash array with 'columns' => array of columns, which is used in 'expression' => expression referring to indexes in 'columns' as %1, %2, etc., e.g.: 'display' => array('columns' => array('firstname', 'lastname'), 'expression' => "concat_ws(' ', %1 %2)" )		
			- linkage: array
				If cardinality=CARDINALITY_MULTIPLE, we need to define here the m:n relationship table that links records from this table (via fk_self) with records of the other table (via fk_other)
				- table: string
					Table name
				- fk_self: string
					Foreign key field referencing this table
				- fk_other: string 			
					Foreign key field referencing the other table	
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
			'additional_steps' => array('orders' => array('label' => 'Orders of this customer', 'foreign_key': 'customer_id'))
	======================================================================================================== */
	$TABLES = array(
	);
?>