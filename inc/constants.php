<?
	//
	// DO NOT EDIT THESE CONSTANTS UNLESS YOU KNOW WHAT YOU ARE DOING!!!
	//
	
	// supported databases
	define('DB_POSTGRESQL', 'postgresql');	
	
	// input types for different DB fields:
	define('T_TEXT_LINE', 'T_TextLine');	
	define('T_NUMBER', 'T_Number');	
	define('T_TEXT_AREA', 'T_TextArea');
	define('T_ENUM', 'T_Enum'); // can also emulate boolean	
	define('T_LOOKUP', 'T_ForeignKeyLookup'); // foreign key relationships
	define('T_PASSWORD', 'T_Password');
	define('T_UPLOAD', 'T_FileUpload');
	define('T_POSTGIS_GEOM', 'T_PostgisGeometry'); // postgis only!
	
	// m:n and 1:n relationships for T_LOOKUP types
	define('CARDINALITY_SINGLE', 'CARDINALITY_SINGLE');
	define('CARDINALITY_MULTIPLE', 'CARDINALITY_MULTIPLE');
	
	// null value indicator for non-required dropdowns
	define('NULL_OPTION', '__NULL__');
	
	// field name postfix for raw foreign key values (should create reasonably unique field name, i.e. no human assigned field should end with the same string)
	define('FK_FIELD_POSTFIX', '__3ffz3h_k031n');
	
	// where to store uploaded filesize. can be binary or'ed
	define('STORE_FOLDER', 0x1);
	define('STORE_DB', 0x2); // TODO: not implemented yet
	
	// search options
	define('SEARCH_ANY', 'any');
	define('SEARCH_START', 'start');
	define('SEARCH_END', 'end');
	define('SEARCH_EXACT', 'exact');		
	
	define('SEARCH_PARAM_FIELD', 'field');
	define('SEARCH_PARAM_QUERY', 'q');
	define('SEARCH_PARAM_OPTION', 'match');
	define('SEARCH_PARAM_LOOKUP', 'lookup');
	
	// dynamic runtime replacements for default field values
	define('REPLACE_DYNAMIC_SESSION_USER', '%SESSION_USER%');
	
	// prefix for url parameters that should be used as prefill values in MODE_NEW
	define('PREFILL_PREFIX', 'pre:');
	
	// viewing modes (reflected in URL parameter mode=XXX)
	define('MODE_NEW', 'new');
	define('MODE_EDIT', 'edit');
	define('MODE_LIST', 'list');
	define('MODE_VIEW', 'view');
	
	// pseudo modes
	define('MODE_DELETE', 'delete');
	define('MODE_CREATE_DONE', 'create_done');
	define('MODE_LOGOUT', 'logout');	
?>