/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Index_Js(
	'Settings_Log_LogsViewer_Js',
	{},
	{
		/**
		 * Get data from server
		 *
		 * @param {function} callback
		 */
		getData(callback) {
			const progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			let valueFilter = '';
			let filter = this.container.find('.js-log-filter');
			if (filter.data('type-filter') === 'DateTimeRange') {
				valueFilter = filter.find('.js-date-filter').val();
			}
			AppConnector.request({
				data: {
					module: app.getModuleName(),
					parent: 'Settings',
					action: 'LogsViewer',
					type: this.container.find('.nav .active').data('type'),
					typefilter: filter.data('type-filter'),
					valuefilter: valueFilter
				}
			}).done((response) => {
				const columns = [],
					data = [];
				for (let key in response.columns) {
					const render = $.fn.dataTable.render;
					columns.push({
						title: response.columns[key],
						name: key,
						data: key,
						render
					});
				}
				for (let key in response.data) {
					let row = response.data[key];
					data.push(row);
				}
				callback(data, columns);
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
			});
		},

		/**
		 * Initialize data table component
		 *
		 * @param {array} data
		 * @param {array} columns
		 * @returns {jQuery}
		 */
		initDataTable(data, columns) {

			return this.container.find('.js-data-table').dataTable({
				searching: false,
				processing: false,
				scrollX: true,
				bAutoWidth: false,
				data,
				columns,
				language: {
					sLengthMenu: app.vtranslate('JS_S_LENGTH_MENU'),
					sZeroRecords: app.vtranslate('JS_NO_RESULTS_FOUND'),
					sInfo: app.vtranslate('JS_S_INFO'),
					sInfoEmpty: app.vtranslate('JS_S_INFO_EMPTY'),
					sSearch: app.vtranslate('JS_SEARCH'),
					sEmptyTable: app.vtranslate('JS_NO_RESULTS_FOUND'),
					sInfoFiltered: app.vtranslate('JS_S_INFO_FILTERED'),
					sLoadingRecords: app.vtranslate('JS_LOADING_OF_RECORDS'),
					sProcessing: app.vtranslate('JS_LOADING_OF_RECORDS'),
					oPaginate: {
						sFirst: app.vtranslate('JS_S_FIRST'),
						sPrevious: app.vtranslate('JS_S_PREVIOUS'),
						sNext: app.vtranslate('JS_S_NEXT'),
						sLast: app.vtranslate('JS_S_LAST')
					},
					oAria: {
						sSortAscending: app.vtranslate('JS_S_SORT_ASCENDING'),
						sSortDescending: app.vtranslate('JS_S_SORT_DESCENDING')
					}
				}
			});
		},

		/**
		 * Register data table component
		 */
		 registerDataTable() {
			 let typefilter = this.container.find('.js-log-filter');
			 if (typefilter.data('type-filter') === 'DateTimeRange') {
				App.Fields.Date.registerRange(this.container.find('.js-log-filter'));
			 }
			let table, tableApi;
			this.getData((data, columns) => {
				table = this.initDataTable(data, columns);
				tableApi = table.api();
			});
			this.container.find('.js-date-btn').click((e) => {
				this.getData((data, columns) => {
					tableApi.data().clear();
					tableApi.rows.add(data);
					tableApi.draw();
				});
			});
		},



		/**
		 * Register events
		 */
		registerEvents() {
			this._super();
			this.container = $('.js-logs-container');
			this.registerDataTable();
		}
	}
);
