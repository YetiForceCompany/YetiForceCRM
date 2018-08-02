/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("KnowledgeBase_Tree_Js", {},
	{
		treeInstance: false,
		content: false,
		getContent: function () {
			if (!this.content) {
				this.content = $('.contentOfData');
			}
			return this.content;
		},
		generateTree: function (container, data) {
			var thisInstance = this;
			thisInstance.treeInstance = container.find('#treeContent');
			var values = data.result;
			var plugins = ['search'];
			thisInstance.treeInstance.jstree({
				core: {
					data: values,
					themes: {
						name: 'proton',
						responsive: true
					}
				},
				plugins: plugins
			});
		},
		loadTree: function (reload) {
			var thisInstance = this;
			var container = $('.treeContainer');
			var params = {
				module: app.getModuleName(),
				action: 'DataTreeAjax',
			};
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			if (reload) {
				thisInstance.treeInstance.jstree('destroy');
			}
			AppConnector.request(params).done(function (data) {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
				thisInstance.generateTree(container, data);
				thisInstance.registerTreeEvents(container);
			});
		},
		searchingInTree: function (text) {
			this.treeInstance.jstree(true).search(text);
		},
		registerSearchEvent: function () {
			var thisInstance = this;
			var valueSearch = $('#valueSearchTree');
			var btnSearch = $('#btnSearchTree');
			valueSearch.on('keypress', function (e) {
				if (e.which == 13) {
					thisInstance.searchingInTree(valueSearch.val());
				}
			});
			btnSearch.on('click', function () {
				thisInstance.searchingInTree(valueSearch.val());
			});
		},
		loadContent: function (recordId) {
			var thisInstance = this;
			var contentData = thisInstance.getContent();
			var params = {
				module: app.getModuleName(),
				view: 'Content',
			};
			if (typeof recordId !== "undefined") {
				params['record'] = recordId;
			}
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request(params).done(function (data) {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
				contentData.html(data);
				if (typeof recordId === "undefined") {
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
					contentData.find('.dataTableWithDocuments').dataTable();
				}
			});
		},
		registerTreeEvents: function () {
			var thisInstance = this;
			thisInstance.registerSearchEvent();
			thisInstance.treeInstance.on('changed.jstree', function (e, data) {
				if (data.node.original.type != 'folder') {
					thisInstance.loadContent(data.node.original.record_id);
				}
			});
		},
		registerBasicEvents: function () {
			var thisInstance = this;
			$('.addRecord').on('click', function () {
				var headerInstance = Vtiger_Header_Js.getInstance();
				var moduleName = app.getModuleName();
				var postQuickCreate = function (data) {
					thisInstance.loadTree(true);
					thisInstance.loadContent();
				};
				var quickCreateParams = {
					callbackFunction: postQuickCreate,
					noCache: false
				};
				headerInstance.quickCreateModule(moduleName, quickCreateParams);
			});
		},
		registerEvents: function () {
			var thisInstance = this;
			thisInstance.registerBasicEvents();
			thisInstance.loadTree(false);
			thisInstance.loadContent();

		}
	});


