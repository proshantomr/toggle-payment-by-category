/******/ (function() { // webpackBootstrap
	/*!*************************!*\
	  !*** ./src/js/admin.js ***!
	  \*************************/
// admin.js

	jQuery(document).ready(function ($) {

		$('.add-new-button').on('click', function () {
			var table = $('#payment-table');
			var templateRow = table.find('.template-row').clone(); // Clone the template row
			templateRow.removeClass('template-row').show(); // Remove the template class and show the row

			// Reset all select inputs and checkboxes in the cloned row
			templateRow.find('select').each(function () {
				$(this).val(''); // Reset all select inputs to empty
			});
			templateRow.find('input[type="checkbox"]').prop('checked', false).val('show'); // Reset checkbox and set value to 'show'

			// Append the new row to the table body
			table.find('tbody').append(templateRow);
		});

		// Handle the delete button click using event delegation
		$('#payment-table').on('click', '.delete-button', function () {
			var row = $(this).closest('tr'); // Find the closest row
			// Check if the row is not the only one remaining
			if ($('#payment-table tbody tr').length > 1) {
				if (confirm('Are you sure you want to delete this row?')) {
					row.remove(); // Remove the row if confirmed
				}
			} else {
				alert('Cannot delete the last row.'); // Alert if trying to delete the last row
			}
		});

		// Before form submission, remove the template row to avoid it being submitted
		$('form.product-catalog-mode-form').on('submit', function () {
			$('#payment-table').find('.template-row').remove(); // Remove the template row before submitting the form
		});
	});
	(function ($) {
		function toogleSwitch(value, id) {
			if (value == 'hide') {
				$('#payment_visibility_' + id).val('show');
				$('#payment_visibility_h_' + id).val('show');
			} else {
				$('#payment_visibility_' + id).val('hide');
				$('#payment_visibility_h_' + id).val('hide');
			}
		}

		// Attach the function to the global scope if necessary
		window.toogleSwitch = toogleSwitch;
	})(jQuery);
	/******/ })()
;
//# sourceMappingURL=admin.js.map
