/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Index_Js('Settings_Logs_Index_Js', {
	registerSystemWarningsEvents: function(container) {
		var thisInstance = this;
		thisInstance.registerWarningsFolders(container);
	},
	registerWarningsFolders: function(container) {
		var thisInstance = this;
		var data = [];
		var treeValues = container.find('#treeValues').val();
		if (treeValues) {
			data = JSON.parse(treeValues);
		}
		container
			.find('#jstreeContainer')
			.jstree({
				core: {
					data: data,
					themes: {
						name: 'proton',
						responsive: true,
						icons: false
					},
					check_callback: true
				},
				plugins: ['checkbox']
			})
			.on('loaded.jstree', function(event, data) {
				$(this).jstree('open_all');
			})
			.on('changed.jstree', function(e, data) {
				if (data.action != 'model') {
					thisInstance.getWarningsList();
				}
			});
		$.extend($.fn.dataTable.defaults, {
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
		container.find('.js-switch--warnings').on('change', () => {
			thisInstance.getWarningsList();
		});
	},
	registerEventsLoadContent: function(thisInstance, mode, container) {
		thisInstance.registerSystemWarningsEvents(container);
	}
});
