/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Base_SortOrderModal_JS(
	'Settings_CustomView_SortOrderModal_JS',
	{},
	{
		/**
		 * @inheritdoc
		 */
		registerListEvents: function () {
			this._super();
			this.container
				.find('.js-modal__save')
				.off('click')
				.on('click', (e) => {
					e.preventDefault();
					this.saveSorting();
				});
		},
		/**
		 * @inheritdoc
		 */
		getSourceContainer: function () {
			return this.container.find('.js-sorting-form');
		},
		/**
		 * Saves sort the filter
		 */
		saveSorting: function () {
			var progress = $.progressIndicator({
				message: app.vtranslate('JS_SAVE_LOADER_INFO'),
				blockInfo: {
					enabled: true
				}
			});
			let data = this.sourceContainer.serializeFormData();
			app
				.saveAjax(
					'updateField',
					{},
					{
						cvid: data.cvid,
						name: 'sort',
						value: this.getSortData()
					}
				)
				.done(function (data) {
					app.hideModalWindow();
					if (data.success) {
						app.showNotify({ text: data.result.message, type: 'success' });
					}
					progress.progressIndicator({ mode: 'hide' });
				});
		}
	}
);
