/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("YetiForce_ListSearch_Js", {
	getInstance: function (container, noEvents) {
		var module = app.getModuleName();
		var moduleClassName = module + '_ListSearch_Js';
		var basicClassName = 'YetiForce_ListSearch_Js';
		if (typeof window[moduleClassName] != 'undefined') {
			var instance = new window[moduleClassName](container, noEvents);
		} else {
			var instance = new window[basicClassName](container, noEvents);
		}
		return instance;
	}
}, {
	container: false,
	init: function (container, noEvents) {
		var thisInstance = this;
		if (typeof container == 'undefined') {
			container = jQuery('.bodyContents');
		}
		this.setContainer(container);
		if (noEvents != true && this.getContainer().find('[data-trigger="listSearch"]').length) {
			this.initialize();
		}
	},
	setContainer: function (container) {
		this.container = container;
	},
	getContainer: function () {
		return this.container;
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
		var listInstance = this;
		var listViewContainer = this.getContainer();
		listViewContainer.find('.listViewEntriesTable .select2noactive').each(function (index, domElement) {
			var select = $(domElement);
			if (!select.data('select2')) {
				app.showSelect2ElementView(select, {placeholder: app.vtranslate('JS_SELECT_AN_OPTION')});
			}
		});

		if (app.getMainParams('autoRefreshListOnChange') == '1') {
			listViewContainer.find('.listViewEntriesTable select').on('change', function (e) {
				listInstance.triggerListSearch();
			});
			listViewContainer.find('.listViewEntriesTable .dateField').on('DatePicker.onHide', function (e, y) {
				var prevVal = $(this).data('prevVal');
				var value = $(this).val();
				if (prevVal != value) {
					listInstance.triggerListSearch();
				}
			});
			listViewContainer.find('.clockPicker').on('change', function () {
				listInstance.triggerListSearch();
			})
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
		var thisInstance = this;
		var listViewContainer = this.getContainer();
		listViewContainer.find('.dateField').each(function (index, element) {
			var dateElement = jQuery(element);
			var customParams = {
				calendars: 3,
				mode: 'range',
				className: 'rangeCalendar',
				onChange: function (formated) {
					dateElement.data('prevVal', dateElement.val());
					dateElement.val(formated.join(','));
				},
				onHide: function (formated) {
					dateElement.trigger(jQuery.Event('DatePicker.onHide'), formated);
				}
			}
			app.registerEventForDatePickerFields(dateElement, false, customParams);
		});
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
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName()
		params['view'] = 'Pagination';
		params['page'] = 1;
		params['mode'] = 'getPagination';
		params['search_key'] = AlphabetSearchKey
		params['search_value'] = alphabet
		params['operator'] = 's';

		AppConnector.request(params).then(function (data) {
			jQuery('.paginationDiv').html(data);
			var instance = thisInstance.getInstanceView();
			if (instance && instance != undefined && jQuery.isFunction(instance.registerPageNavigationEvents)) {
				instance.registerPageNavigationEvents();
			}
		});
	},
	getListSearchParams: function (urlSearchParams) {
		var listViewPageDiv = this.getContainer();
		var listViewTable = listViewPageDiv.find('.listViewEntriesTable');
		var searchParams = new Array();
		listViewTable.find('.listSearchContributor').each(function (index, domElement) {
			var searchInfo = new Array();
			var searchContributorElement = jQuery(domElement);
			var fieldInfo = searchContributorElement.data('fieldinfo');
			var fieldName = searchContributorElement.attr('name');

			var searchValue = searchContributorElement.val();

			if (typeof searchValue == "object") {
				if (searchValue == null) {
					searchValue = "";
				} else {
					searchValue = searchValue.join(',');
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
			} else if (jQuery.inArray(fieldInfo.type, ['modules', 'time', 'userCreator', 'owner', 'picklist', 'tree', 'boolean', 'fileLocationType', 'userRole', 'companySelect']) >= 0) {
				searchOperator = 'e';
			} else if (fieldInfo.type == "date" || fieldInfo.type == "datetime") {
				searchOperator = 'bw';
			} else if (fieldInfo.type == "multipicklist") {
				searchOperator = 'c';
			}
			searchInfo.push(fieldName);
			searchInfo.push(searchOperator);
			searchInfo.push(searchValue);
			if (fieldInfo.type == "tree") {
				var searchInSubcategories = jQuery('.listViewHeaders #searchInSubcategories[data-columnname="' + fieldName + '"]').prop('checked');
				searchInfo.push(searchInSubcategories);
			}
			searchParams.push(searchInfo);
		});
		if (urlSearchParams) {
			var valueInSearch = null;
			var url = app.getUrlVar('search_params');
			if (url != undefined && url.length) {
				url = jQuery.parseJSON(decodeURIComponent(url));
				$.each(url[0], function (index, value) {
					var exist = false;
					$.each(searchParams, function (index, searchParam) {
						if (searchParam[0] == value[0]) {
							exist = true;
						}
					});
					valueInSearch = listViewTable.find('.listSearchContributor[name="' + value[0] + '"]').val();
					if (exist == false && valueInSearch != '' && valueInSearch !== null) {
						searchParams.push(value);
					}
				});
			}
		}
		return new Array(searchParams);
	},
	getInstanceByView: function () {
		var viewName = app.getViewName();
		var instance = false;
		if (viewName === 'Detail') {
			instance = Vtiger_Detail_Js.getInstance();
			instance.reloadFunctionName = 'loadRelatedList';
		} else if (viewName == 'List') {
			instance = new Vtiger_List_Js();
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
				instance[funcName](params).then(function () {
					thisInstance.resetPagination();
					thisInstance.executeFunctions(instance);
				});
			}
		}
	},
	executeFunctions: function (instance) {
		var thisInstance = this;
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
				app.showSelect2ElementView($(obj));
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
