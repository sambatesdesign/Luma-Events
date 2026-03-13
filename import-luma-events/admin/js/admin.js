/**
 * Admin JavaScript
 */
(function($) {
	'use strict';

	$(document).ready(function() {
		// Test API connection
		$('#ile-test-connection').on('click', function() {
			var $button = $(this);
			var $result = $('#ile-test-result');

			$button.prop('disabled', true).text('Testing...');
			$result.html('');

			$.ajax({
				url: ileAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ile_test_connection',
					nonce: ileAdmin.nonce
				},
				success: function(response) {
					if (response.success) {
						$result.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
					} else {
						$result.html('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>');
					}
				},
				error: function() {
					$result.html('<div class="notice notice-error inline"><p>Connection test failed. Please check your settings.</p></div>');
				},
				complete: function() {
					$button.prop('disabled', false).text('Test Connection');
				}
			});
		});
	});

})(jQuery);
