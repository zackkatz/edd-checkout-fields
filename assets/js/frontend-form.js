(function ($) {
	var CFM_Form = {
		init: function () {
			// clone and remove repeated field
			$('body').on('click', 'img.cfm-clone-field', this.cloneField);
			$('body').on('click', 'img.cfm-remove-field', this.removeField);

			// Frontend checkout submission
			$('body').on('submit', '#edd_purchase_form', this.formSubmit);
			// admin payment submission
			$('form#post').on('submit', this.adminPostSubmit);

			// download links
			$('body').on('click', 'a.upload_file_button', this.fileDownloadable);

			// Repeatable file inputs
			$('body').on('click', 'a.insert-file-row', function (e) {
				e.preventDefault();
				var clickedID = $(this).attr('id');
				var max = $('#cfm-upload-max-files-'+clickedID ).val();
				var optionContainer = $('.cfm-variations-list-'+clickedID);
				var option = optionContainer.find('.cfm-single-variation:last');
				var newOption = option.clone();
				delete newOption[1];
				newOption.length = 1;
				var count = optionContainer.find('.cfm-single-variation').length;

				// too many files
				if ( count + 1 > max && max != 0 ){
					return alert(cfm_form.too_many_files_pt_1 + max + cfm_form.too_many_files_pt_2);
				}

				newOption.find('input, select, textarea').val('');
				newOption.find('input, select, textarea').each(function () {
					var name = $(this).attr('name');
					name = name.replace(/\[(\d+)\]/, '[' + parseInt(count) + ']');
					$(this)
						.attr('name', name)
						.attr('id', name);

					newOption.insertBefore("#"+clickedID);
				});
				return false;
			});

			$('body').on('click', 'a.edd-cfm-delete', function (e) {
				e.preventDefault();
				var option = $(this).parents('.cfm-single-variation');
				var optionContainer = $(this).parents('[class^=cfm-variations-list-]');
				var count = optionContainer.find('.cfm-single-variation').length;

				if (count == 1) {
					option.find('input, select, textarea').val('');
					return false;
				} else {
					option.remove();
					return false;
				}
			});
		},

		fileDownloadable: function (e) {
			e.preventDefault();

			var self = $(this),
				downloadable_frame;

			if (downloadable_frame) {
				downloadable_frame.open();
				return;
			}

			downloadable_frame = wp.media({
				title: cfm_form.file_title,
				frame: 'select',
				button: {
					text: cfm_form.file_button
				},
				multiple: false
			});

			downloadable_frame.on('open',function() {
				// turn on file filter
				var fid   = self.closest('tr').find('input.cfm-file-value').attr("data-formid");
				var fname = self.closest('tr').find('input.cfm-file-value').attr("data-fieldname");
				$.post(cfm_form.ajaxurl,{ action:'cfm_turn_on_file_filter', formid: fid, name: fname }, function (res) { });
			});

			downloadable_frame.on('close',function() {
				// turn on file filter
				var fid   = self.closest('tr').find('input.cfm-file-value').attr("data-formid");
				var fname = self.closest('tr').find('input.cfm-file-value').attr("data-fieldname");
				$.post(cfm_form.ajaxurl,{ action:'cfm_turn_off_file_filter', formid: fid, name: fname }, function (res) { });
			});

			downloadable_frame.on('select', function () {
				var selection = downloadable_frame.state().get('selection');

				selection.map(function (attachment) {
					attachment = attachment.toJSON();

					self.closest('tr').find('input.cfm-file-value').val(attachment.url);
				});
			});

			downloadable_frame.open();
		},

		cloneField: function (e) {
			e.preventDefault();

			var $div = $(this).closest('tr');
			var $clone = $div.clone();
			var $trs = $div.parent().find('tr');

			var key = highest = 0;
			$trs.each(function() {
				var current = $(this).data( 'key' );
				if ( parseInt( current ) > highest ) {
					highest = current;
				}
			});
			key = highest + 1;

			//clear the inputs
			$clone.attr( 'data-key', parseInt( key ) );
			$clone.find(':checked').attr('checked', '');
			$clone.find('input, select, textarea').val('');
			$clone.find('input, select, textarea').each(function () {
				var name = $(this).attr('name');
				name = name.replace(/\[(\d+)\]/, '[' + parseInt(key) + ']');
				$(this).attr('name', name).attr('id', name);
			});

			$div.after($clone);
		},

		removeField: function () {
			//check if it's the only item
			var $parent = $(this).closest('tr');
			var items = $parent.siblings().andSelf().length;

			if (items > 1) {
				$parent.remove();
			}
		},

		hasItems: function (map) {
		   for(var key in map) {
			  if (map.hasOwnProperty(key)) {
				 return true;
			  }
		   }
		   return false;
		},

		adminPostSubmit: function(e) {
			var form = $(this),
				form_data = CFM_Form.validateForm(form);
		},

		formSubmit: function(e) {

			var form = $(this),
				submitButton = form.find('input[type=submit]')
				form_data = CFM_Form.validateForm(form);

			if(form_data) {
				return true;
			} else {
				// Prevent the form from submitting is there are errors
				e.preventDefault();
			}

		},

		validateForm: function (self) {
			var temp,
				form_data = self.serialize(),
				rich_texts = [];

			// grab rich texts from tinyMCE
			$('.cfm-rich-validation').each(function (index, item) {
				temp = $(item).data('id');
				val = $.trim(tinyMCE.get(temp).getContent());
				rich_texts.push(temp + '=' + encodeURIComponent(val));
			});

			// append them to the form var
			form_data = form_data + '&' + rich_texts.join('&');
			return form_data;
		},
	};

	$(function () {
		CFM_Form.init();
	});

})(jQuery);
webshim.setOptions('forms-ext', {
	replaceUI: {
		date: true,
		datetime: true
	},
	types: 'date datetime',
	date: {
		openOnFocus: true,
	},
	datetime: {
		openOnFocus: true,
	}
});
//start polyfilling
webshim.polyfill('forms forms-ext');