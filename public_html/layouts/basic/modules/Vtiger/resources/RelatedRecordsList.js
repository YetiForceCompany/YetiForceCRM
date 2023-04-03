/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_RelatedRecordsList_JS',
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
				cvId: this.getCurrentCvId(),
				additionalData: this.container.find('.js-rl-additional_data').val() || null,
				selected_ids: this.readSelectedIds(true),
				excluded_ids: this.readExcludedIds(true)
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
		 * Get current cvid
		 * @returns int
		 */
		getCurrentCvId: function () {
			return this.getFilterSelectElement().val() || 0;
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
					body.html(responseData);
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
				self.writeSelectedIds([data.id]);
				let selectedRecords;
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
					selectedRecords = selected;
				} else {
					selectedRecords = data;
				}
				self.selectEvent(
					$.extend(self.getParams(), {
						selectedRecords: selectedRecords
					}),
					e
				);
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
					self.selectEvent(
						$.extend(self.getParams(), {
							selectedRecords: selected
						}),
						e
					);
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
			self.container.on('click', '.listViewHeaders .js-select-checkbox', function (_e) {
				let parentElem, mainCheckbox;
				mainCheckbox = $(this);
				let selectedIds = self.readSelectedIds();
				let excludedIds = self.readExcludedIds();
				parentElem = mainCheckbox.closest('table').find('.js-select-checkbox[data-type="row"]');
				let selectAllContainer = self.container.find('.js-check-all-records-container');
				if (mainCheckbox.is(':checked')) {
					selectAllContainer.removeClass('d-none');
					parentElem.each(function (_index, element) {
						$(this).prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
						if (selectedIds == 'all') {
							if ($.inArray($(element).val(), excludedIds) != -1) {
								excludedIds.splice($.inArray($(element).val(), excludedIds), 1);
							}
						} else if ($.inArray($(element).val(), selectedIds) == -1) {
							selectedIds.push($(element).val());
						}
					});
				} else {
					selectAllContainer.addClass('d-none');
					parentElem.each(function (_index, element) {
						$(this).prop('checked', false).closest('tr').removeClass('highlightBackgroundColor');
						if (selectedIds == 'all') {
							if ($.inArray($(element).val(), excludedIds) != -1) {
								excludedIds.splice($.inArray($(element).val(), excludedIds), 1);
							}
						} else if ($.inArray($(element).val(), selectedIds) == -1) {
							selectedIds.push($(element).val());
						}
					});
				}
				self.checkSelectAll();
				self.writeSelectedIds(selectedIds);
				self.writeExcludedIds(excludedIds);
			});
			self.container.on('click', '.listViewEntriesTable tbody .js-select-checkbox', function (_e) {
				let element = $(this);
				let selectedIds = self.readSelectedIds();
				let excludedIds = self.readExcludedIds();
				let recordId = element.closest('tr').attr('data-id');
				if (element.is(':checked')) {
					element.closest('tr').addClass('highlightBackgroundColor');
					if (selectedIds == 'all') {
						excludedIds.splice($.inArray(recordId, excludedIds), 1);
					} else if ($.inArray(recordId, selectedIds) == -1) {
						selectedIds.push(recordId);
					}
				} else {
					element.closest('tr').removeClass('highlightBackgroundColor');
					if (selectedIds == 'all') {
						excludedIds.push(recordId);
						selectedIds = 'all';
					} else {
						selectedIds.splice($.inArray(recordId, selectedIds), 1);
					}
				}
				self.checkSelectAll();
				self.writeSelectedIds(selectedIds);
				self.writeExcludedIds(excludedIds);
			});
		},
		/**
		 * Get selected record ids
		 * @param boolean decode
		 * @returns mixed
		 */
		readSelectedIds: function (decode = false) {
			let selectedIdsDataAttr = this.getCurrentCvId() + 'selectedIds',
				selectedIdsElementDataAttributes = $('#selectedIds').data(),
				selectedIds = [];
			if (!(selectedIdsDataAttr in selectedIdsElementDataAttributes)) {
				this.writeSelectedIds(selectedIds);
			} else {
				selectedIds = selectedIdsElementDataAttributes[selectedIdsDataAttr];
			}
			if (decode == true && typeof selectedIds == 'object') {
				return JSON.stringify(selectedIds);
			}
			return selectedIds;
		},
		/**
		 * Get excluded record ids
		 * @param boolean decode
		 * @returns
		 */
		readExcludedIds: function (decode = false) {
			let excludedIdsDataAttr = this.getCurrentCvId() + 'Excludedids',
				excludedIdsElementDataAttributes = $('#excludedIds').data(),
				excludedIds = [];
			if (!(excludedIdsDataAttr in excludedIdsElementDataAttributes)) {
				this.writeExcludedIds(excludedIds);
			} else {
				excludedIds = excludedIdsElementDataAttributes[excludedIdsDataAttr];
			}
			if (decode == true && typeof excludedIds == 'object') {
				return JSON.stringify(excludedIds);
			}
			return excludedIds;
		},
		/**
		 * Set selected record ids
		 * @param mixed selectedIds
		 */
		writeSelectedIds: function (selectedIds) {
			if (!Array.isArray(selectedIds)) {
				selectedIds = [selectedIds];
			}
			$('#selectedIds').data(this.getCurrentCvId() + 'selectedIds', selectedIds);
		},
		/**
		 * Set excluded record ids
		 * @param array excludedIds
		 */
		writeExcludedIds: function (excludedIds) {
			$('#excludedIds').data(this.getCurrentCvId() + 'Excludedids', excludedIds);
		},
		/**
		 * Select check all records
		 */
		registerSelectCheckAll: function () {
			this.container.on('click', '.js-check-all-records-container', () => {
				this.container.find('.js-check-all-records-container').addClass('d-none');
				this.container.find('.js-uncheck-all-records-container').removeClass('d-none');
				this.container
					.find('.listViewEntriesTable tbody .js-select-checkbox')
					.prop('checked', true)
					.closest('tr')
					.addClass('highlightBackgroundColor');

				this.writeSelectedIds('all');
			});
		},
		/**
		 * Select uncheck all records
		 */
		registerSelectUncheckAll: function () {
			this.container.on('click', '.js-uncheck-all-records-container', () => {
				this.container.find('.js-uncheck-all-records-container').addClass('d-none');
				this.container.find('.listViewHeaders .js-select-checkbox').prop('checked', false);
				this.container
					.find('.listViewEntriesTable tbody .js-select-checkbox')
					.prop('checked', false)
					.closest('tr')
					.removeClass('highlightBackgroundColor');

				this.writeSelectedIds([]);
				this.writeExcludedIds([]);
			});
		},
		/**
		 * Check if all records from list are selected
		 * @returns boolean
		 */
		checkSelectAll: function () {
			let state = true;
			this.container.find('.listViewEntriesTable tbody .js-select-checkbox').each(function (index, element) {
				if ($(element).is(':checked') && state) {
					state = true;
				} else {
					state = false;
				}
			});
			this.container.find('.listViewHeaders .js-select-checkbox').prop('checked', state);
			return state;
		},
		getFilterSelectElement: function () {
			return this.container.find('#customFilter');
		},
		registerCustomFilter: function () {
			const filterSelectElement = this.getFilterSelectElement();
			if (filterSelectElement.length > 0) {
				App.Fields.Picklist.showSelect2ElementView(filterSelectElement, {
					templateSelection: function (data) {
						const resultContainer = document.createElement('span'),
							span = document.createElement('span'),
							image = $('.filterImage').detach();
						image.removeAttr('style');
						span.innerText = data.text;
						resultContainer.appendChild(image.get(0));
						resultContainer.appendChild(span);
						return resultContainer;
					},
					customSortOptGroup: true,
					closeOnSelect: true
				});
				filterSelectElement.data('select2').$dropdown.append(this.container.find('span.filterActionsDiv'));
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
			this.registerSelectCheckAll();
			this.registerSelectUncheckAll();
		}
	}
);
