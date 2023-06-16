/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
jQuery.Class(
	'Calendar_RepeatEventsDelete_JS',
	{},
	{
		/**
		 * Modal container
		 */
		container: false,
		/**
		 * Set saving mode for records
		 */
		seDeleteModeForRecords() {
			const thisInstance = this;
			this.container.find('.js-repeat-events-mode').on('click', function (e) {
				app.hideModalWindow();
				$.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
				let deleteRecordActionUrl = thisInstance.container.find('[name="delete-url"]').attr('data-url');
				let removeType = $(e.currentTarget).data('value');
				AppConnector.request(deleteRecordActionUrl + '&typeRemove=' + removeType).done(function (data) {
					if (data.success == true) {
						window.location.href = data.result.url;
					} else {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					}
				});
			});
		},
		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = modalContainer;
			this.seDeleteModeForRecords();
		}
	}
);
