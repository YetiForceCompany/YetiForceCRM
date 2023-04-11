/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
Base_RecordsList_JS(
	'Base_RelatedRecordsList_JS',
	{},
	{
		/**
		 * Get params for record list
		 * @returns object
		 */
		getParams: function () {
			let params = this._super();
			(params.selected_ids = this.readSelectedIds(true)), (params.excluded_ids = this.readExcludedIds(true));
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
		/**
		 * Register modal basic events
		 */
		registerBasicEvents: function () {
			this._super();
		},
		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this._super(modalContainer);
			this.registerSelectCheckAll();
			this.registerSelectUncheckAll();
		}
	}
);
