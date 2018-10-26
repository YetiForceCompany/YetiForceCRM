/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Index_Js("Settings_Log_Index_Js", {}, {
	registerDataTable: function () {
		let container = $('.tpl-Settings-Log-Index');
		App.Fields.Date.registerRange(container.find('.logRange'));

		let table = container.find('.js-data-table').dataTable({
			searching: false,
			serverSide: true,
			processing: true,
			scrollX: true,
			bAutoWidth: false,
			ajax: {
				url: "index.php",
				type: "POST",
				data: function (d) {
					d.module = "Log";
					d.parent = "Settings";
					d.action = "Data";
					d.type = container.find('.nav .active').data('type');
					d.range = container.find('.dateRangeFilter').val();
					return d;

				},
			},
			language:
				{
					sLengthMenu: app.vtranslate('JS_S_LENGTH_MENU'),
					sZeroRecords:
						app.vtranslate('JS_NO_RESULTS_FOUND'),
					sInfo:
						app.vtranslate('JS_S_INFO'),
					sInfoEmpty:
						app.vtranslate('JS_S_INFO_EMPTY'),
					sSearch:
						app.vtranslate('JS_SEARCH'),
					sEmptyTable:
						app.vtranslate('JS_NO_RESULTS_FOUND'),
					sInfoFiltered:
						app.vtranslate('JS_S_INFO_FILTERED'),
					sLoadingRecords:
						app.vtranslate('JS_LOADING_OF_RECORDS'),
					sProcessing:
						app.vtranslate('JS_LOADING_OF_RECORDS'),
					oPaginate:
						{
							sFirst: app.vtranslate('JS_S_FIRST'),
							sPrevious:
								app.vtranslate('JS_S_PREVIOUS'),
							sNext:
								app.vtranslate('JS_S_NEXT'),
							sLast:
								app.vtranslate('JS_S_LAST')
						}
					,
					oAria: {
						sSortAscending: app.vtranslate('JS_S_SORT_ASCENDING'),
						sSortDescending:
							app.vtranslate('JS_S_SORT_DESCENDING')
					}
				}
		});
		container.find('.dateRangeBtn').click(function (e) {
			table.DataTable().ajax.reload();
		})
	},
	registerEvents: function () {
		this._super();
		this.registerDataTable();
	}
})
;
