/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_MergeRecords_JS',
	{},
	{
		/**
		 * Modal container
		 */
		container: false,
		/**
		 * Register list events
		 */
		registerListEvents: function () {
			this.container.find('[name="record"]').on('change', (e) => {
				this.container.find('input[value=' + $(e.currentTarget).val() + ']').trigger('click');
			});
			this.container.find('[type="submit"]').on('click', (e) => {
				e.preventDefault();
				const progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: { enabled: true }
				});
				AppConnector.request(this.container.find('form').serializeFormData()).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					if (data.result === false) {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					}
					app.hideModalWindow();
					const listInstance = new Vtiger_List_Js();
					listInstance.getListViewRecords();
					Vtiger_List_Js.clearList();
				});
			});
		},
		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = $(modalContainer);
			this.registerListEvents();
		}
	}
);
