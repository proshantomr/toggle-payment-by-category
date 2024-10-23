jQuery(document).ready(function ($) {
	// Function to check visibility of payment methods based on categories
	function checkPaymentMethodVisibility() {
		// Gather selected categories
		var selectedCategories = $('select[name="tpbc_category[]"]').map(function () {
			return $(this).val();
		}).get();

		// Fetch current payment settings via AJAX
		var data = {
			action: 'get_payment_visibility',
			categories: selectedCategories,
			nonce: MYajax.nonce
		};

		$.ajax({
			url: MYajax.ajax_url,
			type: 'POST',
			data: data,
			success: function (response) {
				if (response.success) {
					// Show or hide payment methods based on response.
					$('input[name="radio-control-wc-payment-method-options"]').each(function () {
						var paymentMethod = $(this).val();
						if (response.data.hiddenMethods.includes(paymentMethod)) {
							$(this).prop('checked', false).parent().parent().hide(); // Hide method
						} else {
							$(this).parent().parent().show(); // Show method
						}
					});
				}
			},
			error: function () {
				console.log('Error fetching payment visibility.');
			}
		});
	}

	// Call the function on page load
	checkPaymentMethodVisibility();

	// Call the function when category selections change
	$(document).on('change', 'select[name="tpbc_category[]"]', function () {
		checkPaymentMethodVisibility();
	});
});
