/* ==========================================================================================================
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
/* end jQuery resize event */

//------------------------------------------------------------------------------------------
// jQuery function to link the enabled status of a control with a check/radio control
$.fn.extend({
//------------------------------------------------------------------------------------------
    enabledBy: function(toggle) {
        var self = $(this);
        self.prop('disabled', !toggle.is(':checked'));
        toggle.change(function() {
            self.prop('disabled', !toggle.is(':checked'));
        });
        return this;
    }
});

//------------------------------------------------------------------------------------------
$(window).load(function() {
//------------------------------------------------------------------------------------------
    set_logout_button_handler();
    init_file_selection_handler();
    make_dropdowns_select2();
	set_install_clipped_text_handler();
	init_popovers();
    init_search_popup();
    set_popover_close_handler();
    init_null_value_handler();
	init_multilookup_dropdowns();
    set_create_new_handler();
    init_height_adjustment();
	highlight_diffs_in_mode_list();
	ensure_hidden_input_submission();
    handle_tabs();
    set_dblclick_handler();
    set_map_picker_handler();
    prepare_navigate_away_warning();
    init_remaining_chars_display();
});

//------------------------------------------------------------------------------------------
function set_dblclick_handler() {
//------------------------------------------------------------------------------------------
    // when row double clicked in MODE_LIST, go to MODE_VIEW of the dbl
    $('table.table').dblclick(function(e) {
        if(!e.target) return;
        var row = $(e.target).closest('tr');
        if(row.length == 0) return;
        var link = row.find('td a[data-purpose="view"]');
        if(link.length == 0) return;
        window.location = link.first().attr('href');
    });
}

//------------------------------------------------------------------------------------------
function adjust_tabs_aware_hrefs() {
//------------------------------------------------------------------------------------------
    // adapt edit button href based on tab
    $('a.tabs-aware').each(function() {
        var a = $(this);
        var href = a.attr('href');
        var i = href.lastIndexOf('#');
        a.attr('href', (i < 0 ? href : href.substr(0, i)) + window.location.hash);
    });
}

//------------------------------------------------------------------------------------------
function handle_tabs() {
//------------------------------------------------------------------------------------------
    // this solution based on solution http://stackoverflow.com/a/12138756/5529515
    // by tomaszbak (http://stackoverflow.com/users/478584/tomaszbak)
    var hash = window.location.hash;
    hash && $('ul.nav-tabs a[href="' + hash + '"]').tab('show');
    adjust_tabs_aware_hrefs();

    $('.nav-tabs a').click(function (e) {
        $(this).tab('show');
        var scrollmem = $('body').scrollTop() || $('html').scrollTop();
        window.location.hash = this.hash;
        adjust_tabs_aware_hrefs();
        $('html,body').scrollTop(scrollmem);
    });
}

//------------------------------------------------------------------------------------------
function set_logout_button_handler() {
//------------------------------------------------------------------------------------------
    $('#logout').on('click', function() {
		$.get('?mode=logout', function(data) {
			location.reload();
		});
	});
}

//------------------------------------------------------------------------------------------
function show_hide_async_alert(box, show, error_msg = null) {
//------------------------------------------------------------------------------------------
    $('#lookup-async-alert').remove();
    if(show) {
        var alert = $('#lookup-async-alert');
        var text = error_msg ? error_msg : box.prevAll('.help-block').text();
        if(alert.length == 0 && typeof text === 'string' && text.length > 0) {
            $('#main-container').prepend($('<div/>').attr({
                id: 'lookup-async-alert'
            }).addClass('alert alert-'+ (error_msg? 'danger' : 'warning') +' fade in').text(text))
        }
    }
}

//------------------------------------------------------------------------------------------
function make_dropdowns_select2() {
//------------------------------------------------------------------------------------------
    if(!window.jQuery.fn.select2)
        return;
    $('select').each(function() {
		var box = $(this);
        if(box.data('no-select2') == '1')
            return;
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
                        //console.log('Searching for "' + params.term + '"');
                        show_hide_async_alert(box, false);
						return {
							q: params.term,
							table: box.data('thistable'),
							field: box.data('fieldname'),
							val: box.data('lookuptype') == 'multiple' ? $('#' + box.data('fieldname')).val() : ''
						};
					},
					processResults: function (data) {
                        show_hide_async_alert(box, data.is_limited || data.error_message, data.error_message);
                        return { results: data.items };
					},
					delay: box.data('asyncdelay') ? parseInt(box.data('asyncdelay')) : 0
				}
			}).on('select2:close', function() {
                show_hide_async_alert(box, false);
            });
		}
		else {
			// display search box in dropdown if more than 5 options available
            if(box.children('option').length > 5)
				box.select2({ theme: 'bootstrap', width: width });
			else
				box.select2({ theme: 'bootstrap', width: width, minimumResultsForSearch: Infinity });
		}

        var maxnum = box.data('maxnum');
        if(maxnum && parseInt(box.data('initialcount')) >= parseInt(maxnum))
            multi_lookup_field_allow(box.data('fieldname'), false);

        if(box.val() != '')
			box.change();
	});
}

//------------------------------------------------------------------------------------------
function set_install_clipped_text_handler() {
//------------------------------------------------------------------------------------------
    $('a.clipped_text').on('click', function() {
        $(this).toggle().next('span.clipped_text').toggle();
    });
}

//------------------------------------------------------------------------------------------
function init_popovers() {
//------------------------------------------------------------------------------------------
    $('[data-toggle=popover][data-purpose=help]').popover({
        html: true,
        sanitize: false
    }).on('show.bs.popover', function() {
        let e = $(this);
        if(e.data('min-width')) {
            e.data('bs.popover').tip().css({
                'min-width': e.data('min-width')
            });
        }
    });
}

//------------------------------------------------------------------------------------------
function init_search_popup() {
//------------------------------------------------------------------------------------------
    $('[data-toggle=popover][data-purpose=search]').each(function() {
        // copy any pre-set value for the search field
        $(this).on('shown.bs.popover', function(){
            $('#searchoption').val($(this).data('option'));
            $('#searchtext').val($(this).data('value')).focus();
        });

        // set the form content specific to the field
        $(this).data('content', search_popover_template.replace('%FIELDNAME%', $(this).data('field'))).popover({
            html: true,
            sanitize: false,
            container: '#search-popover' // needed for CSS styling
        });
    });
}

//------------------------------------------------------------------------------------------
function set_popover_close_handler() {
//------------------------------------------------------------------------------------------
    $('body').on('click', function (e) {
        $('[data-toggle="popover"]').each(function () {
            if (!$(this).is(e.target) && $(this).has(e.target).length == 0 && $('.popover').has(e.target).length == 0)
                $(this).popover('hide');
        });
    });
}

//------------------------------------------------------------------------------------------
function update_null_value_checkbox(control) {
//------------------------------------------------------------------------------------------
    $('input[name="' + control.attr('name') + '__null__"]').each(function() {
        var box = $(this);
        if(control.val() !== '' && box.prop('checked'))
            box.prop('checked', false);
        else if(control.val() === '' && !box.prop('checked'))
            box.prop('checked', true);
    });
}

//------------------------------------------------------------------------------------------
function init_null_value_handler() {
//------------------------------------------------------------------------------------------
    // handle NULL checkbox updates for fields that are not required
    $('input[type="text"]:not([required]), input[type="number"]:not([required]), textarea:not([required])').each(function() {
        var control = $(this);
        control.on('input', function(e) {
            update_null_value_checkbox(control);
        });
    });
}

//------------------------------------------------------------------------------------------
$.wait = function(ms) {
//------------------------------------------------------------------------------------------
    var defer = $.Deferred();
    setTimeout(function() { defer.resolve(); }, ms);
    return defer;
};

//------------------------------------------------------------------------------------------
function insert_linked_item_sorted(new_item) {
//------------------------------------------------------------------------------------------
    new_item.addClass('transition just-added');
    new_label = transl(new_item.find('.display-label').first().text());

    var swaps = 0;
    var next_item = new_item;
    do {
        next_item = next_item.next('div.multiple-select-item');
        if(next_item.length == 0)
            break;
        next_item = next_item.first();
        next_label = transl(next_item.find('.display-label').first().text());
        if(new_label.localeCompare(next_label) <= 0)
            break;
        swaps++;
    } while(true);

    setTimeout(function() {
        if(swaps > 0) {
            var delay = 500 / swaps;
            for(var i = 0; i < swaps; i++) {
                setTimeout(function() {
                    new_item.next('div.multiple-select-item').first().detach().insertBefore(new_item);
                }, delay * i);
            }
        }

        setTimeout(function() {
            new_item.removeClass('just-added');
        }, delay * swaps);
    }, 500);
}

//------------------------------------------------------------------------------------------
function init_multilookup_dropdowns() {
//------------------------------------------------------------------------------------------
    // adjust hidden value list after selection changes
    // and add selected items to the list below the dropdown
    $('.multiple-select-hidden').each(function() {
        var field = $(this).attr('name');
        var dropdown_id = '#' + field + '_dropdown';
        var list_id = '#' + field + '_list';
        var hidden_input = this;
        var dropdown_box = $(dropdown_id);

        dropdown_box.val('').change();

        // automatic add
        dropdown_box.on('change', function() {
            var selected_value = dropdown_box.val();
            if(selected_value === null || selected_value === '')
                return;

            // need to extract the plain option text label (without the key value in parentheses)
            // in "normal" lookup boxes this is in the selected option's data label attribute
            // the create new result in async boxes is also in the option's data label attribute
            // in "async" lookup boxes this is in the "label" attribute of the select2's data object
            var selected_option = $(dropdown_id + ' option:selected');
            var label = selected_option.data('label');
            if(typeof label === 'undefined') {
                var data_arr = dropdown_box.select2('data');
                if(Array.isArray(data_arr) && data_arr.length > 0)
                    label = data_arr[0].label;
            }
            if(typeof label === 'undefined') // to be sure we find the culprit faster next time
                console.error('Something wrong again with the way select2 stores the item label in the option data after processing the async lookup results!');

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
                $(hidden_input).val(write_multiple_val(list)).change();

                // add item line to list of selected items
                var linked_items = $(list_id);
                linked_items.prepend(data);
                let inserted_item = linked_items.find('.multiple-select-item').first();

                // if the added item was initially present, then removed, then added again, we can remove any linkage-details-missing warning >>
                let remInitArr = dropdown_box.data('removed-initial-items');
                if(remInitArr && remInitArr.includes(String(inserted_item.data('id-other'))))
                    inserted_item.find('.linkage-details-missing').removeClass('linkage-details-missing');
                // <<
                
                insert_linked_item_sorted(inserted_item);

                // remove added item from dropdown
                $(dropdown_id + " option[value='" + selected_value + "']").each(function() {
                    $(this).remove();
                });

                var maxnum = dropdown_box.data('maxnum');
                if(maxnum && list.length >= maxnum) {
                    // disable box and create new button
                    multi_lookup_field_allow(field, false);
                }

                // reset dropdown selection
                dropdown_box.val('').change();
            });
        });
    });
}

//------------------------------------------------------------------------------------------
function check_missing_linkage_details_warning() {
//------------------------------------------------------------------------------------------
    let hasErrors = false;
    $('div.form-group').each(function () {
        let row = $(this);
        let error = row.find('.linkage-details-missing').length > 0;
        if(error && !row.hasClass('has-error')) {
            row.addClass('has-error').find('div').first().append(
                $('<span/>').addClass('validation-error help-block').html(
                    row.find('span.linkage-details-error-message').html()
                )
            );
            hasErrors = true;
        }
        else if(!error && row.hasClass('has-error')) {
            row.removeClass('has-error').find('span.linkage-details-error-message').remove();
        }
    });
    return hasErrors;
}

//------------------------------------------------------------------------------------------
// calc the desired pos of the popup on the screen (should be centered on the clicked elem)
function get_popup_position(elem, width, height) {
//------------------------------------------------------------------------------------------
    var wnd_offset = {
        x: window.screenLeft ? window.screenLeft : window.screenX,
        y: window.screenTop ? window.screenTop : window.screenY
    };
    var btn = $(elem);
    var btn_center = {
        x: wnd_offset.x + btn.offset().left + btn.outerWidth() - window.pageXOffset,
        y: wnd_offset.y + btn.offset().top + btn.outerHeight() - window.pageYOffset
    }
    var popup = {
        width: width,
        height: height
    };
    popup.x = btn_center.x - popup.width / 2;
    popup.y = btn_center.y - popup.height / 2;
    var oversizeX = popup.x + popup.width - screen.width;
    if(oversizeX > 0)
        popup.x -= oversizeX;
    var oversizeY = popup.y + popup.heigh - screen.height;
    if(oversizeY > 0)
        popup.y -= oversizeY;
    return popup;
}

//------------------------------------------------------------------------------------------
function set_map_picker_handler() {
//------------------------------------------------------------------------------------------
    $('a[data-map-url]').click(function() {
        var popup = get_popup_position(this, 800, 800);
        var url = $(this).data('map-url');
        var target_ctrl = $('#' + $(this).data('target-ctrl'));
        if(target_ctrl.length > 0)
            url += '&val=' + encodeURI(target_ctrl.val());
        window.open(
            url,
            '_blank',
            'location=0,menubar=0,resizable=1,scrollbars=1,toolbar=0,left='+popup.x+',top='+popup.y+',width='+popup.width+',height='+popup.height
        );
    });
}


//------------------------------------------------------------------------------------------
function set_create_new_handler() {
//------------------------------------------------------------------------------------------
    $('button[data-create-url]').click(function() {
        var popup = get_popup_position(this, 770, 700);
        var predef_depend = $(this).data('depend');
        var url_append = '';
        if(typeof predef_depend === 'object') {
            for(var f in predef_depend) {
                if(!predef_depend.hasOwnProperty(f))
                    continue;
                var v = $('#' + predef_depend[f]).val(); // for normal fields
                if(typeof v === 'undefined')
                    v = $('#' + predef_depend[f] + '_dropdown').val(); // could be a dropdown
                if(typeof v !== 'undefined' && String(v) != '')
                    url_append += '&' + 'pre:' + encodeURIComponent(f) + '=' + encodeURIComponent(v);
            }
        }
        window.open(
            $(this).data('create-url') + url_append,
            /*$(this).data('create-title')*/ '_blank',
            'location=0,menubar=0,resizable=1,scrollbars=1,toolbar=0,left='+popup.x+',top='+popup.y+',width='+popup.width+',height='+popup.height
        );
    });
}

//------------------------------------------------------------------------------------------
function init_height_adjustment() {
//------------------------------------------------------------------------------------------
    $(window).resize(adjust_div_full_height);
    adjust_div_full_height();
}

//------------------------------------------------------------------------------------------
function highlight_diffs_in_mode_list() {
//------------------------------------------------------------------------------------------
    // mark changes in a table table
    // TODO: this is not used yet, but works and makes sense only when the
    // table view ordered descending chronologically by change
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
}

//------------------------------------------------------------------------------------------
function ensure_hidden_input_submission() {
//------------------------------------------------------------------------------------------
    // make sure disabled controls are also sent when a form is submitted
    $('form').bind('submit', function () {
        $(this).find(':input').prop('disabled', false);
    });
}

//------------------------------------------------------------------------------------------
//  adjust fill-height div to maximum height
function adjust_div_full_height() {
//------------------------------------------------------------------------------------------
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
    var margin = 0;
    if(div.data('margin-bottom'))
        margin = parseInt(div.data('margin-bottom'));
	div.css('height', height - div.offset().top - margin);
}

//------------------------------------------------------------------------------------------
// "Edit Details" event handler for T_LOOKUP / MULTIPLE
function linkage_details_click(a) {
//------------------------------------------------------------------------------------------
    var popup = get_popup_position(a, 500, 700);
    window.open($(a).data('details-url'), $(a).data('details-title'),
		'scrollbars=1,location=0,menubar=0,resizable=1,left='+popup.x+',top='+popup.y+',width='+popup.width+',height='+popup.height);
}
//------------------------------------------------------------------------------------------
// "Edit Other" event handler for T_LOOKUP / MULTIPLE
function lookup_edit_other(a) {
//------------------------------------------------------------------------------------------
    var popup = get_popup_position(a, 500, 700);
	window.open($(a).data('edit-url'), '_blank',
		'scrollbars=1,location=0,menubar=0,resizable=1,left='+popup.x+',top='+popup.y+',width='+popup.width+',height='+popup.height);
}

//------------------------------------------------------------------------------------------
// parse select2 multiple value
function parse_multiple_val(str) {
//------------------------------------------------------------------------------------------
    return JSON.parse(!str || str == '' ? '[]' : str);
}

//------------------------------------------------------------------------------------------
// write select2 multiple value
function write_multiple_val(arr) {
//------------------------------------------------------------------------------------------
    return JSON.stringify(arr);
}

//------------------------------------------------------------------------------------------
// Insert item into select2
function insert_option_sorted(dropdown_id, value, label, text, selected) {
//------------------------------------------------------------------------------------------
	// insert removed element sorted into the dropdown
    // we also need to do this in case of lookup-async, since the "create new" result is "ingested" here
    var $dropdown = $('#' +  dropdown_id);
    var insert_before = -1;
	$dropdown.children('option').each(function () {
		if(text.localeCompare($(this).text()) <= 0) {
			insert_before = $(this).val();
			return false; // breaks the each() loop
		}
	});

	var opt = $('<option/>', { value: value }).text(text).data('label', label);
	if(insert_before == -1)
		$dropdown.append(opt);
	else
		opt.insertBefore($('#' + dropdown_id + ' option[value="' + insert_before + '"]'));

    if(selected) // select, if this is the box where Create New was clicked
	   $dropdown.val(value).change();
}

//------------------------------------------------------------------------------------------
// Removal of linked item in T_LOOKUP fields with CARDINALITY_MULTIPLE
function remove_linked_item(e) {
//------------------------------------------------------------------------------------------
	var $e = $(e);
    var item_div = $e.closest('.multiple-select-item').first();
	var removed_id = $e.data('id');
	var field = $e.data('field');
	var label = item_div.find('span.display-label').text();
    var dropdown_id = field + '_dropdown';
    let dropdown_box = $('#' + dropdown_id);
	var removed_text = item_div.find('.multiple-select-text').text();

	// remove the value from the hidden input
	var list = parse_multiple_val($('input#' + field).val());
	for(var i = 0; i < list.length; i++) {
		if(list[i].toString() == removed_id.toString()) {
			list.splice(i, 1);
			break;
		}
	}
	$('input#' + field).val(write_multiple_val(list)).change();

	// remove the list item
	item_div.fadeOut(100, () => { 
        item_div.remove(); 

        // we need to remember those items that were 
        if(!item_div.data('newly-added')) {
            if(!dropdown_box.data('removed-initial-items'))
                dropdown_box.data('removed-initial-items', [String(removed_id)]);
            else if(!dropdown_box.data('removed-initial-items').includes(String(removed_id)))
                dropdown_box.data('removed-initial-items').push(String(removed_id));
        }
    });

    // only need to insert in non-async boxes
    if(!dropdown_box.hasClass('lookup-async'))
	   insert_option_sorted(dropdown_id, removed_id, label, removed_text, false);

    // ensure box and create new are enabled
    multi_lookup_field_allow(field, true);
}

//------------------------------------------------------------------------------------------
function multi_lookup_field_allow(field_id, enable) {
//------------------------------------------------------------------------------------------
    var dropdown = $('#' + field_id + '_dropdown');
    var create_new_buttong = $('#' + field_id + '_add');
    if(dropdown.prop('disabled') === enable) {
        dropdown.prop('disabled', !enable);
        create_new_buttong.prop('disabled', !enable);
    }
}

//------------------------------------------------------------------------------------------
// Function to call for popup window that creates new record for T_LOOKUP
function handle_create_new_result(result) {
//------------------------------------------------------------------------------------------
	// insert the new record in all dropdown boxes of the same table type
	var dropdown_id = result.lookup_field + '_dropdown';
	var table = $('#' + dropdown_id).data('table');

	$('select[data-table]').each(function() {
		var $this = $(this);
		if($this.data('table') == table) {
			insert_option_sorted($this.attr('id'), result.value, String(result.label),
				String(result.text), dropdown_id == $this.attr('id'));
		}
	});
}

//------------------------------------------------------------------------------------------
// For T_UPLOAD, show the selected file name next to the "Browse" button
function init_file_selection_handler() {
//------------------------------------------------------------------------------------------
    $('.btn-file :file').on('fileselect', function(event, numFiles, label) {
        var log = numFiles > 1 ? numFiles + ' files selected' : label;
        $('span.filename#' + $(this).data('text')).text(log);
    });

    $(document).on('change', '.btn-file :file', function() {
      var input = $(this);
      var numFiles = input.get(0).files ? input.get(0).files.length : 1;
      var label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
      input.trigger('fileselect', [numFiles, label]);
    });
}

//------------------------------------------------------------------------------------------
function prepare_navigate_away_warning() {
//------------------------------------------------------------------------------------------
    $('form[data-navigate-away-warning="true"]')
    .submit(function() {
        set_navigate_away_warning(false);
    })
    .find(':input').on('change', function() {
        set_navigate_away_warning(true);
    });
}

//------------------------------------------------------------------------------------------
function set_navigate_away_warning(/*bool*/ on) {
//------------------------------------------------------------------------------------------
    window.onbeforeunload = (on? (function() { return true }) : null);
}

//------------------------------------------------------------------------------------------
function init_remaining_chars_display() {
//------------------------------------------------------------------------------------------
    $('.remaining-chars span[data-control-id]').each(function () {
        var label = $(this);
        var ctrl = $('#' + label.data('control-id')).first();
        var maxlen = parseInt(ctrl.attr('maxlength'));
        label.text(maxlen - ctrl.val().length);
        ctrl.bind('keydown focus', function() {
            setTimeout(function() {
                var remaining = maxlen - ctrl.val().length;
                label.text(remaining);
            }, 5);
        }).bind('focusout focusin', function (e) {
            e.type === 'focusin' ? label.parent().show() : label.parent().hide();
        });
    })
}
