/* global jQuery, BWGAdmin */
(function ($) {
	'use strict';

	$(document).ready(function () {
		var testBtn    = document.getElementById('bwg-test-api');
		var testResult = document.getElementById('bwg-test-result');
		if (!testBtn) return;

		testBtn.addEventListener('click', function () {
			testBtn.disabled    = true;
			testBtn.textContent = 'Testowanie…';
			testResult.style.display = 'none';
			testResult.className     = 'bwg-test-result';

			$.ajax({
				url:  BWGAdmin.ajax_url,
				type: 'POST',
				data: {
					action:  'bwg_test_api',
					nonce:   BWGAdmin.nonce,
					api_key: (document.getElementById('bwg_gemini_api_key') || {}).value || '',
				},
				success: function (response) {
					testResult.style.display = 'inline';
					if (response.success) {
						testResult.textContent = '✓ ' + response.data.message;
						testResult.classList.add('bwg-test-result--ok');
					} else {
						testResult.textContent = '✗ ' + (response.data && response.data.message ? response.data.message : 'Błąd');
						testResult.classList.add('bwg-test-result--error');
					}
					testBtn.disabled    = false;
					testBtn.textContent = 'Testuj klucz API';
				},
				error: function () {
					testResult.style.display = 'inline';
					testResult.textContent   = '✗ Błąd połączenia';
					testResult.classList.add('bwg-test-result--error');
					testBtn.disabled    = false;
					testBtn.textContent = 'Testuj klucz API';
				},
			});
		});
	});

})(jQuery);
