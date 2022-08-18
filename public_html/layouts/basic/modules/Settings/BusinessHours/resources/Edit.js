/**
 * Business Hours Edit
 *
 * @package     Edit
 *
 * @description Edit scripts for business hours
 * @license     YetiForce Public License 5.0
 * @author      Rafal Pospiech <r.pospiech@yetiforce.com>
 */
'use strict';
$.Class(
	'Settings_BusinessHours_Edit_Js',
	{},
	{
		/**
		 * Function to register form for validation
		 * @param {jQuery} container
		 */
		registerFormForValidation(container) {
			container.validationEngine(app.validationEngineOptions);
		},

		/**
		 * Checks that at least one working day has been marked.
		 * @param {jQuery} container
		 */
		validationCheckWorkingDays(container) {
			container.on('submit', (e) => {
				if (!container.find('[name="working_days[]"]').is(':checked')) {
					app.showNotify({
						text: app.vtranslate('JS_PLEASE_SELECT_ONE_WORKING_DAYS'),
						type: 'error'
					});
					e.preventDefault();
				}
			});
		},

		/**
		 * Function which will handle the registrations for the elements
		 */
		registerEvents() {
			const container = $('#EditView');
			this.registerFormForValidation(container);
			app.registerEventForClockPicker();
			App.Fields.TimePeriod.register(container);
			this.validationCheckWorkingDays(container);
		}
	}
);
