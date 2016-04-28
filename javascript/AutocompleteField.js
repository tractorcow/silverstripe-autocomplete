/**
 * Register Autocomplete functions with fields.
 */

(function($) {
	$.entwine('ss.autocomplete', function($){

		$('.field.autocomplete input.text').entwine({

    		onmatch: function() {

    			var input = $(this);
    			var hiddenInput = input.parent().find(':hidden');
    			var valueHolder = input.parent().find('.value-holder');
    			var valueEl = input.parent().find('.value-holder .value');

    			var updateField = function(ui){

        			if (input[0].value) {

        				// Accept if item selected from list
        				if(ui.item) {
                            setFieldValue(ui.item.stored, ui.item.label);
        				}

        				// Check if a selection from the list is required
        				else if(!input.attr('data-require-selection')) {
        					// free text is allowed, use it
                            setFieldValue(input[0].value, input[0].value);
        				}
        			}

                    // Persist search term
    				return false;
    			};

    			var setFieldValue = function(value, label){
					hiddenInput.val(value);
					valueEl.text(label).effect('highlight');
                    valueHolder.addClass('has-value');
    			};

    			var clearField = function(){
    				hiddenInput.val('');
    				valueEl.text(valueEl.data('emptyVal'));
    				valueHolder.removeClass('has-value');
    			};

    			// Prevent this field from loading itself multiple times
    			if(input.attr('data-loaded') == 'true')
    				return;
    			input.attr('data-loaded', 'true');

    			// load autocomplete into this field
    			input.autocomplete({
    				source: input.attr('data-source'),
    				minLength: input.attr('data-min-length'),
    				change: function( event, ui ) {
    					return updateField(ui);
    				},
    				select: function( event, ui ) {
    					return updateField(ui);
    				}
    			});

    			// Allow clearing of selection
    			input.parent().find('a.clear').click(function(e){
        			e.preventDefault();
        			clearField();
    			});
    		},

    		onfocusin: function() {
        		// Trigger a search on click/focus if the field contains a value
        		var input = $(this);
        		if (input.val().length >= input.attr('data-min-length')) {
        		    $(this).autocomplete('search');
        		}
    		}
		});
	});
})(jQuery);
