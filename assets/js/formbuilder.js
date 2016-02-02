;(function($) {

	var $formEditor = $('ul#cfm-formbuilder-fields');

	var Editor = {
		init: function() {

			// make it sortable
			this.makeSortable();

			this.tooltip();
			this.tabber();
			this.showHideHelp();

			// on save validation
			$('form#post').submit(function(e) {
				var errors = false;
				var regexp = /^[a-zA-Z0-9_-]+$/; // metakeys can only be upperloweralpha + numeric + underscore
				$('li.custom-field input[data-type="label"]').each( function(index) {
					if ($(this).val().length === 0 ) {
						errors = true;
						$(this).css('border', '3px solid #993333');
					}
				});

				$('li.custom-field input[data-type="metakey"]').each( function(index) {
					var thatstring = $.trim($(this).val());
					if ( ( thatstring.length === 0 ) || ( !regexp.test(thatstring) ) ) {
						errors = true;
						$(this).css('border', '3px solid #993333');
					}
				});

				if (errors) {
					e.preventDefault();
					alert( 'Please fix the errors to save the form.' );
					return false;
				}
			});

			// collapse all
			$('button.cfm-collapse').on('click', this.collapseEditFields);

			// add field click
			$('.cfm-form-buttons').on('click', 'button', this.addNewField);

			// remove form field
			$('ul#cfm-formbuilder-fields').on('click', '.cfm-remove', this.removeFormField);

			// on change event: meta key
			$('ul#cfm-formbuilder-fields').on('blur', 'li.custom-field input[data-type="label"]', this.setMetaKey);

			// on change event: checkbox|radio fields
			$('ul#cfm-formbuilder-fields').on('change', '.cfm-form-sub-fields input[type=text]', function() {
				$(this).prev('input[type=checkbox], input[type=radio]').val($(this).val());
			});

			// on change event: checkbox|radio fields
			$('ul#cfm-formbuilder-fields').on('click', 'input[type=checkbox].multicolumn', function() {
				// $(this).prev('input[type=checkbox], input[type=radio]').val($(this).val());
				var $self = $(this),
					$parent = $self.closest('.cfm-form-rows');

				if ($self.is(':checked')) {
					$parent.next().hide().next().hide();
					$parent.siblings('.column-names').show();
				} else {
					$parent.next().show().next().show();
					$parent.siblings('.column-names').hide();
				}
			});

			// on change event: checkbox|radio fields
			$('ul#cfm-formbuilder-fields').on('click', 'input[type=checkbox].retype-pass', function() {
				// $(this).prev('input[type=checkbox], input[type=radio]').val($(this).val());
				var $self = $(this),
					$parent = $self.closest('.cfm-form-rows');

				if ($self.is(':checked')) {
					$parent.next().show().next().show();
				} else {
					$parent.next().hide().next().hide();
				}
			});

			// toggle form field
			$('ul#cfm-formbuilder-fields').on('click', '.cfm-toggle', this.toggleFormField);

			// clone and remove repeated field
			$('ul#cfm-formbuilder-fields').on('click', 'img.cfm-clone-field', this.cloneField);
			$('ul#cfm-formbuilder-fields').on('click', 'img.cfm-remove-field', this.removeField);
		},

		showHideHelp: function() {
			var childs = $('ul#cfm-formbuilder-fields').children('li');

			if ( !childs.length) {
				$('.cfm-updated').show();
			} else {
				$('.cfm-updated').hide();
			}
		},

		makeSortable: function() {
			$formEditor = $('ul#cfm-formbuilder-fields');

			if ($formEditor) {
				$formEditor.sortable({
					placeholder: "ui-state-highlight",
					handle: '> .cfm-legend',
					distance: 5
				});
			}
		},

		addNewField: function(e) {
			e.preventDefault();

			var $self = $(this),
				$formEditor = $('ul#cfm-formbuilder-fields'),
				name = $self.data('name'),
				type = $self.data('type'),
				id   = $self.data('formid'),
				data = {
					name: name,
					type: type,
					id: id,
					order: $formEditor.find('li').length + 1,
					action: 'cfm_formbuilder'
				};

			// check if these are already inserted
			var oneInstance = ['first_name', 'last_name', 'user_email'];

			if ($.inArray(name, oneInstance) >= 0) {
				if ( $formEditor.find('li.' + name).length ) {
					alert('You already have this field in the form');
					return false;
				}
			}

			var buttonText = $self.text();
			$self.html('<div class="cfm-loading"></div>');
			$self.attr('disabled', 'disabled');
			$('.cfm-button:not(:disabled):not([readonly])').each(function() {
				$(this).attr('disabled', 'disabled');
			})

			$.post(ajaxurl, data, function(res) {
				$formEditor.append(res);

				// re-call sortable
				Editor.makeSortable();

				// enable tooltip
				Editor.tooltip();

				$self.removeAttr('disabled');
				$('.cfm-button:not(:enabled):not([readonly])').each(function() {
					$(this).removeAttr('disabled');
				})
				$self.text(buttonText);
				Editor.showHideHelp();
			});
		},

		removeFormField: function(e) {
			e.preventDefault();

			if (confirm('Are you sure?')) {

				$(this).closest('li').fadeOut(function() {
					$(this).remove();

					Editor.showHideHelp();
				});
			}
		},

		toggleFormField: function(e) {
			e.preventDefault();

			$(this).closest('li').find('.cfm-form-holder').slideToggle('fast');
		},

		cloneField: function(e) {
			e.preventDefault();

			var $div = $(this).closest('div');
			var $clone = $div.clone();
			// console.log($clone);

			//clear the inputs
			$clone.find('input').val('');
			$clone.find(':checked').attr('checked', '');
			$div.after($clone);
		},

		removeField: function() {
			//check if it's the only item
			var $parent = $(this).closest('div');
			var items = $parent.siblings().andSelf().length;

			if ( items > 1 ) {
				$parent.remove();
			}
		},

		setMetaKey: function() {
			var $self = $(this),
				val = $self.val().toLowerCase().split(' ').join('_').split('\'').join(''),
				$metaKey = $(this).closest('.cfm-form-rows').next().find('input[type=text]');

			if ($metaKey.length && $metaKey.val() == '' ) {
				$metaKey.val(val);
			}
		},

		tooltip: function() {
			$('.smallipopInput').smallipop({
				preferredPosition: 'right',
				theme: 'black',
				popupOffset: 0,
				triggerOnClick: true
			});
		},

		collapseEditFields: function(e) {
			e.preventDefault();

			$('ul#cfm-formbuilder-fields').children('li').find('.cfm-form-holder').slideToggle();
		},

		tabber: function() {
			// Switches option sections
			$('.group').hide();
			$('.group:first').fadeIn();

			$('.group .collapsed').each(function(){
				$(this).find('input:checked').parent().parent().parent().nextAll().each(
				function(){
					if ($(this).hasClass('last')) {
						$(this).removeClass('hidden');
						return false;
					}
					$(this).filter('.hidden').removeClass('hidden');
				});
			});

			$('.nav-tab-wrapper a:first').addClass('nav-tab-active');

			$('.nav-tab-wrapper a').click(function(evt) {
				var clicked_group = $(this).attr('href');
				if ( clicked_group.indexOf( '#' ) >= 0 ) {
					evt.preventDefault();
					$('.nav-tab-wrapper a').removeClass('nav-tab-active');
					$(this).addClass('nav-tab-active').blur();
					$('.group').hide();
					$(clicked_group).fadeIn();
				}
			});
		}
	};

	// on DOM ready
	$(function() {
		Editor.init();
		$( "#cfm-metabox-fields-custom.postbox" ).removeClass( "closed" );
		$( "#cfm-metabox-fields-extension.postbox") .addClass( "closed" );
	});

})(jQuery);