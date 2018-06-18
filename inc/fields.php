<?php
	/*
		If a new field type is added:
		- Include here the file where the field class is defined
		- In constants.php, define a T_xxx constant for the field type
		- In field.base.php, FieldFactory::create, add the new class factory
		- In helper.php, include the type in prepare_field_display_val()
		- In new_edit.php check the process_form() and build_post_array_for_edit_mode()
	*/
	require_once 'field.base.php';
	require_once 'field.enum.php';
	require_once 'field.lookup.php';
	require_once 'field.number.php';
	require_once 'field.password.php';
	require_once 'field.textarea.php';
	require_once 'field.textline.php';
	require_once 'field.upload.php';
	require_once 'field.postgisgeom.php';
	require_once 'field.boolean.php';
?>
