/**
 * Register Autocomplete functions with fields.
 */

(function($) {
	$.entwine('ss.autocomplete', function($){

		$('.field.autocomplete input.text').entwine({

    		onmatch: function() {
                // Initialize jQuery selectors for frequently accessed elements.
    			var $input = $(this);
    			var $hiddenInput = $input.parent().find(':hidden');
    			var $valueHolder = $input.parent().find('.value-holder');
    			var $valueEl = $valueHolder.find('.value');

                // Load server-side configuration.
                var popSeparate = !!$input.data('popSeparate');
                var clearInput = !!$input.data('clearInput') && popSeparate;
                var requireSelection = !!$input.data('requireSelection');
                var source = $input.data('source');
                var minLength = parseInt($input.data('minLength'));

    			var updateField = function(ui){
                    var value = $input.val();

        			if (value) {

        				// Accept if item selected from list
        				if(ui.item) {
                            setFieldValue(ui.item.stored, ui.item.label);

                            if (clearInput) {
                                // Reset input field, if specified.
                                $input.val('');

                            } else if (!popSeparate) {
                                // Place label inside input field.
                                $input.val(ui.item.label);
                            }
                        }

                        // Check if a selection from the list is required
                        else if(!requireSelection) {
                            // free text is allowed, use it
                            setFieldValue(value, value);

                            if (clearInput) {
                                // Reset input field, if specified.
                                $input.val('');
                            }

        				} else {
        				    // Free text is not allowed so clear field values now.
        				    clearField();
        				}

        			} else {
        			    clearField();
        			}


                    // Persist search term
    				return false;
    			};

    			var setFieldValue = function(value, label){
					$hiddenInput.val(value);
					if (popSeparate) {
                        $valueEl.text(label).effect('highlight');
                        $valueHolder.addClass('has-value');
					}
    			};

    			var clearField = function(){
    				$hiddenInput.val('');
    				$input.val('');
    				if (popSeparate) {
                        $valueEl.text($valueEl.data('emptyVal'));
                        $valueHolder.removeClass('has-value');
    				}
    			};

    			// Prevent this field from loading itself multiple times
    			if($input.attr('data-loaded') == 'true')
    				return;
    			$input.attr('data-loaded', 'true');

    			// load autocomplete into this field
    			$input.autocomplete({
    				source: source,
    				minLength: minLength,
    				change: function( event, ui ) {
    					return updateField(ui);
    				},
    				select: function( event, ui ) {
    					return updateField(ui);
    				}
    			});

    			// Allow clearing of selection
    			$input.parent().find('a.clear').click(function(e){
        			e.preventDefault();
        			clearField();
    			});

                $input.focus(function() {
                    // Trigger a search on click/focus if the field contains a value
                    if ($input.val().length >= minLength) {
                        $input.autocomplete('search');
                    }
                });
    		}

		});
	});
})(jQuery);
