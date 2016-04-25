$(window).load(function() {	
	//
	// logout button
	//
	$('#logout').on('click', function() {
		$.get('?mode=logout', function(data) {
			location.reload();
		});				
	});
	
	//
	// make all dropdowns a select2
	//
	$('select').each(function() {
		// display search box in dropdown if more than 5 options available
		if($(this).children('option').length > 5)	
			$(this).select2({ theme: 'bootstrap', width: '100%' });
		else
			$(this).select2({ theme: 'bootstrap', width: '100%', minimumResultsForSearch: Infinity });
	});	

	//
	// clipped text handler
	//
	$('a.clipped_text').on('click', function() {
		$(this).toggle().next('span.clipped_text').toggle();
	});
	
	//
	// Help popovers
	//
	$('[data-toggle=popover][data-purpose=help]').popover({
		html: true
	}); 
	
	//
	// Search popovers
	//
	$('[data-toggle=popover][data-purpose=search]').each(function() {
		// set the form content specific to the field
		$(this).data('content', search_popover_template.replace('%FIELDNAME%', $(this).data('field'))).popover({
			html: true,
			container: '#search-popover' // needed for CSS styling
		}); 
	});		
	
	//
	// Popover close if clicked outside
	//	
	$('body').on('click', function (e) {
		$('[data-toggle="popover"]').each(function () {			
			if (!$(this).is(e.target) && $(this).has(e.target).length == 0 && $('.popover').has(e.target).length == 0)
				$(this).popover('hide');			
		});
	});
	
	//
	// handle NULL checkbox updates for fields that are not required
	//
	$('input[type="text"]:not([required]), input[type="number"]:not([required]), textarea:not([required])').each(function() {
		var control = $(this);
		$('input[name="' + control.attr('name') + '_null"]').each(function() {
			var checkbox = $(this);
			control.on('input', function() {
				if(control.val() != '' && checkbox.prop('checked'))
					checkbox.prop('checked', false);
				else if(control.val() == '' && !checkbox.prop('checked'))
					checkbox.prop('checked', true);
			});
		});		
	});
	
	//
	// T_LOOKUP fields with CARDINALITY_MULTIPLE
	//
	$('.multiple-select-hidden').each(function() {		
		var field = $(this).attr('name');
		var dropdown_id = '#' + field + '_dropdown';
		var list_id = '#' + field + '_list';
		var button_id = '#' + field + '_add';
 		var hidden_input = this;
		
		$(dropdown_id).val('').change();
		
		// automatic add
		$(dropdown_id).on('change', function() {			
			if($(dropdown_id).val() === null)
				return false; // don't submit the form
			
			// add selected item to hidden input
			var list = parse_multiple_val($(hidden_input).val());
			list.push($(dropdown_id).val());			
			$(hidden_input).val(write_multiple_val(list));
			
			// append selected item to bullet list
			$(list_id).append(
				'<div class="multiple-select-item">' +
				'<a role="button" onclick="remove_linked_item(this)" data-field="'+ field +'" data-id="' + $(dropdown_id).val() +'"><span class="glyphicon glyphicon-trash"></span></a> ' + 				
				'<span class="multiple-select-text">' + $(dropdown_id + ' option:selected').text() + '</span>');			
			
			// remove added item from dropdown
			$(dropdown_id + " option[value='" + $(dropdown_id).val() + "']").each(function() { $(this).remove(); });			
			
			// reset dropdown selection
			$(dropdown_id).val('').change();			
						
			// console.log($(hidden_input).val());
			
			return false; // don't submit the form		
		});	
	});
	
	//
	// "Create New" button event handler for T_LOOKUP
	// 
	$('button[data-create-url]').click(function() {		
		window.open($(this).data('create-url'), $(this).data('create-title'),
			'location=0,menubar=0,resizable=1,width=400,height=600');
	});		
});

//
// parse and write select2 multiple value
//
function parse_multiple_val(str) { return JSON.parse(!str || str == '' ? '[]' : str); }
function write_multiple_val(arr) { return JSON.stringify(arr); }

//
// Insert item into select2 
//
function insert_option_sorted(dropdown_id, value, text, selected) {
	// insert removed element sorted into the dropdown
	var insert_before = -1;
	$(dropdown_id).children('option').each(function () {
		if(text.localeCompare($(this).text()) <= 0) {
			insert_before = $(this).val();					
			return false; // breaks the each() loop
		}
	});
	
	var opt = $('<option/>', { value: value }).text(text);	
	if(insert_before == -1)
		$(dropdown_id).append(opt);
	else
		opt.insertBefore($(dropdown_id + ' option[value="' + insert_before + '"]'));
	
	$(dropdown_id).val(selected ? value : '').change();
}

//
// Removal of linked item in T_LOOKUP fields with CARDINALITY_MULTIPLE
//
function remove_linked_item(e) {	
	var removed_id = $(e).attr('data-id');
	var field = $(e).attr('data-field');
	var dropdown_id = '#' + field + '_dropdown';
	var removed_text = $(e).next('span.multiple-select-text').text();
	
	// remove the value from the hidden input
	var list = parse_multiple_val($('input#' + field).val());
	for(var i = 0; i < list.length - 1; i++) {
		if(list[i].toString() == removed_id.toString()) {
			list.splice(i, 1);
			break;
		}
	}		
	$('input#' + field).val(write_multiple_val(list));
	
	// remove the list item
	$(e).closest('div').remove();
	
	insert_option_sorted(dropdown_id, removed_id, removed_text, false);
}

//
// Function to call for popup window that creates new record for T_LOOKUP
//
function handle_create_new_result(result) {
	insert_option_sorted('#' + result.lookup_field + '_dropdown',
		result.value, result.label, true);
}

//
// For T_UPLOAD
//
$(document).on('change', '.btn-file :file', function() {
  var input = $(this);
  var numFiles = input.get(0).files ? input.get(0).files.length : 1;
  var label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
  input.trigger('fileselect', [numFiles, label]);
});
$(document).ready(function() {
    $('.btn-file :file').on('fileselect', function(event, numFiles, label) {
        var log = numFiles > 1 ? numFiles + ' files selected' : label;
		//var input = $(this).parents('.input-group').find(':text');
        $('span.filename#' + $(this).data('text')).text(log);
    });
});