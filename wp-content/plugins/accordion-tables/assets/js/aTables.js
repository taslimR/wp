/*wph_editor header*/
if (typeof wph_editor !== "object") {
	var wph_editor = {};

	/*Plugins hook in here*/
	wph_editor.plugins = {};
}

jQuery( function( $ ){

// centre current column's featured image
$( '.atw_image' ).find( 'img' ).aTablesCenterImage( 'inside' );

// set up variables
var $atw_el, $atw_parentWrap, $atw_otherWrap, atw_lock;

// slide and open on trigger click
$("body").on("click", ".atw_trigger", function() {

	// not supposed to trigger is details are not there in regular element
	if ($(this).hasClass('atw_title') && $(this).next('.atw_details').is(':empty')) return;

	$atw_el = $(this);
	$atw_parentWrap = $atw_el.closest('.atw_info_col_details');

	// lock if previous column isn't done expanding. Unlock if it's the same column
	if (atw_lock && atw_lock !== $atw_parentWrap) {
		return false;
	}
	atw_lock = false;

	//get settings
	var 		container = $atw_el.closest('.atw_container'),
				overall_settings = aTables_get_overall_settings( container ),
				width_as = overall_settings['columnWidthCalculatedAs'] ? overall_settings['columnWidthCalculatedAs'] : 'pixel',
				contract_width = overall_settings['columnWidthBeforeExpansion'] ? overall_settings['columnWidthBeforeExpansion'] : 150,
				expand_width = overall_settings['columnWidthAfterExpansion'] ? overall_settings['columnWidthAfterExpansion'] : 320,
				column_gap_horizontal = overall_settings['columnGapHorizontal'] ? overall_settings['columnGapHorizontal'] : 5,
				table_padding_horizontal = overall_settings['tablePaddingHorizontal'] ? overall_settings['tablePaddingHorizontal'] : 20,
				animation_speed = overall_settings['animationSpeed'] ? parseInt(overall_settings['animationSpeed']) : 400,
				sliding_speed = overall_settings['slidingSpeed'] ? parseInt(overall_settings['slidingSpeed']) : 400,
				expansion_type = overall_settings['expansionType'] ? overall_settings['expansionType'] : "push",
				curr_col = container.find( '.atw_curCol' );

	if( width_as === 'percentage' ){
		var available_width = container.closest( '.atw_outer_container' ).width( ) + parseInt( column_gap_horizontal ) - ( parseInt( table_padding_horizontal ) * 2 );

		contract_width = ( ( available_width * contract_width ) / 100 ) - column_gap_horizontal;
		expand_width = ( ( available_width * expand_width ) / 100 ) - column_gap_horizontal;
	}

	if( curr_col.length ){ // avoid situation where assumed expand width is greater than actual possible expand width
		expand_width = curr_col.width( );
	}

	// close all info cells
	container.find(".atw_details").stop().slideUp(sliding_speed, 'swing', function () {
		$(this).atwZindexManage('SlideUp');
	}).closest('.atw_cover').removeClass('atw_active');

	//if this clicked cell is not the currently open one
	if (!$atw_el.hasClass("atw_current")) {

		var 	$atw_otherWraps = container.find(".atw_info_col_details").not($atw_parentWrap),
				$atw_allTitles = container.find(".atw_trigger").not(this); // remove current cell from selection of all cells

		$atw_el.next().filter(function () {
			return !$(this).is(':empty');
		}).atwZindexManage('SlideDown').stop().slideDown(sliding_speed).closest('.atw_cover').addClass('atw_active');

		//set idle state for others' state
		container.find('.atw_current').each(function () {
			var 	$this = $(this),
					elm = $this.closest('.atw_cover');

			$this.removeClass('atw_current');
			elm.removeClass('atw_active');
			wph_editor.set_state (elm, "Idle");
		})

		var $prev = container.find(".atw_curCol").not($atw_parentWrap);

		// mark the current col
		$atw_parentWrap.addClass("atw_curCol");
		$prev.removeClass("atw_curCol");

		// adjust margins
		atw_adjust_margins (container, sliding_speed, expand_width, contract_width);

		// contract the prev expanded column
		atw_animate_col($prev, sliding_speed, contract_width, 'contract');

		// expand the current column
		atw_animate_col( $atw_parentWrap, sliding_speed, expand_width, 'expand' );

		// make sure the correct column is current
		$atw_el.addClass("atw_current");

	}else{ //close current col

		// unmark the current col
		$atw_el.removeClass( "atw_current" );
		$atw_parentWrap.removeClass( "atw_curCol" );

		// contract the current column
		atw_animate_col($atw_parentWrap, sliding_speed, contract_width, 'contract');

		// adjust margins
		atw_adjust_margins (container, sliding_speed, expand_width, contract_width);
	}

});

// handles the column width expansion and contraction
function atw_animate_col( col, speed, width, type ){

	var img = col.find( '.atw_image img' );
	function fn( ){
		col.css( {
			width: Math.floor( width ),
			"transition": 'width ' + ( speed / 1000 ) + 's'
		} );

		atw_lock = col;
		setTimeout( function( ){
			atw_lock = false;
		}, speed );

	}

	img.each(function () {
		if (img[0].complete) {
			img.aTablesCenterImage('inside', fn, width, speed)
		} else {
			fn();
			img.aTablesCenterImage('inside', false, width, speed)
		}
	})

	if (!img.length) fn();

}

// adjust margins so rows of cols maintain center alignment
function atw_adjust_margins( container, sliding_speed, expand_width, contract_width ){
	var 	atw_cur_col = container.find('.atw_curCol'),
				atw_parent_col = atw_cur_col.parent(),
				atw_col_margin_tinker = atw_parent_col.hasClass( 'wph_start_of_row' ) ? atw_parent_col : atw_parent_col.prevAll( '.wph_start_of_row' ).first( ),
				atw_col_margin_normalize = atw_cur_col.length ? atw_col_margin_tinker.siblings( '.wph_start_of_row' ) : container.children( '.wph_start_of_row' ),
				container_width = container.width( );
	//margin animations
	var atw_margin_transition = 'margin '+(sliding_speed/1000)+'s';
	atw_col_margin_tinker.css({
		"margin-left": 0,
		"transition": atw_margin_transition
	})

	atw_col_margin_normalize.each( function( ){
		var 	$this = $( this ),
				margin_left = parseInt( $this.css( 'margin-left' ) );

		if( expand_width <= container_width ){
			$this.css( {
				"margin-left": ( expand_width - contract_width ) / 2 + 'px',
				"transition": atw_margin_transition
			} )
		}
	} )
}

// manage column z index and height
$.fn.atwZindexManage = function(type) {
	var 	$this = $(this[0]),
			col = $this.closest('.atw_info_col');

	// if we are sliding down the details panel, its column should get zIndex
	if( type === "SlideDown" ){
		col.css( 'zIndex', 1 );
	// else its parent column should get zIndex removed and min-height nulled
	// column min-height is not set for 'overlap' type expansion
	}else{
		var 	height = col.css( 'min-height' ) !== '0px' ? col.css('min-height') : col.height(),
				child_height = col.children('.atw_info_col_details').height();
		if( height > child_height ) col.css( 'zIndex', 0 );
	}
	return $this;

}

// init
var containers = $( '.atw_container:visible' );
aTables_init( containers );

// invisible containers workaround
var aTables_invisible_containers = $( '.atw_container:hidden' );
if( aTables_invisible_containers.length ) aTables_invisible_fix( );

function aTables_invisible_fix( ){
	var $containers_still_left = $( '.atw_container' ).not( '.atw_init' );

	// end if there are no more containers left to init
	if( ! $containers_still_left.length )
		return;

	// init the containers that are visible now and not yet init
	var $ready_containers = $( '.atw_container:visible' ).not( '.atw_init' );
	if( $ready_containers.length ){
		aTables_init( $ready_containers );
	}

	// set timeout if there are more hidden uninit containers left
	if( $( '.atw_container:hidden' ).not( '.atw_init' ).length )
		setTimeout( aTables_invisible_fix, 200 );
}

function aTables_get_overall_settings( container ){

	var doc_width = $(window).width();
	aTables_device_group = "pc";

	if (doc_width < 1100) {
		aTables_device_group = "tablet";
	}
	if (doc_width < 750) {
		aTables_device_group = "mobile";
	}

  var pc_overall_settings_json = container.attr( 'data-wph-overall-settings' );
	pc_overall_settings = (pc_overall_settings_json && pc_overall_settings_json.length > 3) ? JSON.parse( pc_overall_settings_json ) : {};

  if(aTables_device_group !== 'pc'){
    var device_overall_settings_json = container.attr( 'data-wph-overall-settings-' + aTables_device_group );
  	device_overall_settings = (device_overall_settings_json && device_overall_settings_json.length > 3) ? JSON.parse( device_overall_settings_json ) : {};
    var overall_settings = $.extend( {}, pc_overall_settings, device_overall_settings );
  }else{
    var overall_settings = pc_overall_settings;
  }

  // console.log('settings for ' + aTables_device_group, overall_settings);
  // console.log('doc_width: ' + doc_width);

	return overall_settings;
}

function aTables_init (containers) {
	containers.addClass('wph_editor_kill_transition');
	containers.each( function ( ) {
		//get settings from the JSON based attr of atw_container
		var 	$this = $(this),
				overall_settings = aTables_get_overall_settings( $this.closest('.atw_container') ), // gets the settings for the current device group
				data = {};

		data.width_as = overall_settings['columnWidthCalculatedAs'] ? overall_settings['columnWidthCalculatedAs'] : 'px';
		data.contract_width = overall_settings['columnWidthBeforeExpansion'] ? overall_settings['columnWidthBeforeExpansion'] : 150;
		data.expand_width = overall_settings['columnWidthAfterExpansion'] ? overall_settings['columnWidthAfterExpansion'] : 320;
		data.max_col_per_row = overall_settings['maxColumnsPerRow'] ? overall_settings['maxColumnsPerRow'] : 8;
		data.column_gap_horizontal = overall_settings['columnGapHorizontal'] ? overall_settings['columnGapHorizontal'] : 5;
		data.column_gap_vertical = overall_settings['columnGapVertical'] ? overall_settings['columnGapVertical'] : 5;
		data.column_shadow = overall_settings['columnShadow'] ? overall_settings['columnShadow'] : 'medium';
		data.table_padding_horizontal = overall_settings['tablePaddingHorizontal'] ? overall_settings['tablePaddingHorizontal'] : 20;
		data.table_padding_vertical = overall_settings['tablePaddingVertical'] ? overall_settings['tablePaddingVertical'] : 20;
		data.animation_speed = overall_settings['animationSpeed'] ? parseInt(overall_settings['animationSpeed']) : 400;
		data.sliding_speed = overall_settings['slidingSpeed'] ? parseInt(overall_settings['slidingSpeed']) : 400;
		data.expansion_type = overall_settings['expansionType'] ? overall_settings['expansionType'] : "push";
		data.col_max_width = overall_settings['columnMax-width'] ? overall_settings['columnMax-width'] : 300;
		data.col_min_width = overall_settings['columnMin-width'] ? overall_settings['columnMin-width'] : 300;
		/*
		data.col_per_row_pc = overall_settings['columnsPerRowOnPC'] ? overall_settings['columnsPerRowOnPC'] : 5;
		data.col_per_row_tablet = overall_settings['columnsPerRowOnTablet'] ? overall_settings['columnsPerRowOnTablet'] : 3;
		data.col_per_row_mobile = overall_settings['columnsPerRowOnMobile'] ? overall_settings['columnsPerRowOnMobile'] : 1;
		*/

		// shadows
		$this.removeClass( ' atw_column_shadow_light atw_column_shadow_medium atw_column_shadow_heavy ' );
		if( data.column_shadow ) $this.addClass( 'atw_column_shadow_' + data.column_shadow );

		// percentage widths
		if( data.width_as === 'percentage' ){
	//		var available_width = $this.closest( '.atw_outer_container' ).width( ) - data.column_gap_horizontal - ( data.table_padding_horizontal * 2 );
	//		var available_width = $this.closest( '.atw_outer_container' ).width( ) - ( data.table_padding_horizontal * 2 );
			var available_width = $this.closest( '.atw_outer_container' ).width( ) + parseInt( data.column_gap_horizontal ) - ( data.table_padding_horizontal * 2 );

	/* 		console.log( 'available_width:  ', available_width );
			console.log( 'data.contract_width %: ', data.contract_width );
			console.log( 'data.expand_width %: ', data.expand_width );
	 */
			data.contract_width = ( available_width * data.contract_width ) / 100 - data.column_gap_horizontal;
			data.expand_width = ( available_width * data.expand_width ) / 100 - data.column_gap_horizontal;

	/* 		console.log( 'data.contract_width: ', data.contract_width );
			console.log( 'data.expand_width: ', data.expand_width );	 */
		}

		// .data holds this information for quicker retrieval
//		$this.data('data-wph-overall-settings', data);

		// --init responsiveness
		aTables_responsive($this, data);

		// --init bindings
		aTables_bindings($this);

		$this.addClass( 'atw_init' );

		// height bug hack
		$( '.atw_details', $this ).each( function( ){
			var 	$this = $( this ),
					height = $this.css( 'height' );
			if( height === '1px' ){
				$this.css( {'height': '', 'display':''} );
			}
		} )

	} )
	containers.removeClass('wph_editor_kill_transition');
}

// if a apply button is ever added
$('.wph_editor_aTables_overallOps_button').click(function () {
	aTables_init(wph_editor.target.container);
})

// responsive
function aTables_responsive (container, data) {

  var	data = data? data : container.data('data-wph-overall-settings');

	//get variables
	//--horizontal gap in between columns
	var		col_gap_horizontal = parseInt(data.column_gap_horizontal),
	//--vertical gap in between columns
			col_gap_vertical = parseInt(data.column_gap_vertical),
	//--table padding horizontal
			table_padding_horizontal = parseInt(data.table_padding_horizontal),
	//--table padding vertical
			table_padding_vertical = parseInt(data.table_padding_vertical),
	//--available width
			available_width = ( data.width_as === 'percentage' ) ? container.parent().width() + col_gap_horizontal - ( table_padding_horizontal * 2 ) : container.parent().width() - ( table_padding_horizontal * 2 );
	//--column per row for that size
			col_per_row = parseInt( data['max_col_per_row'] ),
	//--sliding speed
			sliding_speed = data['sliding_speed'],
	//--col expansion width
//			contract_width = ( data.width_as === 'percentage' ) ? parseInt(data['contract_width']) - col_gap_horizontal : parseInt(data['contract_width']), // Already did the subtraction
			contract_width = parseInt(data['contract_width']),
	//--col contraction width
//			expand_width = ( data.width_as === 'percentage' ) ? parseInt(data['expand_width']) - col_gap_horizontal : parseInt(data['expand_width']),
			expand_width = parseInt(data['expand_width']),
	//--col expansion type
			expansion_type = data['expansion_type'],
	//--col max permitted width
			col_max_width = data['col_max_width'],
	//--col min permitted width
			col_min_width = data['col_min_width'],
	//--max number of columns per row in contracted state
			max_contracted_cols_per_row = parseInt( available_width / ( contract_width + col_gap_horizontal ) );
			if( 1 > max_contracted_cols_per_row )
				max_contracted_cols_per_row = 1;
	//--$ cache of columns
			children = container.children('.atw_info_col');

	if (col_per_row > max_contracted_cols_per_row) col_per_row = max_contracted_cols_per_row; // user set more columns per row than contracted columns possible to fit in one row
	if (col_per_row > children.length) col_per_row = children.length; // user set 4 cols per row but there are only 3 columns total

/* 	console.log( 'contract_width: ', contract_width );
	console.log( 'expand_width: ', expand_width );	 */

	//--account for col expansion
	// currently col_per_row will keep the columns in a row only as long as none are expanded.
	// now we need to figure out the number of columns that can be supported with one of the columns expanded.
	var 	row_expanded_width, // will record the width of a row with a column expanded
			first = true;
	do{
		first ? first = false : col_per_row--;
		if( data.width_as === 'percentage' )
//			row_expanded_width =  ( ( col_per_row - 1 ) * contract_width ) + expand_width + ( ( col_per_row - 1 ) * col_gap_horizontal ) - col_gap_horizontal;
			row_expanded_width =  ( ( col_per_row - 1 ) * contract_width ) + expand_width + ( col_per_row * col_gap_horizontal ); // ### why subtract a col gap when when available_width is calculated to accommodate them all?
		else
			row_expanded_width =  ( ( col_per_row - 1 ) * contract_width ) + expand_width + ( col_per_row * col_gap_horizontal ) - col_gap_horizontal;
	}
	while( row_expanded_width > available_width && col_per_row > 1 );

	// case where the tiles are simply too broad for the container
	if ( row_expanded_width > available_width ){
		available_width = available_width + ( 2 * table_padding_horizontal );
		table_padding_horizontal = 0;

		contract_width = contract_width > available_width  ? available_width : contract_width;
		expand_width = expand_width > available_width  ? available_width : expand_width;
	}

/* 	console.log( 'row_expanded_width: ', row_expanded_width );
	console.log( 'col_per_row: ', col_per_row ); */

	//--details section width
	container.find( '.atw_details' ).each( function( ){
		var 	$this  = $( this ),
				$parent = $this.parent( ),
				$padding = parseInt( $this.css( 'padding-left' ) ) + parseInt( $this.css( 'padding-right' ) ),
				$parent_padding = parseInt( $parent.css( 'padding-left' ) ) + parseInt( $parent.css( 'padding-right' ) );
//		$this.outerWidth( expand_width - $parent_padding );
		$this.width( expand_width - $parent_padding - $padding );
	} )

	//set table padding
	container.css('padding', table_padding_vertical+'px '+table_padding_horizontal+'px');

	//apply column widths
	//-- contract
	children.children('.atw_info_col_details').not('.atw_curCol').width(contract_width);
	//-- expand
	children.children('.atw_info_col_details.atw_curCol').width(expand_width);

	//set container width
	if( data.width_as === 'percentage' )
		container.css( 'width', row_expanded_width + (table_padding_horizontal*2) - col_gap_horizontal );
	else
		container.css( 'width', row_expanded_width + (table_padding_horizontal*2) );

	//adjust margins
	var width_diff = expand_width - contract_width;

	//cache args for corner grid system
	container.data('atw_regrid', {
		children:children,
		col_per_row:col_per_row,
		col_gap_horizontal:col_gap_horizontal,
		col_gap_vertical:col_gap_vertical,
		expansion_type:expansion_type,
		width_diff:width_diff
	})

	//apply corner grid system
	aTables_corner_grid( container, children, col_per_row, col_gap_horizontal, col_gap_vertical, expansion_type, width_diff );

	//center images
	var inner = children.children('.atw_info_col_details');
	inner.not('.atw_curCol').find('.atw_image>img').aTablesCenterImage('inside', false, contract_width, 0);
	inner.filter('.atw_curCol').find('.atw_image>img').aTablesCenterImage('inside', false, expand_width, 0);
	atw_adjust_margins(container, 0, expand_width, contract_width)

}

// trigger on window resize
var aTables_responsive_all = aTables_debounce(function() {
		$('.atw_container').each(function () {
      aTables_init($(this));
		})
}, 150);
window.addEventListener ? window.addEventListener('resize', aTables_responsive_all) : window.attachEvent('resize', aTables_responsive_all);

// aligns children into grids with no margins
function aTables_corner_grid (container, children, col_per_row, gap_horizontal, gap_vertical, expansion_type, width_diff) {

	if (!children) {
		var data = container.data('atw_regrid');
		children = container.children('.atw_info_col');
		col_per_row = data.col_per_row;
		gap_horizontal = data.col_gap_horizontal;
		gap_vertical = data.col_gap_vertical;
		expansion_type = data.expansion_type;
		width_diff = data.width_diff;
	}
	//neutralization needed for all children including helper
	children.css({
		'margin-right': parseInt(gap_horizontal),
		'margin-bottom': gap_vertical,
		'clear':'none',
		'min-height':0,
		'height':'auto',
		'transition' : '0s all'
	}).removeClass('wph_start_of_row wph_end_of_row wph_editor_target_pseudo_row');

	var selected_child = children.filter( function( ){
		var $this = $( this );
		if( $this.children( '.atw_curCol' ).length ) return $this;
	} )

	// setting up variables
	var 	children = children.not('.ui-sortable-helper'),
			total_children = children.length,
			heights = [],
			targets = [];

	//bring children to default
	children.css({
		'margin-left' : 0
	}).first().addClass('wph_start_of_row');

	// iterate over children, removing margin right from end of row children,
	// and raising heights of all children in a row to the tile with max height
	for (var i = 1; i < total_children+1; i++ ){
		var $this = children.eq(i-1);
		heights.push($this.height());
		targets.push($this);
		if (((i)%col_per_row === 0 && i!==0) || i === total_children){
			$this.css('margin-right', 0).addClass('wph_end_of_row');
			var max_height = Math.max.apply(null, heights);
			targets[0].css('margin-left', 0);
			$.each(targets, function (i, elm) {
				// checking for selected_child for the corner case where table starts off with a pre expanded column
				expansion_type === "overlap" ? elm.css('height', max_height) : selected_child.length ? elm.css('min-height', '') : elm.css('min-height', max_height); //default is 'push'
			})
			heights = [];
			targets = [];
			var next = $this.next();
			if (next.hasClass('ui-sortable-helper')) next = next.next();
			next.css('clear','left').addClass('wph_start_of_row');
		}
	}
	//remove margin bottom for last row
	children.slice('-'+col_per_row).css('margin-bottom',0);
	children.filter('.wph_start_of_row').css({
		'margin-left': width_diff/2,
		'transition': 'none'
	});

}

/*State Change*/
function aTables_bindings ($container) {
	//turn all bindings off so we do not rebind by accident
	$container.off('mouseenter mouseleave click', '.atw_cover')

	//hover and idle states are toggled on an element only if it does not have the atw_active class
	$container.on('mouseenter', '.atw_cover', function (e) {
		var $this = $(this);
		if (!$this.hasClass('atw_active')) {
			//hover
			wph_editor.set_state($this, 'hover');
		}

	}).on('mouseleave', '.atw_cover', function () {
		var $this = $(this);
		if (!$this.hasClass('atw_active')) {
			//idle
			wph_editor.set_state($this, 'idle');
		}

	}).on('click', '.atw_cover', function (e) {

		if (atw_lock) return;
		var $this = $(this);
		// clicking keys should not trigger the cell
		$target = $(e.target);
		if ($target.closest('.wph_keys').length) return;
		// reactions
		if ($target.closest('.wph_cell').attr('data-wph-element-name') === "regular") {
			var title = $target.closest('.atw_title');
			if (!title.length) return; // no reaction on clicking other than title
			else { // no reaction if there are no details
				var details = title.next('.atw_details');
				if (!details.length || !details.text() || !details.text().trim) return;
			}
		}
		// active
		if (!$this.hasClass('atw_active')) wph_editor.set_state($this, 'active');
		// hover
		else wph_editor.set_state($this, 'hover');

	})
}


/**
* Admin
*/

// provides aTables specific callbacks for the editor
wph_editor.plugins['aTables'] = {};

// animation speed
wph_editor.plugins['aTables']['anim_speed'] = function (elm) {
	var 	overall_settings_attr = elm.closest('.atw_container').attr('data-wph-overall-settings'),
			overall_settings = overall_settings_attr ? JSON.parse(overall_settings_attr) : {},
			animation_speed = overall_settings['animationSpeed'] ? parseInt(overall_settings['animationSpeed']) : 400;
	return animation_speed;
};

/*-- Admin functions only beyond this point --*/
if( typeof wph_editor.add_action !== 'function' ) return;

// init
wph_editor.plugins['aTables'].init = function (container) {
	aTables_init(container);
}

// image replaced callback
wph_editor.plugins['aTables']['image_replaced_callback'] = function (img) {
	img.aTablesCenterImage('inside');
}

// return components to wph editor interface
wph_editor.plugins['aTables'].get_components = function (elm) {
	// Container
	if (elm.find('.atw_info_col').length) {
		return {
			"Element": "Container",
			"Container": elm,
		};
	}
	//Regular
	if (elm.find('.atw_title').length) {

		// add 2nd icon if not there
		elm.closest( '.wph_container' ).find( '.wph_editor_target_element' ).each( function( ){
			var $this = $( this );
			if( $this.is( '[data-wph-element-name="regular"]' ) && ! $this.find( '.atw_cell_title_icon_2' ).length ) $this.find( '.atw_cell_title_icon' ).before( '<div class="atw_cell_title_icon_2  fa fa-angle-down " data-wph-icon="fa fa-angle-down" style="display:none;" data-wph-idle="{&quot;display&quot;:&quot;none&quot;}"></div>' );
		} );

		return {
			"Element": "Title",
			"Overall": elm.find('.atw_cell'),
			"Title Overall":elm.find('.atw_title'),
			"Title Text":elm.find('.atw_cell_title_text'),
			"Icon":elm.find('.atw_cell_title_icon'),
			"Value":elm.find('.atw_cell_title_val'),
			"Details":elm.find('.atw_details'),
			"Icon 2":elm.find('.atw_cell_title_icon_2'),
		};
	}
	//Image
	if (elm.find('.atw_image').length) {
		return {
			"Element": "Image",
			"Overall": elm.find('.atw_image'),
			"Image": elm.find('.atw_image img'),
		};
	}
	//Column Title
	if (elm.find('.atw_column_title').length) {

		// add title text element if not there
		elm.closest( '.wph_container' ).find( '.wph_editor_target_element' ).each( function( ){
			var $this = $( this );
			if( $this.is( '[data-wph-element-name="column title"]' ) && ! $this.find( '.atw_column_title_text' ).length ) {
				var 	title_overall = $this.find( '.atw_column_title' ),
						content = title_overall.html(  );

				title_overall.html( '<div class="atw_column_title_icon"></div><div class="atw_column_title_text">' + content + '</div>' );
			}
		} );

		return {
			"Element": "Column Title",
			"Overall": elm.find('.atw_column_title'),
			"Text": elm.find('.atw_column_title_text'),
			"Icon": elm.find('.atw_column_title_icon'),
		};
	}
	//Price
	if (elm.find('.atw_price').length) {
		var overall = elm.find('.atw_price');
		return {
			"Element": "Price",
			"Overall": overall,
			"Previous": overall.children('.atw_prev_price'),
			"Current": overall.children('.atw_current_price'),
			"Duration": overall.children('.atw_price_duration'),
		};
	}
	//Link
	if (elm.find('.atw_link').length) {
		return {
			"Element": "Link",
			"Overall": elm.find('.atw_link'),
			"Text": elm.find('.atw_link_text'),
			"Link": elm.find('.atw_link_text'),
			"Icon": elm.find('.atw_link_icon'),
		};
	}

	return {};
};

// get type
wph_editor.plugins['aTables'].get_type = function (elm) {
	var cell = elm.children('.atw_cell');
	if (cell.children().hasClass('atw_title')) return "Regular";
	if (cell.hasClass('atw_column_title')) return "Column Title";
	if (cell.hasClass('atw_image')) return "Image";
	if (cell.hasClass('atw_price')) return "Price";
	if (cell.hasClass('atw_link')) return "Link";
}

// set change
wph_editor.plugins['aTables']['set_state'] = function (elm, state) {
	//however, there are other changes that need to be triggered as well, which will be handled here.
	//reason why using .trigger(state) is not enough is 'cuz it can't simulate things like "un-click" before setting new state (eg triggered by clicking a second time)
	//unclicking in this case causing the details panel to slideup and hide (this is something unknown to and not controlled by the editor)
	if (!elm.hasClass('atw_cover')) return;
	state = state.toLowerCase();
	if (state === "idle" || state ==="hover") {
		//check if cell is currently active, de-activate if so
		if (aTables_is_active_cell (elm)) aTables_deactivate_cell(elm);
		//set state in either condition
		wph_editor.set_state (elm, state);
	} else if (state === "active") {
		//check if cell is currently active, activate if not
		if (!aTables_is_active_cell (elm)) aTables_activate_cell(elm);
		else return;
	} else {
		return;
	}

};

//set state styles
wph_editor.plugins['aTables']['set_state_styles'] = function (style_obj, component, state) {
	if (component.hasClass('atw_details')) state = ["Idle", "Active"];
	return [style_obj, component, state];
}

// check if element is in active state
function aTables_is_active_cell (elm) {
	return elm.find('.atw_current').length;
}

// deactivate element
function aTables_deactivate_cell (elm) {
	elm.find('.atw_trigger.atw_current').click();
}

// activate element
function aTables_activate_cell (elm) {
	elm.find('.atw_trigger').last().click();//we don't want each cell in the selection to be triggered

}

// targeting
$('.wph_editor_aTables_targetOps select').click(function () {
	$(this).change();
});

wph_editor.live_changes_aTables_target_execution = function (data) {
	var 	panel = $('.wph_editor_aTables_targetOps'),
			container = data.container,
			target = $(),
			columns = panel.find('[data-wph-property="columns"]').next().val(),
			custom_columns = panel.find('[data-wph-property="customColumns"]').next().val(),
			rows = panel.find('[data-wph-property="rows"]').next().val(),
			custom_rows = panel.find('[data-wph-property="customRows"]').next().val(),
			cell_type = panel.find('[data-wph-property="cellType"]').next().val(),
			multiple_cell_types = panel.find('[data-wph-property="multipleCellTypes"]').next().val();

	panel.find('.wph_editor_hide').each(function () {
		var 	$this = $(this);
				prev = $this.prev().children('select').val().trim();
		if (prev === "custom" || prev === "multiple") {
			$this.show();
		} else {
			$this.hide();
		}
	})

	// columns
	if (columns === "all") {
		target = container.find('.atw_info_col');
	} else if (columns === "custom") {
		custom_columns = custom_columns.trim().split(",");
		$.each(custom_columns , function (i, val) {
			val = parseInt(val);
			target = target.add(container.find('.atw_info_col').eq(val - 1));
		})
	} else {
		if (columns === "even") columns = "odd";
		else if (columns === "odd") columns = "even";
		target = container.find('.atw_info_col:'+columns);
		if (!target.length) target = container.find('.atw_info_col');
	}
	columns = target;

	// cell types
	target = columns.find('.atw_cover'); // case of all
	if (cell_type !== "all" && cell_type !== "multiple") {
		target = columns.find('.atw_cover').filter('[data-wph-element-name="'+cell_type+'"]');
	} else if (cell_type === "multiple") {
		var filter = "";
		multiple_cell_types = multiple_cell_types.trim().split(",");
		$.each(multiple_cell_types, function (i, type) {
			filter +='[data-wph-element-name="'+type.toLowerCase().trim()+'"],'
		})
		filter = filter.substr(0, filter.length - 1); // remove trailing comma
		target = columns.find('.atw_cover').filter(filter);
	}
	cell_type = target;

	// rows
	target = $();
	if (rows === "all") {
		target = cell_type;
	} else if (rows === "custom") {
		custom_rows = custom_rows.trim();
		if (custom_rows) {
			var filter = "";
			custom_rows = custom_rows.split(",");
			$.each(custom_rows, function (i, type) {
				filter +=':eq('+(type.toLowerCase().trim() - 1)+'),';
			})
			filter = filter.substr(0, filter.length - 1); // remove trailing comma
			columns.each(function () { // so we get eg - 2nd and 3rd of each column, not total
				target = target.add($(this).find('.atw_cover').filter(cell_type).filter(filter));
			})
		}
	} else {
		if (rows === "even") rows = "odd";
		else if (rows === "odd") rows = "even";
		columns.each(function () {
			target = target.add($(this).find('.atw_cover').filter(cell_type).filter(':'+rows));
		})
		if (!target.length) {
			columns.each(function () {
				target = target.add($(this).find('.atw_cover').filter(cell_type));
			})
		}
	}

	// re-select
	$('.wph_editor_target_element').removeClass('wph_editor_target_element');
	if (target.length > 1) { // editor refresh should only occur once
		var 	last = target.last(),
				others = target.not(last);
		others.addClass('wph_editor_target_element');
		target = last;
	}
	target.children('.wph_keys_center_horizontal').find('.wph_editor_key_select').click();
}

//preset color scheme
if (typeof aTables_presets_map !== "undefined") {
	var 	fields = $('.atw_preset_name'),
			map = aTables_presets_map;
	fields = fields.add($('.atw_preset_type, .atw_preset_color_scheme'));
	wph_editor.preset_select_boxes_init(map, fields, false);
	wph_editor.preset_select_boxes_click_behaviour(map, fields);
}

//get preset
$('.atw_preset_input_button').click(aTables_ajax_get_preset);
function aTables_ajax_get_preset () {
   $.ajax({
		type : "post",
		dataType : "json",
		url : wph_ajax.url,
		beforeSend: function () {
			var proceed = confirm($('.atw_preset_input_button').attr('data-atw-message'));
			if (proceed) {
				wph_editor.start_loader($('.atw_preset_input_button'));
			} else {
				wph_editor.stop_loader($('.atw_preset_input_button'));
				return false;
			}
		},
		data : {"action":"aTables_get_preset_via_ajax", "nonce":wph_ajax.nonce, "name":$('.atw_preset_name').val(), "type":$('.atw_preset_type').val(), "color":$('.atw_preset_color_scheme').val()},
		success: function (response) {
			if (response && response.result ==="success" ) {
				var 	preview = $('.aTables_preview'),
						prev = preview.children('.atw_outer_container'),
						data_id = 'data-wph-post-id',
						id = prev.children().attr(data_id);
				prev.replaceWith(response.html)
				preview.find('.atw_container').attr(data_id, id);
				wph_editor.init_keys();
				aTables_init(preview.find('.atw_container'));
			} else {
				alert(response.message)
			};
		},
		complete: function () {
			wph_editor.stop_loader($('.atw_preset_input_button'));
		}
	});
}


// color theme
$('body').on('click', '.atw_extract_color_theme_button', wph_editor.extract_color_theme);

//action hooks

//--ensure a device group is selected
wph_editor.add_action('after_load_data_to_editor', function atw_ensure_device_group_selection( ){
	var selected_device_group = wph_editor.ensure_selection( $( '.wph_editor_device_groups_menu' ) );
});


//--keys: sorting
wph_editor.add_action('cell_sorting', atw_regrid);
wph_editor.add_action('column_sorting', atw_regrid);
function atw_regrid (data) {
	if (data.container.attr('data-wph-plugin') !== "aTables") return;
	if (data.ui) {
		var col = data.ui.item;
		if (!col.hasClass('atw_info_col')) col = col.closest('.atw_info_col');
		col.css('zIndex',1);
	}
	aTables_corner_grid (data.container);
	if (data.event && data.event.type === "sortstop") col.css('zIndex',0);
}

//--keys: copy delete
wph_editor.add_action('delete_elm', atw_re_responsive);
wph_editor.add_action('copy_elm', atw_re_responsive);
function atw_re_responsive (data) {
	if (data.container.attr('data-wph-plugin') !== "aTables") return;
  aTables_init(data.container)
}

//--display correct style ops
//wph_editor.add_action('runnable_style', atw_correct_style_ops);
var atw_style_view_affected = [];
function atw_correct_style_ops () { // only padding relates properties to be shown
	if (wph_editor.target.plugin !== "aTables") return;
	var 	parent = $('.wph_editor_Dimensions'),
			component = wph_editor.target.current.component_name;

	parent.children('.wph_editor_p').each(function () {
		var 	$this = $(this),
				prop = $this.children('label').attr('data-wph-property'),
				image = false;

		if ( $('.wph_editor_componentIndex').children('[data-wph-type="Image"]').is(':visible') && $('.wph_editor_componentIndex').children('[data-wph-type="Overall"]').hasClass('wph_editor_selected')) image = true;

		if (prop.indexOf('padding') === -1) {
			if (image) {
				if (prop !=="height") atw_style_view_affected.push($this);
			} else {
				atw_style_view_affected.push($this);
			}
		}
	})
	$.each(atw_style_view_affected, function (i, elm) {
		elm.hide();
	})
}

//--change type
wph_editor.add_action('change_type', aTables_change_type)
function aTables_change_type (data) {
	if (wph_editor.target.plugin !== "aTables") return;
	var 	elm = $('.wph_editor_target_element'),
			type = data.type;
	elm.attr('data-wph-element-name', type.toLowerCase()); // the element holds the type name attr
	elm.children('.atw_cell').replaceWith(aTables_cell_types[type]); // the child cell gets the content
	if (type === "Image") elm.find('.atw_image>img').aTablesCenterImage();
	wph_editor.get_data_from_elms();

}

//--clean-up for display correct style ops
wph_editor.add_action('runnable_style_cleanup', atw_cleanup);
function atw_cleanup () {
	$.each(atw_style_view_affected, function(i, elm) {
		elm.css('display', '');
	})
	atw_style_view_affected = [];
}

//--image height live change
wph_editor.add_action('live_changed_style', atw_live_change_reinit);
function atw_live_change_reinit (data) {
	if (wph_editor.target.plugin === "aTables") {
		if (data.prop === "height" && wph_editor.target.current.component.hasClass('atw_image')) {
			wph_editor.target.current.component.children('img').aTablesCenterImage('inside', false, false, 0);
		}
		if (!wph_editor.target.current.component.hasClass('atw_details')) { // else we get reinit->slideUp mayhem
			var 	props = ['margin', 'padding', 'height'],
					reinit = false;
			$.each(props, function (i, val) {
				if (data.prop.indexOf(val) !== -1) reinit = true;
			})
			if (reinit) aTables_init(data.container);
		}
	}

}

//--set image other ops
wph_editor.add_action('live_changes_plugin_execution', atw_image_other_ops);
function atw_image_other_ops (data) {
	if (wph_editor.target.plugin !== "aTables" || wph_editor.target.element_name !== "Image") return;
	data.component = data.elms.find('.atw_image img');

	var attr = data.component.attr('data-wph-settings');
	attr = attr ? JSON.parse(attr) : {};
	attr[data.prop] = data.val;
	data.component.attr('data-wph-settings', JSON.stringify(attr));
	if (data.prop === "imageCenteringStyle")
		data.component.aTablesCenterImage(data.val, false, false, 0);
	if (data.prop === "imageTitle")
		data.component.attr('title', data.val);

}

//--other
wph_editor.add_action('runnable_other', atw_image_load_other_ops)
function atw_image_load_other_ops () {
	if (wph_editor.target.plugin !== "aTables" || wph_editor.target.element_name !== "Image") return;
	var 	img = wph_editor.target.parents.find('img'),
			panel = $('.wph_editor_aTables_imageOtherOps'),
			title = img.attr('title'),
			settings = img.attr('data-wph-settings'),
			centering = "outside";
	settings = settings ? JSON.parse(settings) : {};
	title = title ? title : "";
	centering = settings["imageCenteringStyle"] ? settings["imageCenteringStyle"] : centering;

	panel.find('[data-wph-property="imageCenteringStyle"]+select').val(centering);
	panel.find('[data-wph-property="imageTitle"]+input').val(title);

}

//--ensuring element in active state opens the editor in active
//-- --flag when new elements are selected
/*
var atw_new_load = false;
wph_editor.add_action('before_load_data_to_editor', atw_mark_new_load);
function atw_mark_new_load () {
	atw_new_load = true;
}
//-- --set flag off upon style execution. Reflect state on first load
wph_editor.add_action('runnable_style', atw_load_correct_state);
function atw_load_correct_state () {
	if (atw_new_load && $('.wph_editor_target_element').last().hasClass('atw_active'))
		$('.wph_editor_componentState .wph_editor_Active').click();
	else
		wph_editor.plugins[plugin]['set_state'](elm, state); //set the state on the element
	atw_new_load = false;
}
*/

//--save
wph_editor.add_action( 'save', aTables_save );
function aTables_save (elm){

	if( wph_editor.target.plugin !== "aTables" ) return;

	var html = elm.parent().clone(true, true);
	html.find('.wph_keys_center').remove();
	html.find('.wph_editor_target_element, .wph_editor_target_pseudo_row').removeClass('wph_editor_target_element wph_editor_target_pseudo_row');

   $.ajax({
		type : "post",
		dataType : "json",
		url : wph_ajax.url,
		beforeSend: function () {
			wph_editor.start_loader();
		},
		data : {"action":"aTables_save", "nonce":wph_ajax.nonce, "html":html[0].outerHTML, "post-id":elm.attr('data-wph-post-id')},
		success: function (response) {
			if (response && response.result ==="success" ) {
				wph_editor.stop_loader();
			} else {
				alert(response.message);
			};
		}
	});

};

//psuedo rows
//--delete
wph_editor.add_action('delete_pseudo_row', atw_delete_pseudo_row);
function atw_delete_pseudo_row (data) {
	var 	starting_cells = data.starting_cells;

	if (!$(data.context).closest('.atw_container').length) return; // check if applicable

	//confirmation
	if (!starting_cells.length || !confirm("There are "+starting_cells.length+" rows selected. Press OK to delete all of them.")) return;

	starting_cells.each(function () {
		cells = wph_editor.target_pseudo_row($(this));
		cells.each(function () {
			$(this).find('.wph_keys_center_horizontal .wph_editor_key_delete').click();
		})
	})
}


//--copy
wph_editor.add_action('copy_pseudo_row', atw_copy_pseudo_row);
function atw_copy_pseudo_row (data) {
	var 	starting_cells = data.starting_cells,
			cells = $();

	if (!$(data.context).closest('.atw_container').length) return; // check if applicable

	// clean up before hand just to be safe
	$('.wph_marked_by_copy_process').removeClass('wph_marked_by_copy_process');
	// build collection of all the cells at the start of pseudo rows
	starting_cells.each(function () {
		cells = cells.add(wph_editor.target_pseudo_row($(this)));
	})
	// do the copying
	cells.addClass('wph_marked_by_copy_process').each(function () {
		wph_editor.copy_cell.call($(this), "pseudo row");
	})
	//clean up
	$('.wph_marked_by_copy_process').removeClass('wph_marked_by_copy_process');
	// initiating keys at the very end to save on overhead
	wph_editor.init_keys();

}

//--move
wph_editor.add_action('psuedo_row_dragged', atw_psuedo_row_dragged);
function atw_psuedo_row_dragged (data) {

	if (!data.ui.helper.closest('.atw_container').length) return; // check if applicable

	var 	event = data.event,
			ui = data.ui,
			cell = ui.helper.closest('.wph_cell'),
			prev = cell.prevAll('.wph_cell'),
			next = cell.nextAll('.wph_cell'),
			position = ui.position.top,
			originalPosition = ui.originalPosition.top,
			moveTo = false,
			heights = 0,
			query_attr = 'data-wph-element-name',
			cell_type = cell.attr(query_attr),
			originalIndex = cell.parent().children('.wph_cell['+query_attr+'="'+cell_type+'"]').index(cell);

	if ( position < originalPosition ) { // shift up
		if (position > 0) return; // still within bounds of the element
		cell.prevAll('.wph_cell['+query_attr+'="'+cell_type+'"]').each(function () {
			moveTo = $(this);
			heights += moveTo.outerHeight(true);
			if (heights > Math.abs(position))
			return false;
		})
	} else { // shift down
		position = position - cell.outerHeight(true); // neutralize
		if (position < 0) return; // still within bounds of the element
		cell.nextAll('.wph_cell['+query_attr+'="'+cell_type+'"]').each(function () {
			moveTo = $(this);
			heights += moveTo.outerHeight(true);
			if (heights > Math.abs(position))
			return false;
		})
	}

	if (moveTo) {
		var newIndex = moveTo.parent().children('.wph_cell['+query_attr+'="'+cell_type+'"]').index(moveTo);
		wph_editor.shift_psuedo_row (cell, cell_type, originalIndex, newIndex)
	}

}

//keyboard navigation
//--select nearby element
wph_editor.add_action('kb_select_element', atw_kb_select_element);
function atw_kb_select_element (data) {
	if (wph_editor.target.plugin !== "aTables") return; // check if its the plugin

	if (!wph_editor.keyboard_navigation_permission()) return;
//	if (!wph_editor.focus_on_content()) return;

	var 	e = data.e,
			direction = data.direction,
			element = $('.wph_editor_target_element'),
			textarea = $('.wph_editor_contentTextarea');
	if (element.length > 1) { // multiple selected
		var target_keys = element.first().find('>.wph_keys_center_horizontal>.wph_keys');
		target_keys.children('.wph_editor_key_select').click(); // remove first element from selection
		target_keys.children('.wph_editor_key_settings').click(); // now select it to start editing
	} else { // single selected
		var	column = element.closest('.wph_column'),
				attr = 'data-wph-element-name',
				cell_type = element.attr(attr),
				selector = '.wph_cell['+attr+'="'+cell_type+'"]',
				index = element.parent().children(selector).index(element),
				target = $();
		switch(direction) {
			case "up":
				target = element.prev('.wph_cell');
				break;

			case "right":
				//get target column
				var next_column = column.next('.wph_column');
				if (!next_column.length) break;
				//get target cell
				target = next_column.find(selector).eq(index);
				if (!target.length) target = $();
				break;

			case "down":
				target = element.next('.wph_cell');
				break;

			case "left":
				//get target column
				var prev_column = column.prev('.wph_column');
				if (!prev_column.length) break;
				//get target cell
				target = prev_column.find(selector).eq(index);
				if (!target.length) target = $();
				break;
		}
		target.find('>.wph_keys_center_horizontal>.wph_keys>.wph_editor_key_settings').click();
	}
	if (textarea.is(':visible')) textarea.focus();

}

/*--same element - other components*/
wph_editor.add_action('kb_select_component', atw_kb_select_component);
function atw_kb_select_component (data) {
	if (wph_editor.target.plugin !== "aTables") return; // check if its the plugin
	if (!wph_editor.keyboard_navigation_permission()) return;
	var 	e = data.e,
			direction = data.direction,
			components = $('.wph_editor_componentIndex>span[data-wph-type]:visible');
	if (direction === "prev" ) {
		var 	prev_component = components.filter('.wph_editor_selected').prev().filter(components),
				select_component = prev_component.length ? prev_component : components.last();

	} else {
		var 	next_component = components.filter('.wph_editor_selected').next().filter(components),
				select_component = next_component.length ? next_component : components.first();

	}
	var textarea = $('.wph_editor_contentTextarea');
	select_component.click();
	if (textarea.is(':visible')) textarea.focus();
}

})

// debounce
function aTables_debounce(func, wait, immediate) {

	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	}

}
