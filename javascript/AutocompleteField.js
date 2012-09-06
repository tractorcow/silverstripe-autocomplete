/**
 * Register Autocomplete functions with fields.
 * This is not strictly using jQuery, like the rest of the CMS.
 */

(function($) {
	$(function() {

		// Load autocomplete functionality when field gets focused
		$('.field.autocomplete input.text').live('focus', function() {
            
            // Prevent this field from loading itself multiple times
            if($(this).attr('data-loaded') == 'true')
                return;
            $(this).attr('data-loaded', 'true');
            
            // load autocomplete into this field
            $(this).autocomplete({
				source: $(this).attr('data-source'),
				minLength: $(this).attr('data-min-length')
			});
		});
	});
})(jQuery);