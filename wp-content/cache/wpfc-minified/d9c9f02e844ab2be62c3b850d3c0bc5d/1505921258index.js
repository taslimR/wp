// source --> http://www.bristolgate.com/wp-content/plugins/accordion-tables/assets/js/jquery.blImageCenter.aTables.js?ver=4.8.2 
/**
 * bl-jquery-image-center jQuery Plugin
 *
 * @copyright Boxlight Media Ltd. 2012
 * @license MIT License
 * @description Centers an image by moving, cropping and filling spaces inside it's parent container. Call
 * this on a set of images to have them fill their parent whilst maintaining aspect ratio
 * @author Robert Cambridge
 *
 * Usage: See documentation at http://boxlight.github.com/bl-jquery-image-center
 */
(function($) {
    $.fn.aTablesCenterImage = function(method, callback, width, speed) {
        callback = callback || function() {};
        var els = this;
        var remaining = $(this).length;
        speed = typeof speed !== "number" ? 400 : speed;

        // execute this on an individual image element once it's loaded
        var fn = function(img) {
            var 	$img = $(img),
					$div = $img.parent(),
					settings = $img.attr('data-wph-settings'),
					inside_centering = false; //outside centering by default

			if (settings) {
				var settings = JSON.parse(settings);
				if (settings.imageCenteringStyle && settings.imageCenteringStyle === "inside") inside_centering = true;
			}

            // parent CSS should be in stylesheet, but to reinforce:
            $div.css({
                overflow: 'hidden',
                position: $div.css('position') == 'absolute' ? 'absolute' : 'relative'
            });

            // cache original values to revert to after temp changes made for calculations
            img_original = {
                w: $img.width(),
                h: $img.height()
            };

            // temporarily set the image size naturally so we can get the aspect ratio
            $img.css({
                'position': 'static',
                'width': 'auto',
                'height': 'auto',
                'max-width': '100%',
                'max-height': '100%',
                'min-width': '0',
                'min-height': '0',
                'transition': '0s all'
            });

            // now resize
            var div = {
                w: width ? width : $div.width(),
                h: $div.height(),
                r: width ? width / $div.height() : $div.width() / $div.height()
            };
            var img = {
                w: $img.width(),
                h: $img.height(),
                r: $img.width() / $img.height()
            };

			var 	widthx = Math.round((div.r > img.r) ^ inside_centering ? '100%' : div.h / img.h * img.w),
					heightx = Math.round((div.r < img.r) ^ inside_centering ? '100%' : div.w / img.w * img.h);
			if (div.r === img.r) {
				widthx = div.w;
				heightx = div.h;
			}
			if (isNaN(heightx)) heightx = div.h 

            $img.css({
                'max-width': 'none',
                'max-height': 'none',
                'width': widthx,
                'height': heightx
            });
			
			/*
			if (inside_centering) {
				$img.css({
					'max-width': '100%',
					'max-height': '100%',
					'min-width': '0',
					'min-height': '0'
				});
			} else {
				$img.css({
					'max-width': 'none',
					'max-height': 'none',
					'min-width': '100%',
					'min-height': '100%'
				});
			}
			*/

            // now center - but portrait images need to be centred slightly above halfway (33%)
            var img_ = {
                w: $img.width(),
                h: $img.height()
            };

            $img.css({
                'position': 'absolute',
                'width': img_original.w,
                'height': img_original.h,
                'max-width': 'none',
                'max-height': 'none',
                'min-width': 0,
                'min-height': 0
            });

			var fix = $img.css('width'); // goes foobar without this!
/*
            // reduce animation lag
            if (Math.abs(img_.w - div.w) < 3) $img.css('min-width', '100%');
            if (Math.abs(img_.h - div.h) < 3) $img.css('min-height', '100%');
*/
            // animate to the new vals
            $img.css({
                'width': img_.w,
                'height': img_.h,
                'left': Math.round((div.w - img_.w) / 2),
                'top': Math.round((div.h - img_.h) / 2),
                'transition': (speed / 1000) + 's all'
            });

            callbackWrapped(img)
        };

        var callbackWrapped = function(img) {
            remaining--;
            callback.apply(els, [img, remaining]);
        };

        // iterate through elements
        return els.each(function(i) {
            if (this.complete || this.readyState === 'complete') {
                // loaded already? run fn
                // when binding, we can tell whether image loaded or not.
                // not if it's already failed though :(
				fn(this);
				/*
                (function(el) {
                    // use setTimeout to prevent browser locking up
                    setTimeout(function() {
                        fn(el)
                    }, 1);
                })(this);
				*/
            } else {
                // not loaded? bind to load
                (function(el) {
                    $(el)
                        .one('load', function() {
                            // use setTimeout to prevent browser locking up
                            setTimeout(function() {
                                fn(el);
                            }, 1);
                        })
                        .one('error', function() {
                            // the image did not load
                            callbackWrapped(el)
                        })
                        .end();

                    // IE9/10 won't always trigger the load event. fix it.
                    if (navigator.userAgent.indexOf("Trident/5") >= 0 || navigator.userAgent.indexOf("Trident/6")) {
                        el.src = el.src;
                    }
                })(this);
            }
        });
    };
    // Alias functions which often better describe the use case
    $.fn.aTablesImageCenterResize = function(callback, width) {
        return $(this).aTablesCenterImage('inside', callback, width);
    };
    $.fn.aTablesImageCropFill = function(callback, width) {
        return $(this).aTablesCenterImage('outside', callback, width);
    };
})(jQuery);