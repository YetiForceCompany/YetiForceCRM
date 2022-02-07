/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_RecordsList_JS',
	{},
	{
		/**
		 * Event for select row
		 */
		selectEvent: false,
		/**
		 * List search instance
		 */
		listSearchInstance: false,
		/**
		 * Module name
		 */
		moduleName: false,
		/**
		 * Modal container
		 */
		container: false,
		/**
		 * Set event for select row
		 * @param {function} cb
		 */
		setSelectEvent: function (cb) {
			this.selectEvent = cb;
		},
		/**
		 * Get params for record list
		 * @returns {{module: string, view: string, src_module: string, src_record: int, src_field: string, related_parent_module: string, related_parent_id: int|string, page: int, orderby: string, sortorder: string, multi_select: boolean, totalCount: int|string, noOfEntries: int, onlyBody: boolean}}
		 */
		getParams: function () {
			let params = {
				module: this.moduleName,
				view: this.container.data('view'),
				src_module: this.container.find('.js-parent-module').val(),
				src_record: this.container.find('.js-source-record').val(),
				src_field: this.container.find('.js-source-field').val(),
				related_parent_module: this.container.find('.js-related-parent-module').val(),
				related_parent_id: this.container.find('.js-related-parent-id').val(),
				page: this.container.find('.js-page-number').val(),
				orderby: this.container.find('.js-order-by').val(),
				sortorder: this.container.find('.js-sort-order').val(),
				multi_select: this.container.find('.js-multi-select').val(),
				totalCount: this.container.find('.js-total-count').val(),
				noOfEntries: this.container.find('.js-no-entries').val(),
				filterFields: JSON.parse(this.container.find('.js-filter-fields').val()),
				lockedFields: this.container.find('.js-locked-fields').val(),
				lockedEmptyFields: this.container.find('.js-empty-fields').val(),
				onlyBody: true,
				cvId: this.getFilterSelectElement().val()
			};
			let searchValue = this.listSearchInstance.getAlphabetSearchValue();
			params['search_params'] = this.listSearchInstance.getListSearchParams(true);
			if (typeof searchValue !== 'undefined' && searchValue.length > 0) {
				params['search_key'] = this.listSearchInstance.getAlphabetSearchField();
				params['search_value'] = searchValue;
				params['operator'] = 's';
			}
			this.listSearchInstance.parseConditions(params);
			params.search_params = JSON.stringify(params.search_params);
			return params;
		},
		/**
		 * Load records list
		 * @param {object} params
		 * @returns {*}
		 */
		loadRecordList: function (params) {
			const body = this.container.find('.js-modal-body');
			const aDeferred = $.Deferred();
			const progressIndicatorElement = $.progressIndicator({
				blockInfo: {
					enabled: true,
					elementToBlock: body
				}
			});
			AppConnector.request($.extend(this.getParams(), params))
				.done((responseData) => {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					body.html($(responseData).html());
					this.registerBasicEvents();
					aDeferred.resolve(responseData);
				})
				.fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					app.showNotify({
						text: app.vtranslate('JS_NOT_ALLOWED_VALUE'),
						type: 'error'
					});
				});
			return aDeferred.promise();
		},
		/*
		 * Register the click event for listView headers
		 */
		registerHeadersClickEvent: function () {
			YetiForce_ListSearch_Js.registerSearch(this.container, (data) => {
				this.loadRecordList(data);
			});
		},
		/**
		 * Update record pagination
		 * @param {boolean} countNumberRecords
		 */
		updatePagination: function (countNumberRecords) {
			let params = this.getParams();
			params['mode'] = 'getPagination';
			if (countNumberRecords) {
				params['showTotalCount'] = true;
			}
			AppConnector.request(params).done((responseData) => {
				this.container.find('.js-pagination-container').html(responseData);
				let totalCount = this.container.find('.js-pagination-list').data('totalCount');
				if (totalCount) {
					this.container.find('.js-total-count').val(totalCount);
				}
				this.registerPaginationEvents();
			});
		},
		/**
		 * Register pagination events
		 */
		registerPaginationEvents: function () {
			const self = this;
			this.container.find('.js-next-page').on('click', function () {
				if ($(this).hasClass('disabled')) {
					return;
				}
				if (self.container.find('.js-no-entries').val() == self.container.find('.js-page-limit').val()) {
					let pageNumber = self.container.find('.js-page-number');
					let nextPageNumber = parseInt(parseFloat(pageNumber.val())) + 1;
					pageNumber.val(nextPageNumber);
					self.loadRecordList().done(function () {
						self.updatePagination();
					});
				}
			});
			this.container.find('.js-page--previous').on('click', function () {
				let pageNumber = self.container.find('.js-page-number');
				if (pageNumber.val() > 1) {
					let nextPageNumber = parseInt(parseFloat(pageNumber.val())) - 1;
					pageNumber.val(nextPageNumber);
					self.loadRecordList().done(function () {
						self.updatePagination();
					});
				}
			});
			this.container.find('.js-page--set').on('click', function () {
				if ($(this).hasClass('disabled')) {
					return;
				}
				self.container.find('.js-page-number').val($(this).data('id'));
				self.loadRecordList().done(function () {
					self.updatePagination();
				});
			});
			this.container.find('.js-count-number-records').on('click', function () {
				app.hidePopover($(this));
				Vtiger_Helper_Js.showMessage({
					title: app.vtranslate('JS_LBL_PERMISSION'),
					text: app.vtranslate('JS_GET_PAGINATION_INFO'),
					type: 'info'
				});
				self.updatePagination(true);
			});
			this.container
				.find('.js-page--jump-drop-down')
				.on('click', 'li', function (e) {
					e.stopImmediatePropagation();
				})
				.on('keypress', '.js-page-jump', function (e) {
					if (e.which == 13) {
						e.stopImmediatePropagation();
						const element = $(this);
						const response = Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation(element);
						if (typeof response !== 'undefined') {
							element.validationEngine('showPrompt', response, '', 'topLeft', true);
						} else {
							element.validationEngine('hideAll');
							let pageNumber = self.container.find('.js-page-number');
							let currentPageNumber = pageNumber.val();
							let newPageNumber = parseInt($(this).val());
							let totalPages = parseInt(self.container.find('.js-page--total').text());
							if (newPageNumber > totalPages) {
								let error = app.vtranslate('JS_PAGE_NOT_EXIST');
								element.validationEngine('showPrompt', error, '', 'topLeft', true);
								return;
							}
							if (newPageNumber == currentPageNumber) {
								Vtiger_Helper_Js.showMessage({
									text: app.vtranslate('JS_YOU_ARE_IN_PAGE_NUMBER') + ' ' + newPageNumber,
									type: 'info'
								});
								return;
							}
							pageNumber.val(newPageNumber);
							self.loadRecordList().done(function () {
								self.updatePagination();
							});
						}
					}
				});
		},
		/**
		 * Register list search
		 */
		registerListSearch: function () {
			this.listSearchInstance = YetiForce_ListSearch_Js.getInstance(this.container, false, this, this.moduleName);
			this.listSearchInstance.setViewName('RecordsList');
		},
		/**
		 * Register list events
		 */
		registerListEvents: function () {
			const self = this;
			let additional = this.container.find('.js-additional-informations').val() == 1;
			self.container.on('click', '.js-select-row', function (e) {
				if ($(e.target).hasClass('js-select-checkbox') || $(e.target).hasClass('u-cursor-auto')) {
					return true;
				}
				let row = $(this);
				let data = row.data();
				if (self.container.find('.js-multi-select').val()) {
					let selected = {};
					if (additional) {
						selected[data.id] = [];
						row.find('td[data-field]').each(function (index, field) {
							field = $(field);
							selected[data.id].push({
								value: field.text(),
								field: field.data('field'),
								type: field.data('type')
							});
						});
					} else {
						selected[data.id] = data.name;
					}
					self.selectEvent(selected, e);
				} else {
					self.selectEvent(data, e);
				}
				app.hideModalWindow(false, self.container.parent().attr('id'));
			});
			self.container.on('click', '.js-selected-rows', function (e) {
				let selected = {};
				self.container.find('table tr.js-select-row .js-select-checkbox').each(function (index, element) {
					element = $(element);
					if (!element.is(':checked')) {
						return true;
					}
					let data = element.closest('tr').data();
					if (additional) {
						selected[data.id] = [];
						element
							.closest('tr')
							.find('td[data-field]')
							.each(function (index, field) {
								field = $(field);
								selected[data.id].push({
									value: field.text(),
									field: field.data('field'),
									type: field.data('type')
								});
							});
					} else {
						selected[data.id] = data.name;
					}
				});
				if (Object.keys(selected).length <= 0) {
					app.showNotify({
						text: app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD'),
						type: 'error'
					});
				} else {
					self.selectEvent(selected, e);
					app.hideModalWindow($(e.target).closest('.js-modal-container'));
				}
			});
			self.container.on('change', '.js-hierarchy-records', function () {
				self.container.find('.js-related-parent-id').val(this.value);
				self.container.find('.js-total-count').val('');
				self.container.find('.js-page-number').val(1);
				self.loadRecordList().done(function () {
					self.updatePagination();
				});
			});
			self.container.on('click', '.js-select-checkbox', function (e) {
				let parentElem, element;
				parentElem = element = $(this);
				if (element.data('type') === 'all') {
					parentElem = element.closest('table').find('.js-select-checkbox[data-type="row"]');
				}
				if (element.is(':checked')) {
					parentElem.prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
				} else {
					parentElem.prop('checked', false).closest('tr').removeClass('highlightBackgroundColor');
				}
			});
		},
		getFilterSelectElement: function () {
			return this.container.find('#customFilter');
		},
		registerCustomFilter: function () {
			var filterSelectElement = this.getFilterSelectElement();
			if (filterSelectElement.length > 0) {
				App.Fields.Picklist.showSelect2ElementView(filterSelectElement, {
					templateSelection: function (data) {
						var resultContainer = $('<span></span>');
						resultContainer.append($($('.filterImage').detach().get(0)).show());
						resultContainer.append(data.text);
						return resultContainer;
					},
					customSortOptGroup: true,
					closeOnSelect: true
				});
				var select2Instance = filterSelectElement.data('select2');
				select2Instance.$dropdown.append(this.container.find('span.filterActionsDiv'));
				this.registerChangeCustomFilterEvent(filterSelectElement);
			}
		},
		registerChangeCustomFilterEvent: function (filterSelectElement) {
			filterSelectElement.on('change', (_) => {
				this.loadRecordList({ page: 1, totalCount: 0 }).done((_) => {
					this.updatePagination();
				});
			});
		},
		/**
		 * Register modal basic events
		 */
		registerBasicEvents: function () {
			this.registerListSearch();
			this.registerPaginationEvents();
		},
		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = modalContainer;
			this.moduleName = this.container.data('module');
			this.registerCustomFilter();
			this.registerBasicEvents();
			this.registerListEvents();
			this.registerHeadersClickEvent();
		}
	}
);
