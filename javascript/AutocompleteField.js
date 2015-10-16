/**
 * Register Autocomplete functions with fields.
 * This is not strictly using jQuery, like the rest of the CMS.
 */

(function($) {
	$(function() {

		// Load autocomplete functionality when field gets focused
		$('.field.autocomplete input.text').live('focus', function() {

			var input = $(this);

			// Prevent this field from loading itself multiple times
			if(input.attr('data-loaded') == 'true')
				return;
			input.attr('data-loaded', 'true');

			// load autocomplete into this field
			input.autocomplete({
				source: input.attr('data-source'),
				minLength: input.attr('data-min-length'),
				change: function( event, ui ) {
					var hiddenInput = input.parent().find(':hidden');

					// Accept if item selected from list
					if(ui.item) {
						hiddenInput.val(ui.item.stored);
						return true;
					}

					// Check if a selection from the list is required
					if(!input.attr('data-require-selection')) {
						// free text is allowed, use it
						hiddenInput.val(input[0].value);

						return true;
					}

					// remove invalid value, as it didn't match anything
					input.val("");
					input.data("autocomplete").term = "";
					return false;
				}
			});
		});
	});
})(jQuery);
