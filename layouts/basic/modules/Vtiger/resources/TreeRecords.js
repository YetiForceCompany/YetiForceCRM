/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Vtiger_TreeRecords_Js", {}, {
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
		if (this.treeData == false && container != 'undefined') {
			var treeValues = container.find('#treeListValues').val();
			if (treeValues != '') {
				this.treeData = JSON.parse(treeValues);
			}
		}
		return this.treeData;
	},
	generateTree: function (container) {
		var thisInstance = this;
		thisInstance.treeInstance = container.find("#treeListContents");
		thisInstance.treeInstance.jstree({
			core: {
				data: thisInstance.getTreeListValues(container),
				themes: {
					name: 'proton',
					responsive: true
				}
			},
			plugins: [
				"checkbox",
			]
		});
	},
	getRecordsParams: function (container) {
		var thisInstance = this;
		var selectedFilter = container.find('#moduleFilter').val();
		var selected = [];
		$.each(thisInstance.treeInstance.jstree("get_selected", true), function (index, value) {
			selected.push(value.text);
		});
		var params = {
			module: app.getModuleName(),
			view: app.getViewName(),
			branches: selected,
			filter: selectedFilter,
		};
		return params;
	},
	getRecordsList: function () {
		var thisInstance = this;
		var container = thisInstance.getContainer();
		var progressIndicator = jQuery.progressIndicator({message: app.vtranslate('JS_LOADING_OF_RECORDS'), blockInfo: {enabled: true}});

		$.extend($.fn.dataTable.defaults, {
			//searching: true,
			//ordering: false,
			//bFilter: false,
			//bLengthChange: false,
			//bPaginate: false,
			//bInfo: false,
			//pageLength: -1,
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

		AppConnector.request(thisInstance.getRecordsParams(container)).then(function (data) {
			container.find('#recordsListContents').html(data);
			container.find('#recordsListContents table').dataTable();

			progressIndicator.progressIndicator({mode: 'hide'});
		}, function (error) {
			progressIndicator.progressIndicator({mode: 'hide'});
		});
	},
	registerFilterChangeEvent: function (container) {
		var thisInstance = this;
		container.on('change', '#moduleFilter', function (e) {
			thisInstance.getRecordsList();
		});
	},
	registerSelectBrancheEvent: function (container) {
		var thisInstance = this;
		thisInstance.treeInstance.on("changed.jstree", function (e, data) {
			thisInstance.getRecordsList();
		});
	},
	registerShowHideRightPanelEvent: function (container) {
		container.find('.toggleSiteBarRightButton').click(function (e) {
			var siteBarRight = $(this).closest('.siteBarRight');
			var content = $(this).closest('.row').find('.rowContent');
			var buttonImage = $(this).find('.glyphicon');
			if (siteBarRight.hasClass('hideSiteBar')) {
				siteBarRight.removeClass('hideSiteBar');
				content.removeClass('col-md-12').addClass('col-md-8');
				buttonImage.removeClass('glyphicon-chevron-left').addClass("glyphicon-chevron-right");
			} else {
				siteBarRight.addClass('hideSiteBar');
				content.removeClass('col-md-8').addClass('col-md-12');
				buttonImage.removeClass('glyphicon-chevron-right').addClass("glyphicon-chevron-left");
			}
		});
	},
	registerEvents: function () {
		var container = this.getContainer();
		this.generateTree(container);
		this.registerFilterChangeEvent(container);
		this.registerSelectBrancheEvent(container);
		this.registerShowHideRightPanelEvent(container);
	}
});
