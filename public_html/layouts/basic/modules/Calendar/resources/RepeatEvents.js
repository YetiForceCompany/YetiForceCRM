/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
jQuery.Class(
	'Calendar_RepeatEvents_JS',
	{},
	{
		/**
		 * Modal container
		 */
		container: false,
		/**
		 * Set saving mode for records
		 */
		setSavingModeForRecords() {
			this.container.find('.js-repeat-events-mode').on('click', function (e) {
				$.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
				$('#EditView [name="typeSaving"]').val($(e.currentTarget).data('value'));
				form.submit();
				app.hideModalWindow();
			});
		},
		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = modalContainer;
			this.setSavingModeForRecords();
		}
	}
);
