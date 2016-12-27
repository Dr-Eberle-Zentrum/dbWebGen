/*
 * jQuery resize event - v1.1 - 3/14/2010
 * http://benalman.com/projects/jquery-resize-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function($,h,c){var a=$([]),e=$.resize=$.extend($.resize,{}),i,k="setTimeout",j="resize",d=j+"-special-event",b="delay",f="throttleWindow";e[b]=250;e[f]=true;$.event.special[j]={setup:function(){if(!e[f]&&this[k]){return false}var l=$(this);a=a.add(l);$.data(this,d,{w:l.width(),h:l.height()});if(a.length===1){g()}},teardown:function(){if(!e[f]&&this[k]){return false}var l=$(this);a=a.not(l);l.removeData(d);if(!a.length){clearTimeout(i)}},add:function(l){if(!e[f]&&this[k]){return false}var n;function m(s,o,p){var q=$(this),r=$.data(this,d);r.w=o!==c?o:q.width();r.h=p!==c?p:q.height();n.apply(this,arguments)}if($.isFunction(l)){n=l;return m}else{n=l.handler;l.handler=m}}};function g(){i=h[k](function(){a.each(function(){var n=$(this),m=n.width(),l=n.height(),o=$.data(this,d);if(m!==o.w||l!==o.h){n.trigger(j,[o.w=m,o.h=l])}});g()},e[b])}})(jQuery,this);
$.fn.extend({
    deferredResize : function(fn, delay){
        var timer = null;
        $(this).resize(function(){
            if(timer != null){
                clearTimeout(timer);
                timer = null;
            }
            timer = setTimeout(fn, delay);
        });
        return this;
    }
});

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
		var box = $(this);
        var width = '100%';

		if(box.hasClass('lookup-async')) {
			box.select2({
				// general select2 options are defined in the data-* attributes of the <select> element
				theme: 'bootstrap',
				width: width,
				ajax: {
					url: '?mode=func&target=lookup_async',
					type: 'POST',
					data: function (params) {
						return {
							q: params.term,
							table: box.data('thistable'),
							field: box.data('fieldname'),
							val: box.data('lookuptype') == 'multiple' ? $('#' + box.data('fieldname')).val() : ''
						};
					},
					processResults: function (data) {
						return { results: data };
					},
					delay: box.data('asyncdelay')
				}
			});
		}
		else {
			// display search box in dropdown if more than 5 options available
			if(box.children('option').length > 5)
				box.select2({ theme: 'bootstrap', width: width });
			else
				box.select2({ theme: 'bootstrap', width: width, minimumResultsForSearch: Infinity });
		}

        if(box.val() != '')
			box.change();
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
		// copy any pre-set value for the search field
		$(this).on('shown.bs.popover', function(){
			$('#searchoption').val($(this).data('option'));
			$('#searchtext').val($(this).data('value')).focus();
		});

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
		$('input[name="' + control.attr('name') + '__null__"]').each(function() {
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
			var selected_value = $(dropdown_id).val();
			if(selected_value === null || selected_value === '')
				return;

			// need to extract the plain option text (without the key value in parentheses)
			// in "normal" lookup boxes this is in the option's "data-label" attribute
			// in "async" lookup boxes this is in the "label" attribute of the option's data object
			var sel_option = $(dropdown_id + ' option:selected');
			var label = typeof sel_option.data('data').label === 'undefined' ? sel_option.data('label') : sel_option.data('data').label;

			// append selected item to bullet list
			$.get('', {
				mode: 'func',
				target: 'get_linked_item_html',
				table: $('#__table_name__').val(),
				self_id: $('#__item_id__').val(),
				parent_form: $('#__form_id__').val(),
				field: field,
				other_id: selected_value,
				label: label
			}, function(data) {
				// add selected item to hidden input
				var list = parse_multiple_val($(hidden_input).val());
				list.push(selected_value);
				$(hidden_input).val(write_multiple_val(list));

				// add item line to list of selected items
				$(list_id).append(data);

				// remove added item from dropdown
				$(dropdown_id + " option[value='" + selected_value + "']").each(function() {
					$(this).remove();
				});

				// reset dropdown selection
				$(dropdown_id).val('').change();
			});
		});
	});

	//
	// "Create New" button event handler for T_LOOKUP
	//
	$('button[data-create-url]').click(function() {
		window.open($(this).data('create-url'), $(this).data('create-title'),
			'scrollbars=1,location=0,menubar=0,resizable=1,width=400,height=600');
	});

	//
	// adjust fill height div
	$(window).resize(adjust_div_full_height);
	adjust_div_full_height();

	//
	// mark changes in a table table
	// TODO: this is not used yet, but works and makes sense only when the
	// table view ordered descending chronologically by change
	//
	$('table[data-highlightchanges]').each(function() {
		var table = $(this), tr_prev = [], row = 0;
		table.find('tbody tr').each(function () {
			var tr = $(this), col = 0;
			tr.children('td').each(function() {
				var td = $(this);
				if(tr_prev[col] != undefined && tr_prev[col].html() != td.html())
					tr_prev[col].addClass('bg-danger');
				tr_prev[col++] = td;
			});
		});
	});

	// make sure disabled controls are also sent when a form is submitted
	$('form').bind('submit', function () {
		$(this).find(':input').prop('disabled', false);
	});
});

//
// adjust fill-height div to maximum height
//
function adjust_div_full_height() {
	var div = $('div.fill-height');
	if(div.length === 0)
		return;

	var height = 0;
	var body = window.document.body;
	if (window.innerHeight) {
		height = window.innerHeight;
	} else if (body.parentElement.clientHeight) {
		height = body.parentElement.clientHeight;
	} else if (body && body.clientHeight) {
		height = body.clientHeight;
	}
	div.css('height', (height - div.offset().top) + "px");
}

//
// "Edit Details" event handler for T_LOOKUP / MULTIPLE
//
function linkage_details_click(a) {
	window.open($(a).data('details-url'), $(a).data('details-title'),
		'scrollbars=1,location=0,menubar=0,resizable=1,width=400,height=600');
}

//
// parse and write select2 multiple value
//
function parse_multiple_val(str) { return JSON.parse(!str || str == '' ? '[]' : str); }
function write_multiple_val(arr) { return JSON.stringify(arr); }

//
// Insert item into select2
//
function insert_option_sorted(dropdown_id, value, label, text, selected) {
	// insert removed element sorted into the dropdown
	var insert_before = -1;
	var $dropdown = $('#' +  dropdown_id);
	$dropdown.children('option').each(function () {
		if(text.localeCompare($(this).text()) <= 0) {
			insert_before = $(this).val();
			return false; // breaks the each() loop
		}
	});

	var opt = $('<option/>', { value: value }).html(text).data('label', label);
	if(insert_before == -1)
		$dropdown.append(opt);
	else
		opt.insertBefore($('#' + dropdown_id + ' option[value="' + insert_before + '"]'));

	$dropdown.val(selected ? value : '').change();
}

//
// Removal of linked item in T_LOOKUP fields with CARDINALITY_MULTIPLE
//
function remove_linked_item(e) {
	var $e = $(e);
	var removed_id = $e.data('id');
	var field = $e.data('field');
	var label = $e.data('label');
	var dropdown_id = field + '_dropdown';
	var removed_text = $e.parent().find('span.multiple-select-text').text();

	// remove the value from the hidden input
	var list = parse_multiple_val($('input#' + field).val());
	for(var i = 0; i < list.length; i++) {
		if(list[i].toString() == removed_id.toString()) {
			list.splice(i, 1);
			break;
		}
	}
	$('input#' + field).val(write_multiple_val(list));

	// remove the list item
	$e.closest('div').remove();

	insert_option_sorted(dropdown_id, removed_id, label, removed_text, false);
}

//
// Function to call for popup window that creates new record for T_LOOKUP
//
function handle_create_new_result(result) {
	// insert the new record in all dropdown boxes of the same table type
	var dropdown_id = result.lookup_field + '_dropdown';
	var table = $('#' + dropdown_id).data('table');

	$('select[data-table]').each(function() {
		var $this = $(this);
		if($this.data('table') == table) {
			insert_option_sorted($this.attr('id'), result.value, result.label,
				result.text, dropdown_id == $this.attr('id'));
		}
	});
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
        $('span.filename#' + $(this).data('text')).text(log);
    });
});
