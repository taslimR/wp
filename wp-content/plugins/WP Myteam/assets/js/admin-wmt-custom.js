jQuery(document).ready(function($) {
	$(document).on( 'click', function(e){ // click event listener
		
		if( $(e.target).hasClass( 'fptadd' )){
			e.preventDefault();
			$clone = $(e.target).parents('.wrap-desc').clone();
			$clone.find("input:text").val("");
			$parent = $(e.target).parent('.wrap-desc').parent('td');
			$parent.find( '.wrap-desc' ).last().after($clone);
		}
		if( $(e.target).hasClass( 'fptremove' )){ 
			e.preventDefault();
			$parent = $(e.target).parent('.wrap-desc').parent('td');
			if( $parent.find( '.wrap-desc' ).length > 1 )
			$(e.target).parents('.wrap-desc').remove();
			else
			alert('Minimum 1 description necessary.');
		}
	});
	$(document).on( 'click', function(e){ // click event listener
		if( $(e.target).hasClass('cust-style') ){
			e.preventDefault();
			var arr = [17,18,19,20,21,22,23,24,31,40];
			var cval = $(e.target).val();
			$val = $.inArray( parseInt( cval ) );
			
			var compare = $.inArray(parseInt(cval), arr);
			
			if( compare != -1 ){
				$(".number-column option[value='4']").val(4).attr('selected', 'selected');
				$('.number-column').attr( 'disabled', true );
				$('.wmt-tab li').hide();
				$('.wmt-tab li:lt(4)').show();
				if( $('.form-table').find('span.tmember').length == 0 ){
					$('.cust-style').after('<span class="tmember">Style has 4 Team members</span>');	
				}
				
			}else{
				$('.number-column').attr( 'disabled', false );
				$('.form-table').find('span.tmember').remove();
			}
		}
	});	
	$('span.fa').click(function(){
		$('.iconpicker .selected').removeClass('selected');
		$(this).addClass('selected');
	});

	$(document).on('click', '.wmt_icon_choose', function(e){
		$target_icon = $(e.target).prev();
        e.preventDefault();
        $('.iconpicker').dialog({
            width:360,
            height:270,
            close: function( event, ui ) {
                jQuery( this ).dialog( "destroy" );
            },
            buttons: {
                'Ok': function () {
                	$selected = $('.iconpicker').find('.selected').removeClass('selected');
                	$target_icon.val( $selected.attr('class') );
                    jQuery(this).dialog('close');
                    
                    return false;
                },
                'Cancel': function () {
                    jQuery(this).dialog('close');
                },
            }
        });
    });

    $(document).on('click', '.wmt-social-wrapper .fa-plus', function(){

    	$clone = $(this).parents('.wmt-social-wrapper').clone();
    	
    	$(this).closest( 'table' ).append( $clone );
    });

    $(document).on('click', '.wmt-social-wrapper .fa-minus', function(){
    	$(this).parents('.wmt-social-wrapper').remove();
    });	

	$('.wmt-tab li').click(function(){
		$('.wmt-tab li').removeClass('active');
		$(this).addClass('active');
		$('.price-panel-body div').removeClass('active');
		$('.price-panel-body div').removeAttr('style');
		$tab = $(this).data('tab');
		$('#'+$tab ).removeAttr('style');
		$('#'+$tab ).addClass('active');
	});
	
	$tab = $('.wmt-tab li.active').data('tab');
	$('#'+$tab ).addClass('active');

	$current_column = $('select[name="wmt_option[wmt_columns]"]').val();
	$('.wmt-tab li').hide();
	$('.wmt-tab li:lt(' + $current_column + ')').show();

	$('select[name="wmt_option[wmt_columns]"]').on('change',function(){
		$('.price-panel-body div').hide();
		$('.price-panel-body div:lt('+$(this).val()+')').show();
		$('.wmt-tab li').hide();
		$('.wmt-tab li:lt(' + $(this).val() + ')').show();
	});

	//Image Uploader as per wordpress version
	jQuery( document ).on('click', '.wmt-img-uploader-1,.wmt-img-uploader-2,.wmt-img-uploader-3,.wmt-img-uploader-4,.wmt-img-uploader-5,.wmt-img-uploader-6,.wmt-img-uploader-bg-image', function() {
		
		var imgfield,showimgfield;
		imgfield = jQuery(this).prev('input');
		showimgfield = jQuery(this).next().next().next('div'); //show uploaded image
    	
		if(typeof wp == "undefined" || WMTSettings.new_media_ui != '1' ){// check for media uploader
				
			tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	    	
			window.original_send_to_editor = window.send_to_editor;
			window.send_to_editor = function(html) {
				
				if(imgfield.attr('id'))  {
					
					var mediaurl = jQuery('img',html).attr('src');
					imgfield.val(mediaurl);
					showimgfield.html('<img src="'+mediaurl+'" alt="Image" />');
					tb_remove();
					imgfield = '';
					
				} else {
					
					window.original_send_to_editor(html);
					
				}
			};
	    	return false;
			
		      
		} else {
			
			var file_frame;
			
			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				file_frame.open();
			  return;
			}
	
			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				frame: 'post',
				state: 'insert',
				multiple: false  // Set to true to allow multiple files to be selected
			});
	
			file_frame.on( 'menu:render:default', function(view) {
		        // Store our views in an object.
		        var views = {};
	
		        // Unset default menu items
		        view.unset('library-separator');
		        view.unset('gallery');
		        view.unset('featured-image');
		        view.unset('embed');
	
		        // Initialize the views in our view object.
		        view.set(views);
		    });
	
			// When an image is selected, run a callback.
			file_frame.on( 'insert', function() {
	
				// Get selected size from media uploader
				var selected_size = jQuery('.attachment-display-settings .size').val();
				
				var selection = file_frame.state().get('selection');
				selection.each( function( attachment, index ) {
					attachment = attachment.toJSON();
					
					// Selected attachment url from media uploader
					var attachment_url = attachment.sizes[selected_size].url;
					
					if(index == 0){
						// place first attachment in field
						imgfield.val(attachment_url);
						showimgfield.html('<img src="'+attachment_url+'" alt="Image" />');
					} else{
						imgfield.val(attachment_url);
						showimgfield.html('<img src="'+attachment_url+'" alt="Image" />');
					}
				});
			});
	
			// Finally, open the modal
			file_frame.open();
			
		}
		
	});
});			