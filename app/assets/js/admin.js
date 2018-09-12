(function($){
	
	$('.upload_avatar_button').on('click', function( e ){

		e.preventDefault();
 
		var button = $(this),
			custom_uploader = wp.media({
				title: 'Insert image',
				library : {
					type : 'image'
				},
				button: {
					text: 'Use this image'
				},
				multiple: false
			}).on('select', function() {

				var attachment = custom_uploader.state().get('selection').first().toJSON();
				
				$(button).removeClass('button').html('<img class="true_pre_image" src="' + attachment.url + '" style="max-width:95%;display:block;" />').next().val(attachment.id).next().show();
			})
			.open();
	});
 

	$('.remove_avatar_button').on('click', function(){

		$(this).hide().prev().val('').prev().addClass('button').html('Upload avatar');

		return false;
	});
 
}) (jQuery);