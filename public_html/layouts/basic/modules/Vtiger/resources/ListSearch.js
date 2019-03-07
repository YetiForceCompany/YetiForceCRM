/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("YetiForce_ListSearch_Js", {
	getInstance: function (container, noEvents, reletedInstance, moduleName) {
		if (typeof moduleName === "undefined") {
			moduleName = app.getModuleName();
		}
		let moduleClassName = module + '_ListSearch_Js',
			instance;
		if (typeof window[moduleClassName] !== "undefined") {
			instance = new window[moduleClassName](container, noEvents, reletedInstance);
		} else {
			instance = new window['YetiForce_ListSearch_Js'](container, noEvents, reletedInstance);
		}
		instance.moduleName = moduleName;
		return instance;
	}
}, {
	moduleName: false,
	container: false,
	reletedInstance: false,
	viewName: false,
	init: function (container, noEvents, reletedInstance) {
		if (typeof container === "undefined") {
			container = jQuery('.bodyContents');
		}
		this.setContainer(container);
		if (noEvents != true && this.getContainer().find('[data-trigger="listSearch"]').length) {
			this.initialize();
		}
		this.reletedInstance = reletedInstance;
	},
	setContainer: function (container) {
		this.container = container;
	},
	getContainer: function () {
		return this.container;
	},
	setViewName: function (viewName) {
		this.viewName = viewName;
	},
	/**
	 * Function  to initialize the advance filter
	 */
	initialize: function () {
		this.registerEvents();
	},
	getAlphabetSearchField: function () {
		return jQuery("#alphabetSearchKey").val();
	},
	getAlphabetSearchValue: function () {
		return jQuery("#alphabetValue").val();
	},
	registerListSearch: function () {
		var thisInstance = this;
		var listViewContainer = this.getContainer();
		listViewContainer.find('[data-trigger="listSearch"]').on('click', function (e) {
			thisInstance.reloadList();
		});
		listViewContainer.find('input.listSearchContributor').on('keypress', function (e) {
			if (e.keyCode == 13) {
				thisInstance.triggerListSearch();
			}
		});
		listViewContainer.find('.removeSearchConditions').on('click', function () {
			thisInstance.reloadList({search_params: [], search_key: '', search_value: '', operator: ''});
		});
	},
	registerListViewSelect: function () {
		let self = this,
			listViewContainer = this.getContainer();
		listViewContainer.find('.listViewEntriesTable .select2noactive').each((index, domElement) => {
			let select = $(domElement);
			if (!select.data('select2')) {
				App.Fields.Picklist.showSelect2ElementView(select, {placeholder: app.vtranslate('JS_SELECT_AN_OPTION')});
			}
		});
		if (app.getMainParams('autoRefreshListOnChange') == '1') {
			listViewContainer.find('.listViewEntriesTable select, .searchInSubcategories').on('change', () => {
				this.triggerListSearch();
			});
			listViewContainer.find('.listViewEntriesTable .picklistSearchField').on('apply.daterangepicker', () => {
				this.triggerListSearch();
			});
			listViewContainer.find('.listViewEntriesTable .dateField').on('DatePicker.onHide', function (e, y) {
				let prevVal = $(this).data('prevVal'),
					value = $(this).val();
				if (prevVal != value) {
					self.triggerListSearch();
				}
			});
			app.event.off('Clockpicker.changed');
			app.event.on('Clockpicker.changed', (e, inputEl) => {
				if (listViewContainer.find(inputEl).length) {
					self.triggerListSearch();
				}
			});
		}
	},
	resetPagination: function () {
		//To unmark the all the selected ids
		jQuery('#deSelectAllMsg').trigger('click');
		jQuery('#recordsCount').val('');
		//To Set the page number as first page
		jQuery('#pageNumber').val('1');
		jQuery('#pageToJump').val('1');
		jQuery('#totalPageCount').text("");
		jQuery('.pagination').data('totalCount', 0);
	},
	triggerListSearch: function () {
		var listInstance = this;
		var listViewContainer = listInstance.getContainer();
		listViewContainer.find('[data-trigger="listSearch"]').trigger("click");
	},
	registerDateListSearch: function (container) {
		App.Fields.Date.registerRange(this.getContainer().find('.dateRangeField'));
	},
	registerTimeListSearch: function () {
		app.registerEventForClockPicker();
	},
	registerAlphabetClick: function () {
		var thisInstance = this;
		this.getContainer().find('.alphabetBtn').on('click', function () {
			app.showModalWindow($('.alphabetModal').html(), function (data) {
				thisInstance.registerEventForAlphabetSearch(data);
			});
		});
	},
	getCurrentCvId: function () {
		return jQuery('#customFilter').find('option:selected').data('id');
	},
	registerEventForAlphabetSearch: function (modalContainer) {
		var thisInstance = this;
		modalContainer.find('.alphabetSearch').on('click', function (e) {
			var alphabet = jQuery(e.currentTarget).find('a').text();
			var cvId = thisInstance.getCurrentCvId();
			var AlphabetSearchKey = thisInstance.getAlphabetSearchField();
			var urlParams = {
				viewname: cvId,
				search_key: AlphabetSearchKey,
				search_value: alphabet,
				operator: 's',
				page: 1
			}
			thisInstance.resetPagination();
			thisInstance.reloadList(urlParams);
			app.hideModalWindow();
		});
		modalContainer.find('.removeAlfabetCondition').on('click', function () {
			thisInstance.reloadList({search_key: '', search_value: '', operator: ''});
			app.hideModalWindow();
		})
	},
	updatePaginationOnAlphabetChange: function (alphabet, AlphabetSearchKey) {
		var thisInstance = this;
		var params = {};
		params['module'] = thisInstance.moduleName;
		params['parent'] = app.getParentModuleName()
		params['view'] = 'Pagination';
		params['page'] = 1;
		params['mode'] = 'getPagination';
		params['search_key'] = AlphabetSearchKey
		params['search_value'] = alphabet
		params['operator'] = 's';

		AppConnector.request(params).done(function (data) {
			jQuery('.paginationDiv').html(data);
			var instance = thisInstance.getInstanceView();
			if (instance && jQuery.isFunction(instance.registerPageNavigationEvents)) {
				instance.registerPageNavigationEvents();
			}
		});
	},
	getListSearchParams: function (urlSearchParams) {
		var listViewPageDiv = this.getContainer();
		var listViewTable = listViewPageDiv.find('.listViewEntriesTable');
		var searchParams = [];
		listViewTable.find('.listSearchContributor').each(function (index, domElement) {
			var searchInfo = [];
			var searchContributorElement = jQuery(domElement);
			var fieldInfo = searchContributorElement.data('fieldinfo');
			var fieldName = searchContributorElement.attr('name');
			var searchValue = searchContributorElement.val();
			if (typeof searchValue == "object") {
				if (searchValue == null) {
					searchValue = "";
				} else {
					searchValue = searchValue.join('##');
				}
			}
			searchValue = searchValue.trim();
			if (searchValue.length <= 0) {
				//continue
				return true;
			}

			var searchOperator = 'a';
			if (fieldInfo.hasOwnProperty("searchOperator")) {
				searchOperator = fieldInfo.searchOperator;
			} else if (jQuery.inArray(fieldInfo.type, ['modules', 'time', 'userCreator', 'owner', 'picklist', 'tree', 'boolean', 'fileLocationType', 'userRole', 'companySelect', 'multiReferenceValue']) >= 0) {
				searchOperator = 'e';
			} else if (fieldInfo.type == "date" || fieldInfo.type == "datetime") {
				searchOperator = 'bw';
			} else if (fieldInfo.type == 'multipicklist' || fieldInfo.type == 'categoryMultipicklist') {
				searchOperator = 'c';
			}
			let sourceFieldName = searchContributorElement.data('sourceFieldName');
			if (sourceFieldName) {
				searchInfo.push(fieldName + ':' + searchContributorElement.data('moduleName') + ':' + sourceFieldName);
			} else {
				searchInfo.push(fieldName);
			}
			searchInfo.push(searchOperator);
			searchInfo.push(searchValue);
			if (fieldInfo.type == 'tree' || fieldInfo.type == 'categoryMultipicklist') {
				var searchInSubcategories = jQuery('.listViewHeaders .searchInSubcategories[data-columnname="' + fieldName + '"]').prop('checked');
				searchInfo.push(searchInSubcategories);
			}
			searchParams.push(searchInfo);
		});
		if (urlSearchParams) {
			var valueInSearch = null;
			var url = app.getUrlVar('search_params');
			if (url != undefined && url.length) {
				var lengthUrl = url.length;
				if (url.charAt(lengthUrl - 1) === '#') {
					url = url.substr(0, lengthUrl - 1);
				}
				url = JSON.parse(decodeURIComponent(url));
				$.each(url[0], function (index, value) {
					var exist = false;
					$.each(searchParams, function (index, searchParam) {
						if (searchParam[0] == value[0]) {
							exist = true;
						}
					});
					if (value[0].indexOf(':') === -1) {
						valueInSearch = listViewTable.find('.listSearchContributor[name="' + value[0] + '"]').val();
					} else {
						let fieldRelation = value[0].split(':');
						valueInSearch = listViewTable.find('.listSearchContributor[name="' + fieldRelation[0] + '"][data-module-name="' + fieldRelation[1] + '"][data-source-field-name="' + fieldRelation[2] + '"]').val();
					}
					if (exist == false && valueInSearch != '' && valueInSearch !== null) {
						searchParams.push(value);
					}
				});
			}
		}
		return [searchParams];
	},
	getInstanceByView: function () {
		var viewName = this.viewName ? this.viewName : app.getViewName();
		var instance = false;
		if (viewName === 'RecordsList') {
			instance = this.reletedInstance;
			instance.reloadFunctionName = 'loadRecordList';
			instance.execute = ['updatePagination'];
		} else if (this.reletedInstance) {
			instance = this.reletedInstance;
			instance.reloadFunctionName = 'loadRelatedList';
		} else if (viewName === 'Detail') {
			instance = Vtiger_Detail_Js.getInstance();
			instance.reloadFunctionName = 'loadRelatedList';
		} else if (viewName == 'List') {
			instance = new Vtiger_List_Js();
			instance.reloadFunctionName = 'getListViewRecords';
			instance.execute = ['updatePagination'];
		} else if (viewName == 'ListPreview') {
			instance = window.pageController;
			instance.reloadFunctionName = 'getListViewRecords';
			instance.execute = ['updatePagination'];
		}
		return instance;
	},
	reloadList: function (params) {
		var thisInstance = this;
		if (params == undefined) {
			params = {'page': 1};
		}
		var instance = this.getInstanceByView();
		if (instance) {
			var funcName = instance.reloadFunctionName;
			if (jQuery.isFunction(instance[funcName])) {
				instance[funcName](params).done(function () {
					thisInstance.resetPagination();
					thisInstance.executeFunctions(instance);
				});
			}
		}
	},
	executeFunctions: function (instance) {
		if (instance.execute) {
			var func = instance.execute;
			for (var i in func) {
				var funcName = func[i];
				if (jQuery.isFunction(instance[funcName])) {
					instance[funcName]();
				}
			}
		}
	},
	registerBasicEvents: function () {
		this.registerListViewSelect();
		this.registerDateListSearch();
		this.registerTimeListSearch();
		this.registerAlphabetClick();
		this.registerListSearch();
		this.getContainer().find('select.select2').each(function (i, obj) {
			if (!$(obj).data('select2')) {
				App.Fields.Picklist.showSelect2ElementView($(obj));
			}
		});
	},
	/**
	 * Function which will regiter all events for this page
	 */
	registerEvents: function () {
		this.registerBasicEvents();
	}
});
