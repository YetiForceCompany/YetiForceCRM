/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Vtiger_TreeRecords_Js',
	{},
	{
		mainContainer: false,
		treeInstance: false,
		treeData: false,
		getContainer: function () {
			if (this.mainContainer == false) {
				this.mainContainer = jQuery('#centerPanel');
			}
			return this.mainContainer;
		},
		setContainer: function (container) {
			this.mainContainer = container;
		},
		getTreeListValues: function (container) {
			if (this.treeData == false && container !== 'undefined') {
				let treeValues = container.find('#treeListValues').val();
				if (treeValues != '') {
					this.treeData = JSON.parse(treeValues);
				}
			}
			return this.treeData;
		},
		generateTree: function (container) {
			const thisInstance = this;
			thisInstance.treeInstance = container.find('#treeListContents');
			thisInstance.treeInstance.jstree({
				core: {
					data: thisInstance.getTreeListValues(container),
					themes: {
						name: 'proton',
						responsive: true
					}
				},
				plugins: ['checkbox']
			});
		},
		getRecordsParams: function (container) {
			let selected = [];
			$.each(this.treeInstance.jstree('get_selected', true), function (index, value) {
				selected.push(value.original.record_id);
			});
			return {
				module: app.getModuleName(),
				view: app.getViewName(),
				branches: selected,
				filter: container.find('#moduleFilter').val()
			};
		},
		getRecordsList: function () {
			let thisInstance = this;
			let container = thisInstance.getContainer();
			let progressIndicator = jQuery.progressIndicator({
				message: app.vtranslate('JS_LOADING_OF_RECORDS'),
				blockInfo: { enabled: true }
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

			AppConnector.request(thisInstance.getRecordsParams(container))
				.done(function (data) {
					container.find('#recordsListContents').html(data);
					container.find('#recordsListContents table').dataTable();

					progressIndicator.progressIndicator({ mode: 'hide' });
				})
				.fail(function (error) {
					progressIndicator.progressIndicator({ mode: 'hide' });
				});
		},
		registerFilterChangeEvent: function (container) {
			let thisInstance = this;
			container.on('change', '#moduleFilter', function (e) {
				thisInstance.getRecordsList();
			});
		},
		registerSelectBrancheEvent: function (container) {
			let thisInstance = this;
			thisInstance.treeInstance.on('changed.jstree', function (e, data) {
				thisInstance.getRecordsList();
			});
		},
		registerEvents: function () {
			let container = this.getContainer();
			this.generateTree(container);
			this.registerFilterChangeEvent(container);
			this.registerSelectBrancheEvent(container);
		}
	}
);
