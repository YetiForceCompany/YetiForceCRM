/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Index_Js(
	'Settings_Log_LogsViewer_Js',
	{},
	{
		/**
		 * Register data table component
		 */
		registerDataTable() {
			let table = this.container.find('.js-data-table');
			let form = this.container.find('.js-filter-form');
			let dataTable = app.registerDataTables(table, {
				order: [],
				processing: true,
				serverSide: true,
				searching: false,
				orderCellsTop: true,
				ajax: {
					url:
						'index.php?parent=Settings&module=Log&action=LogsViewer&type=' +
						this.container.find('.nav .active').data('type'),
					type: 'POST',
					data: function (data) {
						data = $.extend(data, form.serializeFormData());
					}
				}
			});
			this.container.find('input').on('change', function () {
				dataTable.ajax.reload();
			});
		},

		/**
		 * Register events
		 */
		registerEvents() {
			this._super();
			this.container = $('.js-logs-container');
			App.Fields.Date.registerRange(this.container);
			this.registerDataTable();
		}
	}
);
