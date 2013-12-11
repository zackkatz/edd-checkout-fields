var EDDFES = (function($) {
	var $ = jQuery;

	function addoption() {
		var optionContainer = $( '.edd-fes-adf-submission-options' );
		var option          = optionContainer.find( '.edd-fes-adf-submission-option.static' );

		$( '.edd-fes-adf-submission-add-option-button' ).click(function(e) {
			e.preventDefault();

			var newOption = option.clone();
			var count     = optionContainer.find( '.edd-fes-adf-submission-option' ).length;

			newOption.removeClass( 'static' );
			newOption.find( 'input, select, textarea' ).val( '' );
			newOption.find( 'input, select, textarea' ).each(function() {
			
				var name  = $( this ).attr( 'name' );
				
				name  = name.replace( /\[(\d+)\]/, '[' + parseInt( count - 1 ) + ']');


				$( this )
					.attr( 'name', name )
					.attr( 'id', name );

				newOption.insertBefore( $( '.edd-fes-adf-submission-add-option' ) );
			});
		});
	}

	function removeoption() {
		$( 'body' ).on( 'click', '.edd-fes-adf-submission-option-remove a', function(e) {
			e.preventDefault();

			var option          = $( this ).parents( '.edd-fes-adf-submission-option' );
			var optionContainer = $( '.edd-fes-adf-submission-options' );
			var count           = optionContainer.find( '.edd-fes-adf-submission-option' ).length;

			if ( count == 1 || option.hasClass( 'static' ) )
				return alert( EDDFESL10n.oneoption );

			option.remove();
		});
	}

	function validate() {
		$( '.edd-fes-adf-submission' ).validate({
			errorPlacement: function(error, element) {},
			rules: {
				"title" : {
					required : true
				},
				"options[0][name]" : {
					required : true
				},
				"options[0][price]" : {
					required : true
				},
			},
			submitHandler: function(form) {
				form.submit();
			}
		});
	}
	
	function files() {
			if( typeof wp == "undefined" || EDDFESL10n.new_media_ui != '2' ){
				//Old Thickbox uploader
				if ( $( '.edd_upload_image_button' ).length > 0 ) {
					window.formfield = '';

					$('body').on('click', '.edd_upload_image_button', function(e) {
						e.preventDefault();
						window.formfield = $(this).parent().prev();
						window.tbframe_interval = setInterval(function() {
							jQuery('#TB_iframeContent').contents().find('.savesend .button').val(EDDFESL10n.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
							}, 2000);
						if (EDDFESL10n.post_id != null ) {
							var post_id = 'post_id=' + EDDFESL10n.post_id + '&';
						}
						tb_show(EDDFESL10n.add_new_download, EDDFESL10n.admin_ajax_url + 'media-upload.php?' + post_id +'TB_iframe=true');
					});

					window.edd_send_to_editor = window.send_to_editor;
					window.send_to_editor = function (html) {
						if (window.formfield) {
							imgurl = $('a', '<div>' + html + '</div>').attr('href');
							window.formfield.val(imgurl);
							window.clearInterval(window.tbframe_interval);
							tb_remove();
						} else {
							window.edd_send_to_editor(html);
						}
						window.send_to_editor = window.edd_send_to_editor;
						window.formfield = '';
						window.imagefield = false;
					}
				}
			} else {
				if (EDDFESL10n.post_id != null ) {
					var post_id = 'post_id=' + EDDFESL10n.post_id + '&';
				}
				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';

				$('body').on('click', '.edd_upload_image_button', function(e) {

					e.preventDefault();

					var button = $(this);

					window.formfield = $(this).closest('.edd_repeatable_upload_field_container');

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
						file_frame.open();
					  return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media({
						frame: 'post',
						state: 'insert',
						title: button.data( 'uploader_title' ),
						button: {
							text: button.data( 'uploader_button_text' ),
						},
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

						var selection = file_frame.state().get('selection');
						selection.each( function( attachment, index ) {
							attachment = attachment.toJSON();
							window.formfield.find('.edd_repeatable_upload_field').val(attachment.url);
						});
					});

					// Finally, open the modal
					file_frame.open();
				});


				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';
			}

		}

	return {
		init : function() {
			addoption();
			removeoption();
			validate();
			files();
		}
	}
}(jQuery));

jQuery(document).ready(function($) {
	EDDFES.init();
});