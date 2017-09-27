/*wph_editor header*/
if (typeof wph_editor !== "object") {
	var wph_editor = {};

	/*Plugins hook in here*/
	wph_editor.plugins = {};
}

jQuery(function ($) {

/*Applies a state on the components of an element*/
wph_editor.set_state = function (elm, state) {

	elm.attr('data-wph-current-state', state);
	var 	states = ["idle", "hover", "active"],
			affected_selector = "",
			anim_speed;

	if( typeof wph_editor.get_plugin(elm) === 'undefined' ) anim_speed = 0;
	else anim_speed = (typeof wph_editor.get_plugin(elm)['anim_speed'] === "function") ? wph_editor.get_plugin(elm)['anim_speed'](elm) : 400;

	$.each(states, function (key,val) {
		affected_selector+= "[data-wph-"+val+"],";
	})

	affected_selector = affected_selector.substring(0, affected_selector.length - 1); // remove trailing comma

	// Each animation needs an end point corresponding to its start point, however it is not necessary both will be
	// readily available. For example, an idle state may have a line-height attached while there is none for the
	// elm's hover state, resulting in an animation jump when moving between the two states.
	// Thus, action must be taken to ensure that there are end points. These will be acquired via inheritance. While
	// determining end points, priority is given to the element's idle state, so the other states
	// will inherit from the idle state if their own corresponding style end points properties are not set. If the required values are not
	// found in the elm's idle state (our animation is arriving at the idle state itself or at the hover state from active state), then we look
	// for them in the immediate parent's current-style properties (the move to parents above). The parents' would have already gone through the
	// procedure earlier, so a parent's idle state values maybe related to it's child/grandchild's current state.
	// But the parents' style props are not ready for use since they are likely mid animation. So we use their 'wph-current-state' data prop
	// which contains the style props they are about to arrive at. Finally, if the style props we want are not available with any of the parents'
	// current state, ie, it is not being animated on them, then it is safe to flash clear the element's style properties for a moment to collect it's
	// natural style value for that property. While properties could have been hard-coded while being set via the editor, there would be a severe loss in
	// its own dynamic inheritance mechanism.

	elm.find(affected_selector).each(function () {
		var 	$this = $(this),
				old_style = wph_editor.get_inline_styles($this) || {}, // these are animation start points, each property needs an end point
				idle_style = $this.attr('data-wph-idle') ?  JSON.parse($this.attr('data-wph-idle')) : {}, // while inheriting, idle_styles have priority above parents
				new_style = $this.attr('data-wph-'+state) ? JSON.parse($this.attr('data-wph-'+state)) : {}, // these are end points we have with us right now
				got_style = $.extend({}, idle_style, new_style); // these are end points we already have;

		if (old_style['border'] || old_style['border-width']) {
			old_style['border-top-width'] = $this.css('border-top-width');
			old_style['border-right-width'] = $this.css('border-right-width');
			old_style['border-bottom-width'] = $this.css('border-bottom-width');
			old_style['border-left-width'] = $this.css('border-left-width');
			old_style['border-style'] = $this.css('border-style');
			old_style['border-color'] = $this.css('border-color');
		}

		if (old_style['border-radius']) {
			old_style['border-top-left-radius'] = $this.css('border-top-left-radius');
			old_style['border-top-right-radius'] = $this.css('border-top-right-radius');
			old_style['border-bottom-right-radius'] = $this.css('border-bottom-right-radius');
			old_style['border-bottom-left-radius'] = $this.css('border-bottom-left-radius');
		}

		delete old_style['border'];
		delete old_style['border-width'];
		delete old_style['border-radius'];

		delete got_style['border'];
		delete got_style['border-width'];
		delete got_style['border-radius'];

		var 	returned_style = wph_editor.get_anim_end_points(old_style, got_style, $this, elm), // fetch end vals for props missing them
				end_style = $.extend({}, got_style, returned_style[0]), // fetch end vals for props missing them
				remove_styles = returned_style[1]; // styles to remove post anim. to maintain inheritance mech.

		$this.data('wph-current-state', end_style);
		if ($this.hasClass('wph_editor_no_anim')) {
			$this.css(end_style);
		}
		else {
			var 	non_animatable = ['font-family'],
					non_animatable_end_style = {};
			if (!$.isEmptyObject(end_style)) {
				// bring out
				$.each(end_style, function (prop, val) {
					if ($.inArray(prop, non_animatable) !== -1) {
						non_animatable_end_style[prop] = val;
						delete end_style[prop]
					}
				})
				if(typeof aTables_device_group === 'undefined') {
					aTables_device_group = "pc";
				}
				if(aTables_device_group !== "pc"){
					anim_speed = 1;
				}
				$this.stop(true).animate(end_style, anim_speed, "linear", function () {
					if (remove_styles === null) return;
					var _ = $(this);
					$.each(remove_styles, function (i, prop){
						_.css(prop, '');
					})
					//console.log(remove_styles);
				});
				$this.css(non_animatable_end_style);
				//console.log('non_animatable_end_style: ', non_animatable_end_style);
				//console.log('animatable_end_styles: ', end_style);
			}
		}
	})

}

/*Gets the plugin's hooked-in object from which we can call functions*/
wph_editor.get_plugin = function (elm) {
	var 	plugin_name = (typeof elm !== "undefined") ? elm.closest('[data-wph-plugin]').attr('data-wph-plugin'): wph_editor.target.plugin,
			plugin_fns_obj = wph_editor.plugins[plugin_name]; // object contains all the plugin's hooked in fns
	return plugin_fns_obj;
}

/*Gets styles object from inline styles*/
wph_editor.get_inline_styles = function (elm) {
	var 	style = elm.attr("style"),
			obj = {};
	if (typeof style !== "undefined") {
		style = style.split(';');
		$.each(style, function (key, val) {
			if (val && $.trim(val) !== "") {
				val = val.split(':');
				obj[$.trim(val[0])] = $.trim(val[1]);
			}
		})
	}
	return obj;
}

/*Gets animation end points for an element that needs to be animated*/
wph_editor.get_anim_end_points = function (old_style, got_style, component, cover) {
	if (component.hasClass('wph_editor_no_transition')) return [old_style, null];

	var missing_styles = []; // will store props which need end points
	//remove props for which end points are already available
	$.each(old_style, function (prop, val) {
		if (typeof got_style[prop] !== "undefined") old_style[prop] = got_style[prop];
		else missing_styles.push(prop);
	})

	// case where all end points are already available
	if (!missing_styles.length || !cover) {
		return [old_style, null];
	}

	var 	parents = component.parentsUntil(cover.parent()),
			inheritable = ['font-size', 'line-height', 'color', 'font-family', 'font-weight', 'text-align', 'text-decoration', 'font-style', 'text-transform'],
			inherited = []; // caches inherited props names so they can be removed later from inline style to maintain inheritance

	if (parents.length) {
		parents.each(function () {
			if (!missing_styles.length) return false // stop looking if all props found
			var 	$this = $(this),
					current_state = $this.data('wph-current-state') || {};
			$.each(missing_styles, function (i, prop) {
				if ($.inArray(prop, inheritable) !== -1) {
					// if the prop is found, use it, remove it from the array list of missing
					if (current_state[prop]) {
						old_style[prop] = current_state[prop];
						var index = missing_styles.indexOf(prop);
						missing_styles.splice(index, 1);
						inherited.push(prop);
					}
				}
			})
		})
	}

	//flash inline style free regular css style to get the remaining missing properties
	if (missing_styles.length) {
		var style = component.attr('style');
		component.attr('style', '');
		$.each(missing_styles, function (i, prop) {
			old_style[prop] = component.css(prop);
			if ($.inArray(prop, inheritable) !== -1) inherited.push(prop);
		});

		component.attr('style', style);
	}
	return [old_style, inherited];

}

})
