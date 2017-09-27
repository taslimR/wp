jQuery( function( $ ){
	
	var wph_start_loader = function ( elm, callback ){
		if (!elm) $('.wph_editor_save').removeClass('fa-save').addClass('fa-spin fa-refresh');
		else {
			if (!elm.children('i').length) elm.prepend('<i class="fa fa-spin fa-refresh" style="width:0; overflow:hidden; display:inline-block;"></i>')
			elm.children('i').stop().animate({'margin-right':5, 'opacity':1, 'width': 10});
			if (callback) callback();
		}
	}

	/*Stop Loader*/
	var wph_stop_loader = function ( elm, callback ){
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

	/*License Activation*/
	wph_license_manager = function  () {
		var  form = $('.wph_license_form'),
				action_type = form.find('[name="action type"]').val(),
				plugin_name = form.find('[name="plugin name"]').val(),
				plugin_prefix = form.find('[name="plugin prefix"]').val(),
				username = form.find('[name="username"]').val(),
				api_key = form.find('[name="api key"]').val(),
				purchase_code = form.find('[name="purchase code"]').val(),
				license_key = form.find('[name="license key"]').val(),
				mode = form.find('[name="mode"]').val();
				
	   $.ajax({
			type : "post",
			dataType : "json",
			url : wph_ajax.url,
			beforeSend: function () {
				wph_start_loader($('.wph_license_manager'));
			},
			data : {
					action:"wph_license_manager", 
					action_type: action_type, 
					nonce: wph_ajax.nonce, 
					plugin_name: plugin_name, 
					plugin_prefix: plugin_prefix, 
					username: username, 
					api_key: api_key, 
					purchase_code: purchase_code, 
					license_key:license_key, 
					mode : mode 
				},
			success: function (response) {
				alert(response.message);
				if (response.result === "success") {
					form.slideUp('fast');
				}
			},
			complete: function () {
				wph_stop_loader($('.wph_license_manager'));
			}
		});
	}
	$('.wph_license_manager').click(wph_license_manager);	

} )