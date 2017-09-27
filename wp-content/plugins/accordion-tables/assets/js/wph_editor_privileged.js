/*Ensuring wph_editor object is available and read to be hooked into by other plugins*/
if ( typeof wph_editor !== "object" ){
	var wph_editor = { };

	/*Plugins hook in here*/
	wph_editor.plugins = { };
}

/*Hook system*/
if ( typeof wph_editor.actions === "undefined" ) wph_editor.actions = { };

wph_editor.do_action = function ( hook_name, arg ){
	var actions = wph_editor.actions[ hook_name ];
	if ( actions ){
		jQuery.each( actions, function (i, fn) {
			if( typeof fn ==="function" ) fn( arg );
		} )
	}

}

var wph_media_frame;

wph_editor.add_action = function (hook_name, action) {
	var actions = wph_editor.actions[hook_name];
	if (typeof actions === "undefined") actions = [];
	actions.push(action);
	wph_editor.actions[hook_name] = actions;

}

wph_editor.remove_action = function (hook_name, action) {
	var actions = wph_editor.actions[hook_name];
	if (actions) {
		jQuery.each(actions, function (i, fn) {
			if (fn === action) delete actions[i];
		})
	}
	wph_editor.actions[hook_name] = actions;

}

jQuery(document).ready(function ($) {

// ensure editor is in view
wph_editor.safe_position = function( ){
	var $editor = $( '.wph_editor_body' ),
			top = parseInt( $editor.css( 'top' ) ),
			height = $editor.height( ),
			base = height + top;
	if( base < 20 ) $editor.css( 'top', '20px' );
};

// keep editor in view after resize
wph_editor.resizeTimer = false;
$( window ).on( 'resize', function( e ){
	clearTimeout( wph_editor.resizeTimer );
	wph_editor.resizeTimer = setTimeout( function( ){
		// reposition editor
		wph_editor.safe_position( );
	}, 250 );
});

/*Inject those keys into the right places*/
wph_editor.place_keys = function () {
	$(".wph_key_keeper").each(function () {
		if ($(this).children('.wph_keys_center_horizontal').length) return;
		var data = $(this).attr("data-wph-key-type");
		$(this).append('<div class="wph_keys_center wph_keys_center_horizontal">'+wph_editor_keys[data]+'</div>');
		$(this).append('<div class="wph_keys_center wph_keys_center_vertical">'+wph_editor_keys[data]+'</div>');
	})

}

/*Unique classes for key keepers*/
wph_editor.assign_unique_classes = function () {
	var id = this.id = 1;
	$('.wph_key_keeper').each(function (i) {
		var 	$t = $(this),
				data = "data-wph-id",
				curr_id = $t.attr(data);
		if (curr_id && curr_id >= id) {
			id = curr_id;
		} else {
			$t.addClass("wph_id_"+id).attr(data, id);
		}
		id++;
	})

}

/*WPH Init - buttons and keys*/
wph_editor.init = function () {

	/*jQuery UI*/
	$('.wph_editor_body').draggable();//draggable
	$('.wph_editor_slider').each(function () {//slider
		var 	$this = $(this),
				val = parseInt($this.attr('data-wph-val')) || 1,
				min = parseInt($this.attr('data-wph-min')) || 0,
				max = parseInt($this.attr('data-wph-max')) || 100,
				step = parseInt($this.attr('data-wph-step')) || 1,
				prev = $this.prev();

		$this.slider({
			value: val,
			min:min,
			max:max,
			step:step,
			slide:function(event,ui){//slider
				$this.prev('input').val( ui.value ).change();
			}
		});

		if (prev.is('input')) prev.val(val);
	})

	/*Perfect Scrollbar*/
	$('.wph_editor_perfectScrollbar').each(function () {
		var 	$this = $(this),
				width = $this.attr('data-wph-width') || '100%',
				height = $this.attr('data-wph-height') || '300px';
		$this.width(width).height(height).perfectScrollbar();
	})

	/*Post IDs*/
	$('.wph_post_id').each(function () {
		var 	$this = $(this),
				data = "data-wph-post-id",
				data_ = '[' + data + ']',
				id = $this.attr( data );
		$this.next( ).filter( data_ ).attr( data, id ); // set it on parent
		$this.next( ).find( data_ ).attr( data, id ); // or child
		$this.remove( );
	})

	/*Toggle editor show/hide */
	$( '.wph_editor_toggle' ).click( function( ){
		editor = $( '.wph_editor_body' );
		editor.hasClass( 'wph_editor_invisible' ) ? editor.removeClass( 'wph_editor_invisible' ) : editor.addClass( 'wph_editor_invisible' );
	} )

	/*Callbacks*/
	data= { };
	wph_editor.do_action( 'init', data );

	wph_editor.init_keys( );// WPH Keys - separated for re-triggering
	wph_editor.init_once( );

}

/*WPH Keys init*/
wph_editor.init_keys = function () {
	/*Place Keys*/
	wph_editor.place_keys();

	/*Avoid Multiple Bindings*/
	$('.wph_key').off("click");

	/*Settings key*/
	/*--toggle*/
	$( '.wph_editor_generic_focus' ).click( function( ){
		var container = $( this ).closest( '.wph_container' );
		wph_editor.get_data_from_elms( container );
	} )

	/*--cell level*/
	$('.wph_keys_center_horizontal .wph_editor_key_settings').click(function () {
		var key_keeper = $(this).closest('.wph_key_keeper');
		//stop editing this element
		if (key_keeper.hasClass('wph_editor_target_element')) {
			$('.wph_editor_target_element').removeClass('wph_editor_target_element');
			if (typeof wph_editor.target !== "undefined") delete wph_editor.target;
			//or mark this element for editing
		} else {
			$('.wph_editor_target_element').not(key_keeper).removeClass('wph_editor_target_element');
			key_keeper.addClass('wph_editor_target_element');
		}

		//get data from elms
		wph_editor.get_data_from_elms(key_keeper);
	})

	/*Selection key*/
	/*--cell level*/
	$('.wph_keys_center_horizontal .wph_editor_key_select').click(function () {
		var key_keeper = $(this).closest('.wph_key_keeper');
		key_keeper.toggleClass('wph_editor_target_element');
		var key_keepers = $('.wph_editor_target_element');

		//display/hide editor
		if (!$('.wph_editor_target_element').length) $('.wph_editor_body').addClass('wph_editor_invisible')
		else $('.wph_editor_body').removeClass('wph_editor_invisible')

		//get data from elms
		wph_editor.get_data_from_elms(key_keepers);
	})

	/*--pseudo row*/
	$('.wph_keys_center_vertical .wph_editor_key_select').click(function () {
		var key_keeper = $(this).closest('.wph_key_keeper');
		key_keeper.toggleClass('wph_editor_target_pseudo_row');

	})

	/*Move key*/
	/*--cell level*/
	$('.wph_cell_level_sortable').sortable({//for cells
		handle: ".wph_keys_center_horizontal .wph_cell_keys>.wph_editor_key_move",
		connectWith: ".wph_cell_level_sortable",
		sort: wph_editor.cell_sorting,
		stop: wph_editor.cell_sorting

	})
	/*--pseudo row*/
	$( ".wph_keys_center_vertical" ).draggable({
		handle : ".wph_editor_key_move",
		axis: "y",
		revert: true,
		revertDuration: 0,
		stop: wph_editor.psuedo_row_dragged
	});

	/*--column level*/
	$('.wph_column_level_sortable').sortable({//for columns
		handle: ".wph_keys_center_horizontal .wph_column_keys>.wph_editor_key_move",
		sort: wph_editor.column_sorting,
		stop: wph_editor.column_sorting

	})

	/*Copy key*/
	$('.wph_keys_center_horizontal .wph_editor_key_copy').click(wph_editor.copy_cell);
	/*--pseudo row*/
	$('.wph_keys_center_vertical .wph_editor_key_copy').click(wph_editor.copy_pseudo_row);

	/*Delete key*/
	/*--column / cell*/
	$('.wph_keys_center_horizontal .wph_editor_key_delete').click(function () {
		var 	$this = $(this),
				cell = $this.closest('.wph_key_keeper'),
				parent = cell.parent();
		cell.remove();
		wph_editor.delete_elm(parent);

	})
	/*--pseudo row*/
	$('.wph_keys_center_vertical .wph_editor_key_delete').click(wph_editor.delete_pseudo_row);

}

/*Copy cell*/
wph_editor.copy_cell = function (insertion_rule) {
	var 	item = $(this).closest('.wph_key_keeper'), // 'this' refers to cell in case of pseudo rows and key in case of cells
			clone = item.clone(),
			selected = clone.hasClass('wph_editor_target_element') ? true : false;

	clone.removeClass('wph_editor_target_element wph_editor_target_pseudo_row');
	if (insertion_rule && insertion_rule === "pseudo row") { // copying cell for pseudo row
		var cells = Array.prototype.reverse.call(item.nextAll('.wph_cell').add(item));
		cells.each( function () {
			var next = $(this).next('.wph_marked_by_copy_process');
			if (!next.length) clone.insertAfter($(this));
		})
		wph_editor.copy_elm(item, clone);
	} else { // normal
		clone.insertAfter(item);
		wph_editor.init_keys();
		wph_editor.copy_elm(item, clone);
		if (selected) clone.find('.wph_keys_center_horizontal .wph_editor_key_select').click();
	}

}

/*Delete pseudo row*/
wph_editor.delete_pseudo_row = function (e) {
	var 	_ = this,
			starting_cells = wph_editor.target_pseudo_row_starting_cells($(_)),
			data = {
				context : _ ,
				starting_cells : starting_cells
			};
	wph_editor.do_action('delete_pseudo_row', data);

}


/*Copy pseudo row*/
wph_editor.copy_pseudo_row = function () {
	var 	_ = this,
			starting_cells = wph_editor.target_pseudo_row_starting_cells($(_)),
			data = {
				context : _ ,
				starting_cells : starting_cells
			};
	wph_editor.do_action('copy_pseudo_row', data);

}

/*Pseudo row drag*/
wph_editor.psuedo_row_dragged = function (event, ui) {
	var 	data = {event: event, ui: ui};
	wph_editor.do_action('psuedo_row_dragged', data);

}

/*Execute the shift*/
wph_editor.shift_psuedo_row = function (cell, cell_type, originalIndex, newIndex) {
	// get the targeted cells
	var 	starting_cells = wph_editor.target_pseudo_row_starting_cells(cell),
			cells = $(),
			query_attr = 'data-wph-element-name';

	starting_cells.each(function () {
		cells = cells.add(wph_editor.target_pseudo_row($(this)));
	})

	// shift the target cells
	cells.each(function () {
		var 	$this = $(this),
				parent = $this.parent(),
				detached = $this.detach(),
				insertBefore = parent.children('.wph_cell['+query_attr+'="'+cell_type+'"]').eq(newIndex);
		if (!insertBefore.length) detached.insertAfter(parent.children('.wph_cell['+query_attr+'="'+cell_type+'"]').last());
		else detached.insertBefore(insertBefore);
	})

}

/*Fetch all cells of pseudo-row based on starting cell*/
wph_editor.target_pseudo_row = function (cell) {
	var 	column = cell.closest('.wph_column'),
			columns = column, // all the affected columns
			cells = cell, // all the affected cells
			attr = 'data-wph-element-name',
			cell_type = cell.attr(attr),
			index = cell.parent().children('.wph_cell['+attr+'="'+cell_type+'"]').index(cell);
	if (!column.hasClass('wph_start_of_row')) {
		return false;
	}
	columns = column.add(column.nextUntil('.wph_end_of_row'));
	columns = columns.add(columns.last().next('.wph_end_of_row'));
	if (columns.length) {
		columns.each(function () {
			cells = cells.add($(this).find('.wph_cell['+attr+'="'+cell_type+'"]').eq(index));
		})
	}
	return cells;

}

/*Get starting cells for pseudo rows for the container*/
wph_editor.target_pseudo_row_starting_cells = function (elm) {
	if (elm.hasClass('wph_container')) { // case where container is given and we have to get all the starting cells within it
		var 	container = elm,
				starting_cells = container.find('.wph_start_of_row .wph_editor_target_pseudo_row');
	} else { // case where elm is likely a button
		var 	container = elm.closest('.wph_container'),
				starting_cells = container.find('.wph_start_of_row .wph_editor_target_pseudo_row').add(elm.closest('.wph_cell'));
	}
	return starting_cells;

}

/*Key handlers */
/*--copy*/
wph_editor.copy_elm = function (elm, clone) {
	var data = {};
	data.container = elm.closest('.wph_container');
	data.plugin = data.container.attr('data-wph-plugin');
	data.elm = elm;
	data.clone = clone;

	wph_editor.do_action('copy_elm', data)
	wph_editor.set_save_status(false, data.container);

};

/*--delete*/
wph_editor.delete_elm = function (elm) {
	//hide editor if the only selected items were deleted
	if (!$('.wph_editor_target_element').length) $('.wph_editor_body').addClass('wph_editor_invisible');
	// prepare an action hook
	var data = {};
	data.container = elm.hasClass('wph_container') ? elm : elm.closest('.wph_container');
	data.plugin = data.container.attr('data-wph-plugin');
	data.parent = elm;

	wph_editor.do_action('delete_elm', data)
	wph_editor.set_save_status(false, data.container);

};

/*--cell sorting*/
wph_editor.cell_sorting = function (event, ui) {
	var data = {};
	data.container = ui.item.closest('.wph_container');
	data.plugin = data.container.attr('data-wph-plugin');
	data.event = event;
	data.ui = ui;

	wph_editor.do_action('cell_sorting', data);
	wph_editor.set_save_status(false, data.container);

}

/*--column sorting*/
wph_editor.column_sorting = function (event, ui) {
	var data = {};
	data.container = ui.item.closest('.wph_container');
	data.plugin = data.container.attr('data-wph-plugin');
	data.event = event;
	data.ui = ui;

	wph_editor.do_action('column_sorting', data);
	wph_editor.set_save_status(false, data.container);

}

/*Get data from element(s)*/
wph_editor.get_data_from_elms = function (elms) {
	if (!elms) elms = $('.wph_editor_target_element');

	wph_editor.decide_display();

	var 	multi = elms.length > 1,
			plugin = elms.hasClass('wph_container') ? elms.attr('data-wph-plugin') : elms.closest('.wph_container').attr('data-wph-plugin'),
			components = wph_editor.plugins[plugin]['get_components'](elms);

	//setting up the view
	$('.wph_editor_body').attr({
		'data-wph-plugin': plugin,
		'data-wph-key-type':elms.attr('data-wph-key-type')
	});

	//target details
	var element_name = components.Element;
	delete components.Element;
	var target = {
		components:components,
		element_name: element_name,
		current:{},
		multi:multi,
		parents:elms,
		container: elms.hasClass('wph_container') ? elms : elms.closest('.wph_container'),
		plugin: plugin
	};

	if (element_name === "Container") target.current.component = target.container; // else it will not be set as components section will be hidden

	//action hook
	wph_editor.do_action('before_load_data_to_editor');
	wph_editor.load_data_to_editor(target);
	wph_editor.do_action('after_load_data_to_editor');
	$( '.wph_editor_body' ).trigger( 'after_load_data_to_editor' );

}

/*Display/hide editor*/
wph_editor.decide_display = function () {
	if (!$('.wph_editor_target_element').length) $('.wph_editor_body').addClass('wph_editor_invisible');
	else $('.wph_editor_body').removeClass('wph_editor_invisible');

}

/*Set save status*/
wph_editor.set_save_status = function (saved, _container) { // true | false | undefined
	var 	container = _container ? _container : wph_editor.target.container,
			icon = $('.wph_editor_save');
	//check save status
	if (typeof saved === "undefined") {
		saved = container.data('wph-editor-saved');
		if (typeof saved === "undefined") saved = true; // element loaded the first time, so already saved
	}
	if (saved) {
		container.data('wph-editor-saved', true);
		if (!_container || (wph_editor.target && _container === wph_editor.target.container)) icon.removeClass('wph_editor_unsaved');
	} else {
		container.data('wph-editor-saved', false);
		if (!_container || (wph_editor.target && _container === wph_editor.target.container)) icon.addClass('wph_editor_unsaved');
	}

}

/*Loads data to editor*/
wph_editor.load_data_to_editor = function (target) {

	wph_editor.prev_target = $.extend({}, wph_editor.target);
	wph_editor.target = target;

	//feedback for save status
	wph_editor.set_save_status();

	//correct element name
	$('.wph_editor_body').attr('data-wph-elementname', target.element_name);

	//setup the right component labels to choose from
	var skipComponents = false;
	if (typeof wph_editor.prev_target !== "undefined" && typeof wph_editor.prev_target.components !== "undefined") {
		var prev_keys = [];
		$.each(wph_editor.prev_target.components, function (key, val) {
			prev_keys.push(key)
		})
		var keys = [];
		$.each(wph_editor.target.components, function (key, val) {
			keys.push(key)
		})
		if ($(keys).not(prev_keys).length == 0 && $(prev_keys).not(keys).length == 0) skipComponents = true;
	}

	if (!skipComponents) {
		var componentIndex = $('.wph_editor_componentIndex');
		$('.wph_editor_componentIndex').children().not(':first').remove();
		$.each(wph_editor.target.components, function (key,val){
			componentIndex.append('<span class="wph_editor_'+key+'" data-wph-type="'+key+'">'+key+'</span>');
		})
	}

	//some settings type should be selected. Default to style if nothing is already selected. Else go with first menu item
	var already_selected = wph_editor.ensure_selection ($('.wph_editor_settingsType'));
	if (already_selected) $('.wph_editor_settingsType').children('.wph_editor_selected').click();

}

/*Tells the difference between the two objects by returning an object of keys and vals present in main_obj but not present in subtract_obj*/
wph_editor.get_difference_in_objs = function (main_obj, subtract_obj) {
    var diff = {};
    $.each(main_obj, function (key,val){
        if(!(key in subtract_obj) || subtract_obj[key] !== val) diff[key]=val;
    })
	return diff;
}

/*View Update*/
// This method's responsibility is to attach the right data attrs to the main
// editor body based on the menu and sub menu options selected at this point
//	so the right view emerges via CSS
wph_editor.view_update = function () {
	//clean slate
	$('input.wph_editor_color_picker').spectrum('hide'); // else it influences next state
	$('input.wph_editor_color_picker').val(''); // else it influences next state

	//adding new view attrs
	$('.wph_editor_body>div').each(function () { // all div children of body are treated as sub menus
		var 	$this = $(this),
				$selected = $this.children('.wph_editor_selected');

		if (!$selected.length) return;
		var 	chosen = $selected.attr('data-wph-type'),// labels will be excluded
				optionType = $this.attr('data-wph-type');
		$('.wph_editor_body').attr('data-wph-'+optionType, chosen);
	})
};

/*Editor buttons init*/
wph_editor.init_once = function () {

	this.$wph_editor_body = $('.wph_editor_body');
	$wph_editor_body = this.$wph_editor_body;

	//blurring disabled by draggable
	$wph_editor_body.mousedown(function(){
	  document.activeElement.blur();
	})

	//return elements to idle state on mouseleave if
	$wph_editor_body.mouseleave(function () {
		$('.wph_editor_target_element').mouseleave();
	});

	//position undo trigger
	$wph_editor_body.on('mouseenter', '.wph_editor_undo_permitted', function () {
		var 	$this = $(this),
				offset = $this.offset(),
				left = offset.left,
				top = offset.top,
				panel = $('.wph_editor_componentStyles'),
				_offset = panel.offset(),
				_left = _offset.left,
				_top = _offset.top,
				prop = $this.siblings('label').attr('data-wph-property');
		$('.wph_editor_undo_setting').css({
			'left' : left - _left,
			'top' : top - _top,
			'display': 'inline-block'
		}).data('wph-target-prop', prop);
	}).on('mouseleave', '.wph_editor_undo_permitted, .wph_editor_undo_setting ', function (e) {
		if ($(e.toElement).hasClass('wph_editor_undo_setting')) return;
		$('.wph_editor_undo_setting').css({
			'display': 'none'
		})
	}).on('click', '.wph_editor_undo_setting', function () {
		var 	_ = $(this),
				prop = _.data('wph-target-prop'),
				state = $('.wph_editor_body').attr('data-wph-componentstate'),
				components = wph_editor.target.current.component;
		wph_editor.undo_state_prop(prop, state, components);
		_.hide();
		$('.wph_editor_componentState [data-wph-type="'+state+'"]').click();
	})

	// undo a state property
	wph_editor.undo_state_prop = function ( prop, state, components ){
		// delete the current state property
		prop_ = wph_editor.reverse_camel_case( prop );
		components.each( function ( ){
			var 	_ = $( this ),
					current_state = _.data( 'wph-current-state' );
			delete current_state[ prop_ ];
			_.data( 'wph-current-state', current_state );
		})
		// set the new styles
		var style_obj = { };
		style_obj[ prop ] = "";
		wph_editor.set_state_styles( style_obj, components, state );

	}

	//set elements to currently being edited state on mouseenter
	$wph_editor_body.mouseenter(function () {
		if ($('.wph_editor_componentState').is(':visible')) {
			var state = $('.wph_editor_componentState').children('.wph_editor_selected').attr('data-wph-type');
			if( wph_editor.get_plugin()['set_state'] ) wph_editor.get_plugin()['set_state']($('.wph_editor_target_element'), state);
		}
	});

	//hide editor keys while styling
	/*
	$('.wph_editor_body').on('mouseenter', '.wph_editor_componentStyles, .sp-container', function () {
		$('.wph_keys_center').hide();
	}).on('mouseleave', '.wph_editor_componentStyles, .sp-container', function () {
		$('.wph_keys_center').show();
	})
	*/

	/*Selects menu options and attempt to run main function upon click*/
	$('.wph_editor_body').on('click', '>div>span', function () {
		var $this = $(this);
		//return if parent container does not permit selection
		if ($this.parent().hasClass('wph_editor_children_non_selectable')) return;
		//also return if selection is label for the menu
		if (!$this.attr('data-wph-type')) return;
		//else
		$this.addClass('wph_editor_selected').siblings().removeClass('wph_editor_selected');
		wph_editor.view_update(); // updates the view
		wph_editor.run(false, $this.parent().attr('data-wph-type')+" "+$this.attr('data-wph-type')); // attempts to run main function
	});

	/*Component Style Index*/ //needs to be made CSS based
	$('.wph_editor_componentStyleIndex').on('click', 'span', function () {
		$target = $(this).attr("class");
		$('.wph_editor_componentStyles').children('.'+$target).show().siblings().hide();
	})

	/*Live changes*/
	/*--main*/
	$('.wph_editor_liveChanges')
		.on('change', 'input, select', wph_editor.live_changes_handler)
		.on('keyup', 'textarea', wph_editor.live_changes_handler)
		.on('click', '.wph_editor_clickable', wph_editor.live_changes_handler);

	/*--color picker (spectrum)*/
	$('.wph_editor_color_picker').spectrum({
		appendTo: $('.wph_editor_body'),
		color: "rgb(221, 221, 221)",
		allowEmpty:true,
		preferredFormat: "rgb",
		showInput:true,
		showAlpha: true,
		showSelectionPalette: true,
		change: wph_editor.live_color_picking_changed,
		hide: wph_editor.live_color_picking_hidden,
		move: wph_editor.live_color_picking_moving,
		beforeShow: wph_editor.live_color_picking_before_show
	})

	/*Window Ops*/
	/*--save*/
	$('.wph_editor_body').on('click', '.wph_editor_save', wph_editor.save);
	$('#titlewrap input[name="post_title"]').on('change', wph_editor.save_title);

	/*--visibility*/
	$('.wph_editor_visibility').on('click', function( ){
		$('body').toggleClass('wph_editor_visibility_low')
	})

	/*Compose/Decompose properties*/
	$('.wph_editor_body').on('change', '[data-wph-compose]+input, [data-wph-compose]+select', wph_editor.compose_propety);
	$('.wph_editor_body').on('change', '[data-wph-decompose]+input, [data-wph-decompose]+select', wph_editor.decompose_propety);

	/*--select another element*/
	/*-- --next element to the right*/
	Mousetrap.bind('mod+alt+right', function(e) {
		wph_editor.kb_select_element (e, 'right');
		return false;

	});

	/*-- --next element to the left*/
	Mousetrap.bind('mod+alt+left', function(e) {
		wph_editor.kb_select_element (e, 'left');
		return false;

	});

	/*-- --next element below*/
	Mousetrap.bind('mod+alt+down', function(e) {
		wph_editor.kb_select_element (e, 'down');
		return false;

	});

	/*-- --next element above*/
	Mousetrap.bind('mod+alt+up', function(e) {
		wph_editor.kb_select_element (e, 'up');
		return false;

	});

	/*--select another component*/
	/*-- --next component*/
	Mousetrap.bind('tab', function(e) {
		wph_editor.kb_select_component (e, 'next');
		return false;

	})

	/*-- --prev component*/
	Mousetrap.bind('shift+tab', function(e) {
		wph_editor.kb_select_component (e, 'prev');
		return false;

	})

}

/*Save*/
wph_editor.save = function () {
	var 	elm = wph_editor.target.parents,
			plugin = wph_editor.target.plugin;
	//run the ajax based 'save' function hooked in by the appropriate plugin
	if (elm.hasClass('wph_container'))
		container = elm;
	else
		container = elm.closest('.wph_container');

	if (container.attr('data-wph-plugin') === plugin) {
		container.find('.wph_editor_target_element').mouseleave();
		wph_editor.do_action('save', container);
	}

}

/*Save Default*/
wph_editor.default_save = function( elm, plugin, meta_key ){

	if( wph_editor.target.plugin !== plugin ) return;

	var html = elm.clone( true, true );

	// clean out keys
	html.find( '.wph_keys_center' ).remove( );
	html.find( '.wph_editor_target_element, .wph_editor_target_pseudo_row' ).removeClass( 'wph_editor_target_element wph_editor_target_pseudo_row' );

	// ajax request
	$.ajax( {
		type : "post",
		dataType : "json",
		url : wph_ajax.url,
		beforeSend: function ( ){
			wph_editor.start_loader( );
		},
		data : { "action": "wph_default_save", "nonce": wph_ajax.nonce, "html": html[ 0 ].outerHTML, "post-id": elm.attr( 'data-wph-post-id' ) },
		success: function (response) {
			if ( response && response.result ==="success" ) {
				wph_editor.stop_loader( );
			}else{
				alert( response.message );
			};
		}

	} );

}

/*Save Title*/
wph_editor.save_title = function () {
	var 	title = $('#titlewrap input[name="post_title"]').val().trim(),
			container = $('.wph_container'),
			id = container.length ? container.attr('data-wph-post-id') : false;
	if (!title || !id) return;

	if( ! wph_editor.target ) wph_editor.target = {};
	wph_editor.target.container = container;

   $.ajax({
		type : "post",
		dataType : "json",
		url : wph_ajax.url,
		data : {"action":"wph_save_title", "nonce":wph_ajax.nonce, "title":title, "post-id":id},
		beforeSend: function () {wph_editor.start_loader()},
		complete: function () {wph_editor.stop_loader()}
	});
}

/*Start Loader*/
wph_editor.start_loader = function (elm, callback) {
	if (!elm) $('.wph_editor_save').removeClass('fa-save').addClass('fa-spin fa-refresh');
	else {
		if (!elm.children('i').length) elm.prepend('<i class="fa fa-spin fa-refresh" style="width:0; position: relative; left: -5px; font-size: 12px; overflow:hidden; display:inline-block;"></i>')
		elm.children('i').stop().animate({'margin-right':5, 'opacity':1, 'width':'.85em', 'font-size':'1em'});
		if (callback) callback();
	}
}

/*Stop Loader*/
wph_editor.stop_loader = function (elm, callback) {
	if (!elm) {
		$('.wph_editor_save').addClass('fa-save').removeClass('fa-spin fa-refresh');
		wph_editor.set_save_status(true);
	} else {
		var i = elm.children('i');
		i.stop().animate({'margin-right':0, 'opacity':0, 'width':0}, function () {
			i.remove();
		});
		if (callback) callback();
	}
}

/*Live Changes Event Handler*/
wph_editor.live_changes_handler = function (e) {
	var 	data = {},
			$this = $(this),
			execution = $this.closest('[data-wph-execution]').attr('data-wph-execution') || "live_changes_plugin_execution";//container attribute

	data.target = $this;
	data.elms = wph_editor.target.parents;
	data.container = wph_editor.target.container,
	data.component = wph_editor.target.current.component, // component designated earlier in the loop
	data.component_name = wph_editor.target.current.component_name,
	data.label = $this.siblings('label'),
	data.prop = data.label.attr("data-wph-property"),
	data.prepend = data.label.attr("data-wph-prepend") || "",
	data.append = data.label.attr("data-wph-append") || "",
	data.val = data.prepend+$this.val()+data.append,
	data.attr_label = $this.closest('[data-wph-attr-label]').attr('data-wph-attr-label');
	data.default_attr_label = $this.closest('[data-wph-default-attr-label]').attr('data-wph-default-attr-label');

	wph_editor.set_save_status(false); // gives vis feedback of unsaved status

	wph_editor[execution](data); // call relevant executor
}

/*Live Changes Executions*/
// the execution relies on the execution data attr of a parent container.
/*--default*/
wph_editor.live_changes_plugin_execution = function (data) { // writes a JSON attr on targeted component
	wph_editor.do_action('live_changes_plugin_execution', data);

}

/*--overall*/
wph_editor.live_changes_overall_execution = function (data) { // writes a JSON attr on targeted container and runs the hooked in init fn of the plugin
	var obj = data.container.attr('data-wph-'+data.attr_label) ? JSON.parse(data.container.attr('data-wph-'+data.attr_label)) : {};
	/*
	if( data.default_attr_label ){
		var 	default_obj_json = data.container.attr( 'data-wph-' + data.default_attr_label ),
				default_obj = default_obj_json ? JSON.parse( default_obj_json ) : { };
		obj = $.extend( { }, default_obj, obj );
	}
	*/
	obj[ data.prop ] = data.val;
	data.container.attr('data-wph-'+data.attr_label, JSON.stringify(obj));
	wph_editor.get_plugin().init(data.container);

}

/*--style*/
wph_editor.live_changes_style_execution = function (data) {
	// no changes
	if (data.label.hasClass('wph_editor_no_live_change')) return;
	// special cases
	if (data.label.attr('data-wph-property') === "opacity") data.val = parseInt(data.val)/100;// opacity
	// live change style
	$(data.component).css(data.prop,data.val);
	wph_editor.do_action('live_changed_style', data);
	// set styles for the state
	var style_obj = {};
	style_obj[data.prop] = data.val;
	wph_editor.set_state_styles(style_obj);
	// permission to undo

	if (!data.target.hasClass('wph_editor_color_picker'))
		data.target.addClass('wph_editor_undo_permitted');
	else
		data.target.next().addClass('wph_editor_undo_permitted');

}

/*--type*/
wph_editor.live_changes_type_execution = function (data) {
	data.type = data.target.attr('data-wph-type');
	wph_editor.do_action('change_type', data);

}

/*--icon*/
wph_editor.live_changes_icon_execution = function (data) {
	data.target.closest('.wph_editor_clickable_parent').find('.wph_editor_selected').removeClass('wph_editor_selected')
	data.target.addClass('wph_editor_selected');

	var 	icon = data.target,
			icon_class = icon.attr('class').replace('wph_editor_selected', '').replace('wph_editor_clickable', '').trim(),
			prev_class = data.component.attr('data-wph-icon');

	data.component.attr('data-wph-icon', icon_class).removeClass(prev_class).addClass(icon_class);

}

/*--content*/
wph_editor.live_changes_content_execution = function (data) {
	if (data.component_name === "Link") data.component.attr('href', (data.target.val()));
	else data.component.html(data.target.val());

}

/*--content*/
wph_editor.live_changes_content_execution = function (data) {
	if (data.component_name === "Link") data.component.attr('href', (data.target.val()));
	else data.component.html(data.target.val());

}

/*-- --wp editor*/
wph_editor.live_changes_wp_editor_execution = function (data) {
	console.log( 'dummy wp editor content feed event' );
	var content = tinyMCE.activeEditor.getContent();

	data.component.html( content );

}

/*--image*/
wph_editor.live_changes_image_execution = function (data) {
	var val = data.target.val();
	if (!val || !val.trim() || !(/(jpg|gif|png|JPG|GIF|PNG|JPEG|jpeg)$/.test(val))) {
		return;

	}
	data.component.attr('src', val);
	var plugin = wph_editor.get_plugin();
	if (typeof plugin['image_replaced_callback'] === "function") plugin['image_replaced_callback'](data.component);

}

/*Compose Properties*/
wph_editor.compose_propety = function () {
	var 	$this = $(this),
			composer = wph_editor.compose_propety_structure;
	composer[$this.prev().attr('data-wph-compose')](true);

}

wph_editor.decompose_propety = function () {
	var 	$this = $(this),
			composer = wph_editor.compose_propety_structure;
	composer[$this.prev().attr('data-wph-decompose')](false);

}

wph_editor.compose_propety_structure = {
	"boxShadow" : function (compose) {
		if (compose) {
			var	shadow_color = $('[data-wph-property="shadowColor"]+input').val() || 'transparent',
					shadow_x = $('[data-wph-property="shadowOffsetX"]+input').val() || 0,
					shadow_y = $('[data-wph-property="shadowOffsetY"]+input').val() || 0,
					shadow_blur = $('[data-wph-property="shadowBlur"]+input').val() || 0,
					shadow_radius = $('[data-wph-property="shadowRadius"]+input').val() || 0,
					shadow_inset = $('[data-wph-property="shadowInset"]+select').val(),
					shadow_inset = shadow_inset === "yes" ? "inset" : "",
					shadow = shadow_color+' '+shadow_x+'px '+shadow_y+'px '+shadow_blur+'px '+shadow_radius+'px '+shadow_inset;
			$('[data-wph-property="boxShadow"]+input').val(shadow).change();
		} else { // decompose
			var 	shadow = $('[data-wph-property="boxShadow"]+input').val().trim().split("px"),
					empty = shadow.length < 3 ? true : false,
					shadow_color_and_x = empty? "" : shadow[0],
					shadow_color = empty? 'transparent' : shadow_color_and_x.substr(0, shadow_color_and_x.lastIndexOf(" ")),
					shadow_x = empty? 0 : parseInt(shadow_color_and_x.substr(shadow_color_and_x.lastIndexOf(" "), shadow_color_and_x.length)),
					shadow_y = empty? 0 : parseInt(shadow[1]),
					shadow_blur = empty? 0 : parseInt(shadow[2]),
					shadow_radius = empty? 0 : parseInt(shadow[3] ? shadow[3] : 0);
					shadow_inset = empty || !shadow[4] || shadow[4].trim() !=="inset" ? "no" : "yes";
			$('[data-wph-property="shadowColor"]+input').val(shadow_color).spectrum('set', shadow_color);
			$('[data-wph-property="shadowOffsetX"]+input').val(shadow_x).next().slider('value', shadow_x);
			$('[data-wph-property="shadowOffsetY"]+input').val(shadow_y).next().slider('value', shadow_y);
			$('[data-wph-property="shadowBlur"]+input').val(shadow_blur).next().slider('value', shadow_blur);
			$('[data-wph-property="shadowRadius"]+input').val(shadow_radius).next().slider('value', shadow_radius);
			$('[data-wph-property="shadowInset"]+select').val(shadow_inset);
		}
	},
	"margin" : function (compose) {
		if (compose) {
			var 	top = $('[data-wph-property="marginTop"]+input').val() || 0,
					right = $('[data-wph-property="marginRight"]+input').val() || 0,
					bottom = $('[data-wph-property="marginBottom"]+input').val() || 0,
					left = $('[data-wph-property="marginLeft"]+input').val() || 0,
					margin = top+'px '+right+'px '+bottom+'px '+left+'px';
			$('[data-wph-property="margin"]+input').val(margin).change();
		} else { // decompose
			var 	margin = $('[data-wph-property="margin"]+input').val().trim().split("px"),
					top, right, bottom, left,
					length = margin.length - 1; // last elm will be ""
			if (length > 4) length = 4;
			if (!length) top = right = left = bottom = 0;
			if (length === 1) top = right = left = bottom = margin[0];
			if (length === 2) {
				top = bottom = margin[0];
				right = left = margin[1];
			}
			if (length === 3) {
				top = margin[0];
				right = margin[1];
				bottom = margin[2];
				left = margin[1];
			}
			if (length === 4) {
				top = margin[0];
				right = margin[1];
				bottom = margin[2];
				left = margin[3];
			}
			top = parseInt(top);
			right = parseInt(right);
			bottom = parseInt(bottom);
			left = parseInt(left);
			$('[data-wph-property="marginTop"]+input').val(top).next().slider('value', top);
			$('[data-wph-property="marginRight"]+input').val(right).next().slider('value', right);
			$('[data-wph-property="marginBottom"]+input').val(bottom).next().slider('value', bottom);
			$('[data-wph-property="marginLeft"]+input').val(left).next().slider('value', left);
		}
	},
	"padding" : function (compose) {
		if (compose) {
			var 	top = $('[data-wph-property="paddingTop"]+input').val() || 0,
					right = $('[data-wph-property="paddingRight"]+input').val() || 0,
					bottom = $('[data-wph-property="paddingBottom"]+input').val() || 0,
					left = $('[data-wph-property="paddingLeft"]+input').val() || 0,
					padding = top+'px '+right+'px '+bottom+'px '+left+'px';
			$('[data-wph-property="padding"]+input').val(padding).change();
		} else { // decompose
			var 	padding = $('[data-wph-property="padding"]+input').val().trim().split("px"),
					top, right, bottom, left,
					length = padding.length;
			if (length > 4) length = 4 ;
			if (!length) top = right = left = bottom = 0;
			if (length === 1) top = right = left = bottom = padding[0];
			if (length === 2) {
				top = bottom = padding[0];
				right = left = padding[1];
			}
			if (length === 3) {
				top = padding[0];
				right = padding[1];
				bottom = padding[2];
				left = padding[1];
			}
			if (length === 4) {
				top = padding[0];
				right = padding[1];
				bottom = padding[2];
				left = padding[3];
			}
			top = parseInt(top);
			right = parseInt(right);
			bottom = parseInt(bottom);
			left = parseInt(left);
			$('[data-wph-property="paddingTop"]+input').val(top).next().slider('value', parseInt(top));
			$('[data-wph-property="paddingRight"]+input').val(right).next().slider('value', parseInt(right));
			$('[data-wph-property="paddingBottom"]+input').val(bottom).next().slider('value', parseInt(bottom));
			$('[data-wph-property="paddingLeft"]+input').val(left).next().slider('value', parseInt(left));
		}
	},
	"border": function (compose) {
		if (compose) {
			var 	top = $('[data-wph-property="borderTopWidth"]+input').val() || 0,
					right = $('[data-wph-property="borderRightWidth"]+input').val() || 0,
					bottom = $('[data-wph-property="borderBottomWidth"]+input').val() || 0,
					left = $('[data-wph-property="borderLeftWidth"]+input').val() || 0,
					style = $('[data-wph-property="borderStyle"]+input').val() || "solid",
					color = $('[data-wph-property="borderColor"]+input').val() || 'transparent',
					border = (top == right && right === bottom && bottom === left) ? top+'px '+style+' '+color : "";
			$('[data-wph-property="border"]+input').val(border);
			if (border)	$('[data-wph-property="border"]+input').change();
		} else {
			var 	border = $('[data-wph-property="border"]+input').val();
			if (!border) return;
			//width
			var 	split = border.split("px"),
					width = split[0];
			$('[data-wph-property="borderTopWidth"]+input').val(width).next().slider('value', parseInt(width));
			$('[data-wph-property="borderRightWidth"]+input').val(width).next().slider('value', parseInt(width));
			$('[data-wph-property="borderBottomWidth"]+input').val(width).next().slider('value', parseInt(width));
			$('[data-wph-property="borderLeftWidth"]+input').val(width).next().slider('value', parseInt(width));
			//style
			var style = split[1].trim().split(" ")[0];
			$('[data-wph-property="borderStyle"]+input').val(style)
			//color
			var color = split[1].replace(style+" ", "");
			$('[data-wph-property="borderColor"]+input').val(color).spectrum('set', color);
		}
	},
	"borderRadius": function (compose) {
		if (compose) {
			var 	topLeft = $('[data-wph-property="borderTopLeftRadius"]+input').val() || 0,
					topRight = $('[data-wph-property="borderTopRightRadius"]+input').val() || 0,
					bottomLeft = $('[data-wph-property="borderBottomLeftRadius"]+input').val() || 0,
					bottomRight = $('[data-wph-property="borderBottomRightRadius"]+input').val() || 0,
					borderRadius = topLeft+'px '+topRight+'px '+bottomRight+'px '+bottomLeft+'px ';
			$('[data-wph-property="borderRadius"]+input').val(borderRadius).change();
		} else { // decompose
			var 	borderRadius = $('[data-wph-property="borderRadius"]+input').val().split("px"),
					topLeft, topRight, bottomRight, bottomLeft,
					length = borderRadius.length;
			if (length > 4) length = 4 ;
			if (!length) topLeft = topRight = bottomLeft = bottomRight = 0;
			if (length === 1) topLeft = topRight = bottomLeft = bottomRight = borderRadius[0];
			if (length === 2) {
				topLeft = bottomRight = borderRadius[0];
				topRight = bottomLeft = borderRadius[1];
			}
			if (length === 3) {
				topLeft = borderRadius[0];
				topRight = borderRadius[1];
				bottomRight = borderRadius[2];
				bottomLeft = borderRadius[1];
			}
			if (length === 4) {
				topLeft = borderRadius[0];
				topRight = borderRadius[1];
				bottomRight = borderRadius[2];
				bottomLeft = borderRadius[3];
			}
			$('[data-wph-property="borderRadiusTopLeft"]+input').val(topLeft).next().slider('value', parseInt(topLeft));
			$('[data-wph-property="borderRadiusToptopRight"]+input').val(topRight).next().slider('value', parseInt(topRight));
			$('[data-wph-property="borderRadiusBottomRight"]+input').val(bottomRight).next().slider('value', parseInt(bottomRight));
			$('[data-wph-property="borderRadiusBottomLeft"]+input').val(bottomLeft).next().slider('value', parseInt(bottomLeft));
		}
	}

};

// open frame
if (typeof wp !== "undefined" && typeof wph_media_frame === "undefined") var wph_media_frame = wp.media({multiple: false, frame: 'post'}); // 'post' brings up image size options
$('.wph_editor_imageSelectorButton').click(function () {
	wph_media_frame.wph_target = $(this).prev('input');
	wph_media_frame.wph_target_size = 'full';
	wph_media_frame.open();

	// getting the user set size for the image
	wph_media_frame.$el.off( 'change', 'select.size' );
	wph_media_frame.$el.on( 'change', 'select.size', function wph_media_frame_select_size( ){
		wph_media_frame.wph_target_size = $( this ).val( );
	} )

})

// insert an image
wph_media_frame.on('insert', function() {
	$( 'select.size' ).off( 'change', 'wph_media_frame_size_change' );

	var 	selected = wph_media_frame.state( ).get( 'selection' ).first().toJSON(),
			url = selected.sizes[ wph_media_frame.wph_target_size ];
	wph_media_frame.wph_target.val( url.url ).change( );
});

/*Reverse Camel Case*/
wph_editor.reverse_camel_case = function (string) {
	return string.split(/(?=[A-Z])/).join('-').toLowerCase();
}

/*Color Picker Helper*/
/*--before show*/
wph_editor.live_color_picking_before_show = function (color) {
	var 	target = wph_editor.target.current.component,
			val = color.toRgbString(),
			prop = wph_editor.reverse_camel_case($(this).siblings('label').attr('data-wph-property')),
			inline_styles = wph_editor.get_inline_styles(target),
			no_inline_color = !inline_styles[prop] ? "true" : "false";
	$(this).attr('data-wph-no-inline-color', no_inline_color); // flag for whether to return to empty style upon cancel
};

/*--moving*/
wph_editor.live_color_picking_moving = function (color) {
	var 	target = wph_editor.target.current.component,
			val = color ? color.toRgbString() : "",
			prop = $(this).siblings('label').attr('data-wph-property');
	$(target).css(prop,val);
};

/*--changed*/
wph_editor.live_color_picking_changed = function (color) {
	var 	target = wph_editor.target.current.component,
			val = color ? color.toRgbString() : "",
			prop = $(this).siblings('label').attr('data-wph-property');
	$(this).val(val);
	$(this).attr('data-wph-no-inline-color', "false");
	// the prop-val also need to be written on the component's state attr
	var style_obj = {};
	style_obj[prop] = val;
	wph_editor.set_state_styles(style_obj);
};

/*--hidden*/
wph_editor.live_color_picking_hidden = function (color) {
	var 	target = wph_editor.target.current.component,
			val = color ? color.toRgbString() : "",
			prop = wph_editor.reverse_camel_case($(this).siblings('label').attr('data-wph-property'));
	if ($(this).attr('data-wph-no-inline-color') !== "true") target.css(prop, val);
	else target.css(prop, '')
};

/*Set Component State Styles*/
wph_editor.set_state_styles = function (style_obj, component, state) {
	var 	plugin = wph_editor.get_plugin();

	if (!component) component = wph_editor.target.current.component;
	if (!state) state = $('.wph_editor_componentState .wph_editor_selected').attr('data-wph-type');

	//reverse camel case
	var 	new_style_obj = {},
			un_camelcased_prop = "",
			undo = false;
	$.each(style_obj, function (key, value) {
		if (!value) undo = true;
		un_camelcased_prop = wph_editor.reverse_camel_case(key);
		new_style_obj[un_camelcased_prop] = value;
	})
	style_obj = new_style_obj;

	if (plugin['set_state_styles']) {
		var returned = plugin['set_state_styles'](style_obj, component, state);
		if (returned) {
			style_obj = returned[0];
			component = returned[1];
			state = returned[2];
		} else {
			return;
		}
	}

	// covert state to array of states. Could be used by plugin
	if (typeof state === "string") state = [state];

	//set the attr containing state style information
	component.each(function () {
		var 	$this = $(this);
		$.each(state, function (i, state) {
			var	current_style_string = $this.attr('data-wph-'+state),
					current_style = {},
					new_style = {};

			// get current style obj
			if (current_style_string) current_style = JSON.parse(current_style_string);
			else current_style = {};
			// apply style prop / remove style prop
			if (undo) {
				delete current_style[un_camelcased_prop];
				new_style = current_style;
				$this.css(un_camelcased_prop, '');
			} else {
				new_style = $.extend({}, current_style, style_obj);
			}
			// re-write state string
			if (!$.isEmptyObject(new_style)) $this.attr('data-wph-'+state, JSON.stringify(new_style));
			else $this.removeAttr('data-wph-'+state);
		})
	})

}

/*Hook in functions and trigger them via the settings menu buttons data-wph-type attr*/
wph_editor.runnable = {};// hook in fns here
wph_editor.running = {};// record of which fns are running to prevent endless recursion
wph_editor.run = function (label, id) {// use this to call the hooked in fns
	if (!label) label = $('.wph_editor_settingsType>span.wph_editor_selected').attr('data-wph-type');
	if (!id) id = "unknown";

	if (!wph_editor.runnable[label]) {
		return false;
	}

	if (!wph_editor.running[label]) {
		wph_editor.running[label] = true;
		wph_editor.runnable[label]();
		wph_editor.running[label] = false;
		return true;
	}
};

/*--style*/
wph_editor.runnable['Style'] = function () {
	//ensure requisite sub menu ops are selected
	$('.wph_editor_componentIndex, .wph_editor_componentState, .wph_editor_componentStyleIndex').each(function () {
		wph_editor.ensure_selection($(this));
	})

	var	component = wph_editor.update_component();
	if( component ) component = component.last( ); // important to select the last because active mode is auto triggered only upon the last element.
	else return;
	var 	state = wph_editor.target.current.state = $('.wph_editor_componentState>.wph_editor_selected').attr('data-wph-type').toLowerCase(),
			plugin = wph_editor.target.plugin,
			elm = wph_editor.target.parents;

	//action hook
	wph_editor.do_action('runnable_style_cleanup');
	wph_editor.do_action('runnable_style');

	//set the state on the element
	wph_editor.plugins[plugin]['set_state'](elm, state);

	var 	base_styles = component.getStyles(),
			state_styles = component.attr('data-wph-'+state),
			modifications = component.data('wph-current-state') || {},//stores the styles the element will have post .animation()
			modifications_fixed = {};
	//inheritance
	var 	parents = component.parentsUntil('.wph_cell'),
			inheritable = ['font-size', 'line-height', 'color', 'font-family', 'font-weight', 'text-align', 'text-decoration', 'font-style', 'text-transform'],
			inheritence = {};

	if (parents.length) {
		parents = Array.prototype.reverse.call(parents);
		parents.each(function () {
			var current_state = $(this).data('wph-current-state') || {};
			$.each(inheritable, function (i, prop) {
				if (current_state[prop]) inheritence[prop] = current_state[prop];
			})
		})
	}

	modifications = $.extend({}, inheritence, modifications);

	if (state_styles && state_styles.length > 5) {
		state_styles = JSON.parse(state_styles);
	} else {
		state_styles = {}
	}

	//camelcase keys
	$.each(modifications, function (key, val) {
		var fixed_key = key.toLowerCase().replace(/-(.)/g, function(match, group1) {return group1.toUpperCase();});
		modifications_fixed[fixed_key]=val;
	})
	modifications = modifications_fixed;

	var styles = $.extend({}, base_styles, modifications);

	// fresh slate regarding undo permission
	$('.wph_editor_componentStyles .wph_editor_undo_permitted').removeClass('wph_editor_undo_permitted');
	//set styles
	$('.wph_editor_componentStyles [data-wph-decompose]+input').val(''); // else values may not clear if the key is not available in styles
	$.each(styles, function(key,val){
		var 	target = $('.wph_editor_componentStyles [data-wph-property="'+key+'"]'), // this is a label
				prepend = target.attr('data-wph-prepend'),
				append = target.attr('data-wph-append')
		if (val) {
			if (prepend) val = val.slice(prepend.length);
			if (append) val = val.slice(0, '-'+append.length); //negative
		}
		if (key.indexOf("olor") !== -1) {
			target.next().spectrum("set", val);
		//handling opacity values
		} else if (key.indexOf("opacity") !== -1) {
			val = val*100
		}
		//finally give value to input field
		target.next().val(val).addClass(undo_permission);

		// give value to slider
		if (target.next().next().hasClass('wph_editor_slider')) target.next().next().slider("option", "value", val);

		// undo permission
		var undo_permission = (typeof state_styles[wph_editor.reverse_camel_case(key)]  === "undefined") ? "" : "wph_editor_undo_permitted";
		if (target.next().hasClass('wph_editor_color_picker')) target.next().next().addClass(undo_permission);
		else target.next().addClass(undo_permission);

	})

	// decompose vals where available because their decomposed vals may not be
	$('.wph_editor_componentStyles [data-wph-decompose]+input').each(function () {
		var $this = $(this);
		if ($this.val()) wph_editor.decompose_propety.call($this);
	})
}

/*--overall*/
wph_editor.runnable['Overall'] = function () {
	var 	container = wph_editor.target.container,
			// the correct target panel becomes visible due to the view set up by css
			target_editor_panel = $('.wph_editor_body>div[data-wph-attr-label]:visible'), // ### identify better
			attr_label = target_editor_panel.attr( 'data-wph-attr-label' ),
			settings = container.attr('data-wph-'+attr_label) ? JSON.parse(container.attr('data-wph-'+attr_label)) : {},
			default_attr = target_editor_panel.attr( 'data-wph-default-attr-label' );

	// the settings object may be extended by a default settings object
	if( default_attr ){
		var 	default_json_obj = container.attr( 'data-wph-' + default_attr ),
				default_settings = default_json_obj ? JSON.parse( default_json_obj ) : { },
				settings_extended = $.extend( { }, default_settings, settings );
	}else{
		var settings_extended = $.extend( {}, settings );
	}

	//run hooked-in plugin function
	var returned = wph_editor.after_view_setup('Overall');
	if (returned === "stop") return;

	//set styles
	$.each(settings_extended, function(key,val){
		//handling px values
		if (typeof val === "string" && val.length > 2 && val.substring(val.length-2, val.length) === "px") {
			val = parseInt(val.substring(0, val.length-2));
		}
		target_editor_panel.find('[data-wph-property="'+key+'"]').next().next().slider("option", "value", val);
		//finally give value to input field
		target_editor_panel.find('[data-wph-property="'+key+'"]').next().val(val);
	})
}

/*--type*/
wph_editor.runnable['Type'] =	function () {
	var 	plugin = plugin = wph_editor.get_plugin(),
			type = plugin.get_type(wph_editor.target.parents),
			spans = $('.wph_editor_types:visible').children();
	spans.removeClass('wph_editor_selected');
	spans.filter('[data-wph-type="'+type+'"]').addClass('wph_editor_selected');

}

/*--content*/
wph_editor.runnable['Content'] =	function () {

	//ensure requisite sub menu ops are selected
	$('.wph_editor_componentIndex').each(function () {
		wph_editor.ensure_selection($(this));
	})

	var	component = wph_editor.update_component(),
			component_name = wph_editor.target.current.component_name,
			content_source = $( '.wph_editor_body ' ).attr( 'data-wph-contentsource' ),
			content = component.html(),
			plugin = wph_editor.get_plugin();

	//run hooked-in plugin function
	var returned = wph_editor.after_view_setup('Content');
	if (returned === "stop") return;

	//set content
	//--icon
	if ( component_name === "Icon" ) {
		//perfect scrollbar update
		$('.wph_editor_perfectScrollbar.wph_editor_fontAwesomeIndex').perfectScrollbar('update');

		var 		icon = component.attr('data-wph-icon');
		if( icon ){
					icon_selector = '.'+icon.split(' ').join('.'),
					icon_index = $('.wph_editor_fontAwesomeIndex:visible');
			if( icon_index.length ){
				icon_index.find('.wph_editor_selected').removeClass('wph_editor_selected');
				icon_index.find(icon_selector).addClass('wph_editor_selected');
			}
		}

	//--link
	} else if ( component_name === "Link" ) {
		var link = component.attr('href') || "";
		$('.wph_editor_contentTextarea').val(link);

	//--image
	} else if ( component_name === "Image" ) {
		var source = component.attr('src') || "";
		$('.wph_editor_imageSelectorInput').val(source);

	//--wp editor
	} else if ( component_name === "Details" && content_source === "WP Editor" ) {
		var wp_editor = tinyMCE.get( 'wph_editor_wp_editor' );

		if( wp_editor ) wp_editor.setContent( content );
		else $( '#wph_editor_wp_editor' ).val( content );

		if( wp_editor ){
			wp_editor.off( 'change' );
			wp_editor.on('change', function( e ){
				var content = wp_editor.getContent( );
				wph_editor.target.current.component.html( content );
			});
			wp_editor.off( 'keyup' );
			wp_editor.on('keyup', function( e ){
				var content = wp_editor.getContent( );
				wph_editor.target.current.component.html( content );
			});

		}
		/*
		else{
			$( '.wph_editor_body' ).off( 'keyup', '#wph_editor_wp_editor' );
			$( '.wph_editor_body' ).on( 'keyup', '#wph_editor_wp_editor', function( e ){
				var content = $( this ).val( );
				wph_editor.target.current.component.html(content);
				console.log('ping!')
			});
		}
		*/

	//--text / html
	} else {
		$('.wph_editor_contentTextarea').val(content);

	}
	// ensure a content source is selected in case of details
	if( component_name === "Details" ) wph_editor.ensure_selection( '.wph_editor_contentSource' );

}

/*--target*/
wph_editor.runnable['Target'] =	function () {
	wph_editor.do_action('runnable_target_cleanup');
	wph_editor.do_action('runnable_target');
}

/*--other*/
wph_editor.runnable['Other'] =	function () {
	wph_editor.do_action('runnable_other_cleanup');
	wph_editor.do_action('runnable_other');
}

/*Updates component information*/
wph_editor.update_component = function () {
	var component_name = $('.wph_editor_componentIndex').children('.wph_editor_selected').attr('data-wph-type');

	wph_editor.target.current.component = wph_editor.target.components[component_name];
	wph_editor.target.current.component_name = component_name;

	return wph_editor.target.current.component;
};

/*Ensures an item is selected from the group*/
wph_editor.ensure_selection = function (elm) {
	elm = $( elm );
	if (!elm.children('.wph_editor_selected:visible').length) elm.children('[data-wph-type]:visible').first().click();
	else return true;

}

/*Check if mouse is on editor and so its permitted to engage keyboard navigation*/
wph_editor.keyboard_navigation_permission = function () {
	return this.$wph_editor_body.is(':visible');

}

/*Check if user is working in the content area*/
wph_editor.focus_on_content = function () {
return this.$wph_editor_body.attr('data-wph-settingstype') === "Content";

}

/*Keyboard select component with Mousetrap*/
wph_editor.kb_select_component = function (e, direction) {
	var data = {e:e, direction:direction};
	wph_editor.do_action('kb_select_component_cleanup');
	wph_editor.do_action('kb_select_component', data);

}

/*Keyboard select element with Mousetrap*/
wph_editor.kb_select_element = function (e, direction) {
	var data = {e:e, direction:direction};
	wph_editor.do_action('kb_select_element_cleanup');
	wph_editor.do_action('kb_select_element', data);

}

/*Color theme*/
/*--extract colors*/
wph_editor.extract_color_theme = function () {
	var 	_ = this,
			$ = jQuery,
			$this = $(_),
			id = $this.attr('data-wph-for-post-id'),
			container = $('.wph_container[data-wph-post-id="'+id+'"]'),
			plugin = container.attr('data-wph-plugin'), // need plugin name to get components later
			states = ['idle', 'hover', 'active'],
			color_theme = {};

	container.find('[data-wph-element-name]').each(function () {
		var 	$this = $(this),
				components = wph_editor.plugins[plugin].get_components($this);

		$.each(components, function (key, val) { // cycle through components
			if (typeof val === "string") return; // exclude the name and such
			val.each(function () {
				var $component = $(this);
				$.each(states, function (i, v) { // cycle through states
					var 	state = 'data-wph-'+v,
							state_styles = $component.attr(state);
					if (state_styles && state_styles.length > 2) {
						state_styles = JSON.parse(state_styles);
						$.each(state_styles, function (k_, v_){ // cycle through props in a state
							if (wph_editor.is_color(v_)) { // proceed if the value is a valid color
								var 	color_theme_info = $component.data('wph-color-theme-info'), // the data prop we will be building
										color_theme_info = color_theme_info? color_theme_info : {};
								if (typeof color_theme_info[v] === "undefined") color_theme_info[v] = {};
								color_theme_info[v][k_] = v_;
								$component.data('wph-color-theme-info', color_theme_info);

								// add it to the color_theme object
								if (typeof color_theme[v_] === "undefined") color_theme[v_] = $component;
								else color_theme[v_] = color_theme[v_].add($component);
							}
						})
					}
				})
			})
		})
	})

	// build the color theme editor boxes
	$this.siblings('.wph_color_theme_color_picker, .sp-replacer').remove(); // clean slate
	$.each(color_theme, function (color, components) {
		var 	input = $('<input class="wph_color_theme_color_picker" value="'+color+'" data-wph-original="'+color+'"/>'),
				components = components;
		input.insertAfter($this).data('wph-target', components)
		input.spectrum({
			preferredFormat: "rgb",
			showInput:true,
			showAlpha: true,
			showSelectionPalette: true,
			move: wph_editor.apply_color_theme_color,
			change: wph_editor.apply_color_theme_color
		});
	})
	$this.siblings( '.sp-replacer' ).css( 'margin-left', 10 ).each(wph_editor.display_theme_color_picker_labels);
}

/*--displays the target labels in bubbles*/
wph_editor.display_theme_color_picker_labels = function ( ){
		var 	$this = $(this),
				input = $this.prev( 'input' ), // it is this input that actually holds the data
				label_strings = [ ],
				components = input.data( 'wph-target' ),
				plugin = false;

		components.each(function( ){
			var 	component = $(this),
					info = component.data( 'wph-color-theme-info' ),
					plugin = plugin ? plugin : component.closest( '.wph_container' ).attr( 'data-wph-plugin' ),
					state, prop;
			$.each( info, function( state, state_props ){ // iterate through states
				$.each( state_props, function( prop, val ){ // iterate through state props
					if( prop === 'color' ) prop = 'font color'; // renaming color for user convenience
					var label = 'Component: '+wph_editor.get_component_name( component, plugin )+', State: '+state+', Prop: '+prop // form new label component string
					if ( $.inArray( label, label_strings ) === -1 ) // add new string to the label array only if original
						label_strings.push( label );
				})
			})
		})
		// tooltip
		label_strings = label_strings.length ? label_strings.join( '<br>' ) : '';
		$this.tooltipster({
            content: $('<span>'+label_strings+'</span>'),
			theme: 'tooltipster-light'
		});
}

/*--gets component name*/
wph_editor.get_component_name = function( component, plugin ){
		var 	plugin = plugin ? plugin : component.closest( '.wph_container' ).attr( 'data-wph-plugin' ),
				parent_cell = component.closest( '.wph_cell' ), // we need the cell to use the get_components fn
				componets = wph_editor.plugins[plugin].get_components( parent_cell ),
				node = component.get( 0 ),
				name = '';

		$.each( componets, function( name_, $obj ){ // iterate over all components
			if( typeof $obj !== 'object' ) return true;
			if( node === $obj.get( 0 ) ) name = name_; // compare component against val as it contains the jQuery object

		})
		return name;
}

/*--apply a single color from the color theme*/
wph_editor.apply_color_theme_color = function (color) {
	var 	$this = $(this),
			components = $this.data('wph-target'),
			original = $this.attr('data-wph-original'),
			color = color.toRgbString();

	components.each(function () {
		var 	$component = $(this),
				data = $component.data('wph-color-theme-info'),
				current_state = $component.closest('.wph_cell').attr('data-wph-current-state'),
				current_state = typeof current_state !== 'undefined' ? current_state.toLowerCase() : '';

		if (!data) return;
		$.each(data, function (state, obj) {
			state = state.toLowerCase();
			$.each(obj, function (prop, val) {
				if (val !== original) return;
				var 	state_styles = $component.attr('data-wph-'+state);
				if (!state_styles || state_styles.length < 3) return;
				else state_styles = JSON.parse(state_styles);
				state_styles[prop] = color;
				state_styles = JSON.stringify(state_styles);
				$component.attr('data-wph-'+state, state_styles);
				if (current_state === state || (!current_state && state === "idle")) { // change the property value in the inline style as well if element is in that state
					var inline_styles = wph_editor.get_inline_styles($component);
					$.each(inline_styles, function (i_prop, i_val) {
						if (i_prop === prop) $component.css(prop, color);
					})
				}
			})
		})

	})
}

/*Check if string represents a color*/
wph_editor.is_color = function (string) {
	if (typeof string !== "string") return;
	var 	string = string.trim(),
			black = ["rgb(0, 0, 0)", "rgb(0, 0, 0, 1)", "black", "#000000", "#000"],
			not_permitted = ["solid", "px", "em"],
			required = ["rgb", "rgba", "#", "hsl"],
			stop = false,
			starting = string.slice(0,4),
			ending = string.substr(-4);

	// transparent
	if (starting === "rgba" && ending === ", 0)") return false;

	if (string === "") return false;
	// check for not permitted
	if ($.each(not_permitted, function (i,v) {
		if (string.indexOf(v) !== -1) stop = true;
	}))
	if (stop) return false;
	// check for required
	stop = true;
	if ($.each(required, function (i,v) {
		if (string.indexOf(v) !== -1) stop = false;
	}))
	if (stop) return false;
	// if it is black, return true
	if ($.inArray(string, black) !== -1) return true;
	var div = $('<div class="wph_check_if_color" style="background:rgb(0, 0, 0)"></div>');
	$('body').append(div);
	div.css('background-color', string);
	var color = div.css('background-color');
	div.remove();
	// if it was able to color a div away from black then it must be a valid color
	if ($.inArray(color, black) !== -1) return false;
	else return true;
}

/*Fill up preset select boxes*/
wph_editor.preset_select_boxes_init = function (map, fields, selections) {
	if (typeof map === "number" || typeof map === "undefined") return;
	var 	first = false,
			first_field = fields.first(),
			selections = selections,
			html = "";
	// create the html
	$.each(map, function (key, val) {
		if (!first) {
			first = key;
		}
		var cap = key.charAt(0).toUpperCase() + key.substring(1);
		html += '<option value="'+key+'">'+cap+'</option>';
	})
	// print or skip based on whether the selections array has run out
	if (selections === false) { // if first run
		first_field.html(html);
	}	if (Object.prototype.toString.call( selections ) === '[object Array]' && !selections.length) { // have to print on current as the selections array has run out
		first_field.html(html);
	}

	if (Object.prototype.toString.call( selections ) === '[object Array]' && selections.length){ // don't have to print on current
		first = selections.shift(); // reduce the selections array by one
	}

	// do the next select box
	fields = fields.not(first_field);
	if (fields.length) wph_editor.preset_select_boxes_init(map[first], fields, selections);

}

/*Preset boxes click behaviour init*/
wph_editor.preset_select_boxes_click_behaviour = function (map, fields) {
	var map = map;
	fields.click(function () {
		var 	$this = $(this);
				prev = $this.prevAll('select'),
				selections = [],
		prev.each(function () {
			selections.push($(this).val());
		})
		selections = Array.prototype.reverse.call(selections);
		selections.push($this.val());
		wph_editor.preset_select_boxes_init(map, fields, selections);
	})
}

/*After view setup*/
wph_editor.after_view_setup = function (running) {
	var plugin = wph_editor.get_plugin();
	if (typeof plugin.after_view_setup === "function") plugin.after_view_setup(running);

};

// init
wph_editor.init();

})
