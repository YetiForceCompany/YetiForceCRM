/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_MeetingServices_List_Js',
	{},
	{
		/**
		 * Container
		 */
		container: false,
		/**
		 * Gets container
		 */
		getContainer: function () {
			if (this.container === false) {
				this.container = this.getListViewContentContainer().closest('.contentsDiv');
			}
			return this.container;
		},

		/**
		 * Register button to create record
		 */
		registerButtons: function () {
			let container = this.getContainer();
			container.on('click', '.js-add-record, .js-edit-record', (e) => {
				app.showModalWindow({
					url: e.currentTarget.dataset.url,
					sendByAjaxCb: (_, __) => {
						this.getListViewRecords();
					}
				});
			});
			App.Fields.Text.registerCopyClipboard(container, '.js-clipboard');
		},

		postLoadListViewRecordsEvents: function (container) {
			App.Fields.Text.registerCopyClipboard(container, '.js-clipboard');
		},
		/**
		 * Main function
		 */
		registerEvents: function () {
			this._super();
			this.registerButtons();
		}
	}
);
