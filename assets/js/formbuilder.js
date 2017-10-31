;(function($) {

	var $formEditor = $('ul#cfm-formbuilder-fields');

	var Editor = {
		init: function() {

			// make it sortable
			this.makeSortable();

			this.tooltip();
			this.tabber();
			this.showHideHelp();
			this.conditionalLogic();

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

				// Re-call sortable
				Editor.makeSortable();

				// Enable tooltip
				Editor.tooltip();

				$('select.edd-select-chosen').chosen({
					inherit_select_classes: true,
					placeholder_text_multiple: edd_vars.one_or_more_option,
				}).css('width', '100%');

				$self.removeAttr('disabled');
				$('.cfm-button:not(:enabled):not([readonly])').each(function() {
					$(this).removeAttr('disabled');
				});
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
			$('.cfm-conditional-logic-repeatable-row .edd-select-chosen').css('width', '100%');
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
				$metaKey.val(val.replace(/\W/g, ""));
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
			$('.cfm-conditional-logic-repeatable-row .edd-select-chosen').css('width', '100%');
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
		},

		conditionalLogic: function () {
			$(document).on('change', '.cfm-toggle-conditional-logic-toggle', function() {
				$(this).parent().next().slideToggle();
				var conditionalLogicContainer = $(this).parent().next();
				$('select.edd-select-chosen', conditionalLogicContainer).chosen('destroy');
				$('select.edd-select-chosen', conditionalLogicContainer).chosen({
					inherit_select_classes: true,
					placeholder_text_multiple: edd_vars.one_or_more_option,
				});
			});

			$(document).on('change', '.cfm-conditional-logic-rule', function() {
				var value = $(this).val();
				var parent = $(this).parent();
				if (value === 'in_cart' || value === 'not_in_cart') {
					$('.cfm-conditional-logic-value , .cfm-conditional-logic-operator', parent).hide();
					$('.edd-select-chosen.chosen-container.cfm-conditional-logic-product-dropdown', parent).removeClass('hidden');
					$('.edd-select-chosen.chosen-container.cfm-conditional-logic-user-role-dropdown', parent).addClass('hidden');
				} else if (value === 'user_role') {
					$('.cfm-conditional-logic-value , .cfm-conditional-logic-operator', parent).hide();
					$('.edd-select-chosen.chosen-container.cfm-conditional-logic-product-dropdown', parent).addClass('hidden');
					$('.edd-select-chosen.chosen-container.cfm-conditional-logic-user-role-dropdown', parent).removeClass('hidden');
				} else {
					$('.cfm-conditional-logic-value , .cfm-conditional-logic-operator', parent).show();
					$('.edd-select-chosen.chosen-container', parent).addClass('hidden');
				}

				if (value === 'cart_amount') {
					$('.cfm-conditional-logic-operator').children('option[value="is_not"]').hide();
				} else {
					$('.cfm-conditional-logic-operator').children('option[value="is_not"]').show();
				}
			});

			$(document).on('click', '.cfm-conditional-logic-repeatable-row .edd-delete', function(e) {
				e.preventDefault();

				var container = $(this).parents('.cfm-conditional-logic-conditions'),
					row   = $(this).parents('.cfm-conditional-logic-repeatable-row'),
					count = row.parent().find('.cfm-conditional-logic-repeatable-row').length;

				if (count > 1) {
					$('input, select', row).val('');
					row.fadeOut('fast').remove();
				} else {
					alert(edd_vars.one_field_min);
				}

				$('.cfm-conditional-logic-repeatable-row', container).each( function(rowIndex) {
					$(this).attr('data-key', rowIndex);

					$(this).find('input, select').each(function() {
						var name = $(this).attr('name');
						var id   = $(this).attr('id');

						if ( name ) {
							name = name.replace( /\[rules\]\[(\d+)\]/, '[rules][' + rowIndex + ']');
							$(this).attr('name', name);
						}

						if (typeof id !== 'undefined') {
							id = id.replace( /(\d+)/, rowIndex );
							$(this).attr('id', id);
						}
					});
				});
			});

			$(document).on('click', '.cfm-conditional-logic-add-repeatable', function(e) {
				e.preventDefault();

				var container = $(this).parents('.cfm-conditional-logic-conditions');
				var div = $('.cfm-conditional-logic-repeatable-row', container).last();
				var highestKey = parseInt(div.attr('data-key'));
				var nextKey = highestKey + 1;

				div = $(e.target).parent().prev();

				div.find('select.edd-select-chosen').chosen('destroy');
				var clone = div.clone(true);
				div.find('select.edd-select-chosen').chosen({
					inherit_select_classes: true,
					placeholder_text_multiple: edd_vars.one_or_more_option,
				});

				var rule = div.find('.cfm-conditional-logic-rule').val();

				if ( 'in_cart' === rule || 'not_in_cart' === rule ) {
					div.find('.edd-select-chosen.chosen-container.cfm-conditional-logic-product-dropdown').removeClass('hidden');
				}

				if ( 'user_role' === rule ) {
					div.find('.edd-select-chosen.chosen-container.cfm-conditional-logic-user-role-dropdown').removeClass('hidden');
				}

				clone.attr('data-key', nextKey);
				clone.find('input, select').val('').each(function () {
					var name = $(this).attr('name');
					var id   = $(this).attr('id');

					if ( name ) {
						name = name.replace( /\[rules\]\[(\d+)\]/, '[rules][' + parseInt(nextKey) + ']');
						$(this).attr('name', name);
					}

					if (typeof id !== 'undefined') {
						id = id.replace( /(\d+)/, parseInt(nextKey) );
						$(this).attr('id', id);
					}
				});

				div.after(clone);
				clone.find('select.edd-select-chosen').chosen({
					inherit_select_classes: true,
					placeholder_text_multiple: edd_vars.one_or_more_option,
				}).css('width', '100%').addClass('hidden');
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