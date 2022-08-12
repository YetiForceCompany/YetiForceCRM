/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'YetiForce_ListSearch_Js',
	{
		getInstance: function (container, noEvents, reletedInstance, moduleName) {
			if (typeof moduleName === 'undefined') {
				moduleName = app.getModuleName();
			}
			let moduleClassName = module + '_ListSearch_Js',
				instance;
			if (typeof window[moduleClassName] !== 'undefined') {
				instance = new window[moduleClassName](container, noEvents, reletedInstance);
			} else {
				instance = new window['YetiForce_ListSearch_Js'](container, noEvents, reletedInstance);
			}
			instance.moduleName = moduleName;
			return instance;
		},
		registerSearch: function (container, callBack) {
			container.on('click', '.js-change-order', function (e) {
				let element = $(e.currentTarget);
				callBack({ orderby: { [element.data('columnname')]: element.data('nextsortorderval') } });
			});
			container.on('click', '.js-listview_header', function (e) {
				let element = $(e.currentTarget);
				callBack({
					orderby: element.data('columnname'),
					sortorder: element.data('nextsortorderval')
				});
			});
			container.on('click', '.js-list-reload', function (e, data) {
				callBack(data);
			});
		}
	},
	{
		moduleName: false,
		container: false,
		reletedInstance: false,
		viewName: false,
		init: function (container, noEvents, reletedInstance) {
			if (typeof container === 'undefined') {
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
			return jQuery('#alphabetSearchKey').val();
		},
		getAlphabetSearchValue: function () {
			return jQuery('#alphabetValue').val();
		},
		registerListSearch: function () {
			let listViewContainer = this.getContainer();
			listViewContainer.find('[data-trigger="listSearch"]').on('click', () => {
				this.reloadList();
			});
			listViewContainer.find('input.listSearchContributor').on('keypress', (e) => {
				if (e.keyCode == 13) {
					this.triggerListSearch();
				}
			});
			listViewContainer.find('.removeSearchConditions').on('click', () => {
				this.reloadList({
					search_params: [],
					search_key: '',
					search_value: '',
					operator: '',
					lockedEmptyFields: []
				});
			});
			this.registerListSearchEmptyValue();
		},
		/**
		 * Register list search if value empty.
		 * @param {array} params
		 * @returns {array}
		 */
		parseConditions: function (params) {
			let listViewContainer = this.getContainer();
			let lockedEmptyFields = [];
			let lockedInput = listViewContainer.find('.js-empty-fields').val();
			if (!!lockedInput) {
				lockedEmptyFields = JSON.parse(lockedInput);
			}
			listViewContainer.find('.js-empty-value').each(function () {
				let element = $(this);
				let parentField = element.parents('.searchField').find('.listSearchContributor');
				let fieldName = parentField.attr('name');
				let moduleName = parentField.data('module-name');
				let sourceFieldName = parentField.data('source-field-name');
				if (moduleName !== undefined && sourceFieldName !== undefined) {
					fieldName = fieldName + ':' + moduleName + ':' + sourceFieldName;
				}
				if (element.is(':checked')) {
					if ($.inArray(fieldName, lockedEmptyFields) == -1) {
						lockedEmptyFields.push(fieldName);
					}
					let state = 0;
					for (let i = 0; i < params.search_params[0].length; i++) {
						if (params.search_params[0][i][0] === fieldName) {
							params.search_params[0][i] = [fieldName, 'y', ''];
							state = 1;
						}
					}
					if (!state) {
						params.search_params[0].push([fieldName, 'y', '']);
					}
				} else {
					for (let i = 0; i < lockedEmptyFields.length; i++) {
						if (lockedEmptyFields[i] === fieldName) {
							lockedEmptyFields.splice(i, 1);
						}
					}
				}
			});
			params.lockedEmptyFields = lockedEmptyFields;
			return params;
		},
		/**
		 * Register list search if value empty.
		 */
		registerListSearchEmptyValue: function () {
			let listViewContainer = this.getContainer();
			const self = this;
			listViewContainer.find('.js-empty-value').each(function () {
				let element = $(this);
				element.on('click', function (e) {
					self.reloadList();
				});
			});
		},
		registerListViewSelect: function () {
			let self = this,
				listViewContainer = this.getContainer();
			listViewContainer.find('.listViewEntriesTable .select2noactive').each((index, domElement) => {
				let select = $(domElement);
				if (!select.data('select2')) {
					App.Fields.Picklist.showSelect2ElementView(select, {
						placeholder: app.vtranslate('JS_SELECT_AN_OPTION')
					});
				}
			});
			if (app.getMainParams('autoRefreshListOnChange') == '1') {
				listViewContainer.find('.listViewEntriesTable select, .searchInSubcategories').on('change', (e) => {
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
				listViewContainer.find('.listViewEntriesTable .js-tree-container .listSearchContributor').on('change', () => {
					this.triggerListSearch();
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
			jQuery('#totalPageCount').text('');
			jQuery('.pagination').data('totalCount', 0);
		},
		triggerListSearch: function () {
			let listInstance = this;
			let listViewContainer = listInstance.getContainer();
			listViewContainer.find('[data-trigger="listSearch"]').trigger('click');
		},
		registerDateListSearch: function (container) {
			App.Fields.Date.registerRange(this.getContainer().find('.dateRangeField'));
		},
		registerDateTimeListSearch: function (container) {
			App.Fields.DateTime.register(this.getContainer());
		},
		registerTimeListSearch: function () {
			app.registerEventForClockPicker();
		},
		registerAlphabetClick: function () {
			let thisInstance = this;
			this.getContainer()
				.find('.alphabetBtn')
				.on('click', function () {
					app.showModalWindow($('.alphabetModal').html(), function (data) {
						thisInstance.registerEventForAlphabetSearch(data);
					});
				});
		},
		getCurrentCvId: function () {
			return jQuery('#customFilter').find('option:selected').data('id');
		},
		registerEventForAlphabetSearch: function (modalContainer) {
			let thisInstance = this;
			modalContainer.find('.alphabetSearch').on('click', function (e) {
				let alphabet = $(e.currentTarget).find('a').text();
				let cvId = thisInstance.getCurrentCvId();
				let AlphabetSearchKey = thisInstance.getAlphabetSearchField();
				let urlParams = {
					viewname: cvId,
					search_key: AlphabetSearchKey,
					search_value: alphabet,
					operator: 's',
					page: 1
				};
				thisInstance.resetPagination();
				thisInstance.reloadList(urlParams);
				app.hideModalWindow(false, modalContainer.parent().attr('id'));
			});
			modalContainer.find('.removeAlfabetCondition').on('click', function () {
				thisInstance.reloadList({ search_key: '', search_value: '', operator: '' });
				app.hideModalWindow(false, modalContainer.parent().attr('id'));
			});
		},
		updatePaginationOnAlphabetChange: function (alphabet, AlphabetSearchKey) {
			let thisInstance = this;
			let params = {};
			params['module'] = thisInstance.moduleName;
			params['parent'] = app.getParentModuleName();
			params['view'] = 'Pagination';
			params['page'] = 1;
			params['mode'] = 'getPagination';
			params['search_key'] = AlphabetSearchKey;
			params['search_value'] = alphabet;
			params['operator'] = 's';

			AppConnector.request(params).done(function (data) {
				jQuery('.paginationDiv').html(data);
				let instance = thisInstance.getInstanceView();
				if (instance && jQuery.isFunction(instance.registerPageNavigationEvents)) {
					instance.registerPageNavigationEvents();
				}
			});
		},
		getListSearchParams: function (urlSearchParams) {
			let listViewPageDiv = this.getContainer();
			let listViewTable = listViewPageDiv.find('.listViewEntriesTable');
			let searchParams = [];
			listViewTable.find('.listSearchContributor').each(function (index, domElement) {
				let searchInfo = [];
				let searchContributorElement = $(domElement);
				let fieldInfo = searchContributorElement.data('fieldinfo');
				let fieldName = searchContributorElement.attr('name');
				let searchValue = searchContributorElement.val();
				if (typeof searchValue == 'object') {
					if (searchValue == null) {
						searchValue = '';
					} else {
						searchValue = searchValue.join('##');
					}
				} else if ($.inArray(fieldInfo.type, ['tree']) >= 0) {
					searchValue = searchValue.replace(/,/g, '##');
				}
				searchValue = searchValue.trim();
				if (searchValue.length <= 0) {
					return true;
				}
				let searchOperator = 'a';
				if (fieldInfo.hasOwnProperty('searchOperator')) {
					searchOperator = fieldInfo.searchOperator;
				} else if (
					$.inArray(fieldInfo.type, [
						'modules',
						'time',
						'userCreator',
						'owner',
						'picklist',
						'tree',
						'boolean',
						'fileLocationType',
						'userRole',
						'multiReferenceValue',
						'inventoryLimit',
						'currencyList'
					]) >= 0
				) {
					searchOperator = 'e';
				} else if (fieldInfo.type == 'date' || fieldInfo.type == 'datetime') {
					searchOperator = 'bw';
				} else if (
					$.inArray(fieldInfo.type, [
						'multipicklist',
						'categoryMultipicklist',
						'multiListFields',
						'mailScannerFields',
						'mailScannerActions'
					]) != -1
				) {
					searchOperator = 'c';
				}
				let sourceFieldName = searchContributorElement.data('sourceFieldName');
				if (sourceFieldName) {
					searchInfo.push(fieldName + ':' + searchContributorElement.data('moduleName') + ':' + sourceFieldName);
				} else {
					searchInfo.push(fieldName);
				}
				if (
					$.inArray(fieldInfo.type, ['tree', 'categoryMultipicklist']) != -1 &&
					$('.searchInSubcategories[data-columnname="' + fieldName + '"]', listViewTable).prop('checked')
				) {
					searchOperator = 'ch';
				}
				searchInfo.push(searchOperator);
				searchInfo.push(searchValue);
				searchParams.push(searchInfo);
			});
			if (urlSearchParams && listViewPageDiv.find('#search_params').length) {
				let valueInSearch = null;
				let url = listViewPageDiv.find('#search_params').val();
				if (url != undefined && url.length) {
					url = JSON.parse(url);
					$.each(url[0], function (index, value) {
						let exist = false;
						$.each(searchParams, function (index, searchParam) {
							if (searchParam[0] == value[0]) {
								exist = true;
							}
						});
						if (value[0].indexOf(':') === -1) {
							valueInSearch = listViewTable.find('.listSearchContributor[name="' + value[0] + '"]').val();
						} else {
							let fieldRelation = value[0].split(':');
							valueInSearch = listViewTable
								.find(
									'.listSearchContributor[name="' +
										fieldRelation[0] +
										'"][data-module-name="' +
										fieldRelation[1] +
										'"][data-source-field-name="' +
										fieldRelation[2] +
										'"]'
								)
								.val();
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
			let viewName = this.viewName ? this.viewName : app.getViewName();
			let instance = false;
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
			} else if (viewName === 'List' || viewName === 'Tiles') {
				instance = new Vtiger_List_Js();
				instance.reloadFunctionName = 'getListViewRecords';
				instance.execute = ['updatePagination'];
			} else if (viewName === 'ListPreview') {
				instance = window.pageController;
				instance.reloadFunctionName = 'getListViewRecords';
				instance.execute = ['updatePagination'];
			}

			return instance;
		},
		reloadList: function (params) {
			let thisInstance = this;
			if (params == undefined) {
				params = { page: 1, totalCount: 0 };
			}
			let instance = this.getInstanceByView();
			if (instance) {
				let funcName = instance.reloadFunctionName;
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
				let func = instance.execute;
				for (let i in func) {
					let funcName = func[i];
					if (jQuery.isFunction(instance[funcName])) {
						instance[funcName]();
					}
				}
			}
		},
		registerBasicEvents: function () {
			this.registerListViewSelect();
			this.registerDateListSearch();
			this.registerDateTimeListSearch();
			this.registerTimeListSearch();
			this.registerAlphabetClick();
			this.registerListSearch();
			this.getContainer()
				.find('select.select2')
				.each(function (i, obj) {
					if (!$(obj).data('select2')) {
						App.Fields.Picklist.showSelect2ElementView($(obj));
					}
				});
			App.Fields.Tree.register(this.container);
		},
		/**
		 * Function which will regiter all events for this page
		 */
		registerEvents: function () {
			this.registerBasicEvents();
		}
	}
);
