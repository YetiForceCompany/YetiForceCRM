/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Settings_PublicHoliday_Js',
	{
		$progressiveIndicator: null,

		/**
		 * Shows progressive indicator
		 *
		 * @param none
		 * @return none
		 */
		showProgressive() {
			Settings_PublicHoliday_Js.$progressiveIndicator = $.progressIndicator({
				position: 'html',
				blockInfo: { enabled: true }
			});
		},

		/**
		 * Hides progressive indicator
		 *
		 * @param none
		 * @return none
		 */
		hideProgressive() {
			Settings_PublicHoliday_Js.$progressiveIndicator.progressIndicator({ mode: 'hide' });
		}
	},
	{
		$editModal: null,
		$duplicateModal: null,
		$itemsContainer: null,
		$activeDateFilter: null,

		/**
		 * Initialises the class
		 */
		init() {
			let outer = $('.contents');
			let container = $('#moduleBlocks', outer);
			this.$editModal = $('.publicHolidayModal', outer);
			this.$duplicateModal = $('.publicHolidayModalMassDuplicate', outer);
			this.$itemsContainer = $('#itemsContainer', outer);
			$('form', this.$editModal).validationEngine($.extend({}, app.validationEngineOptions, { binded: false }));
			$('form', this.$duplicateModal).validationEngine($.extend({}, app.validationEngineOptions, { binded: false }));
			this.registerEvents(container);
		},

		/**
		 * Reloads holiday items into layout
		 *
		 * @param none
		 * @return none
		 */
		reloadItems() {
			let thisInstance = this;
			AppConnector.request({
				parent: app.getParentModuleName(),
				module: app.getModuleName(),
				view: 'Configuration',
				mode: 'list',
				date: this.$activeDateFilter ? this.$activeDateFilter.val() : ''
			})
				.done((response) => {
					if (response) {
						thisInstance.$itemsContainer.html(response);
					} else {
						Settings_Vtiger_Index_Js.showMessage({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					}
					thisInstance.$itemsContainer.trigger('items.reloaded');
				})
				.fail(() => {
					app.showNotify({
						text: app.vtranslate('JS_ERROR'),
						type: 'error'
					});
				});
		},

		/**
		 * Registers modal submit event (save, duplicate)
		 *
		 * @param container
		 * @return none
		 */
		registerModalSubmitEvent(container) {
			let thisInstance = this;
			$('form', this.$editModal)
				.add($('form', this.$duplicateModal))
				.on('submit', (e) => {
					e.preventDefault();
					let $target = $(e.target);
					let isValid = $target.validationEngine('validate');
					if (isValid) {
						let params = $target.serializeFormData();
						Settings_PublicHoliday_Js.showProgressive();
						AppConnector.request(params)
							.done((response) => {
								Settings_PublicHoliday_Js.hideProgressive();
								Settings_Vtiger_Index_Js.showMessage({
									text: response.result.message,
									type: response.result.success ? 'success' : 'error'
								});
								thisInstance.reloadItems();
							})
							.fail((error) => {
								Settings_PublicHoliday_Js.hideProgressive();
								app.showNotify({
									textTrusted: false,
									text: error.toString(),
									type: 'error'
								});
							});
						app.hideModalWindow();
					}
				});
		},

		/**
		 * Registers event to add new public holiday
		 *
		 * @param container
		 * @return none
		 */
		registerAddHolidayEvent(container) {
			let thisInstance = this;
			let $addPublicHoliday = $('.addPublicHoliday', container);
			$addPublicHoliday.click((e) => {
				let $editModalClone = thisInstance.$editModal.clone(true, true);
				App.Fields.Picklist.showSelect2ElementView($('select', $editModalClone));
				app.showModalWindow($editModalClone);
			});
		},

		/**
		 * Registers event to edit existing public holiday
		 *
		 * @param container
		 * @return none
		 */
		registerEditHolidayEvent(container) {
			let thisInstance = this;
			this.$itemsContainer.on('click', '.editHoliday', (e) => {
				let $target = $(e.target);
				let $editModalClone = thisInstance.$editModal.clone(true, true);
				let $holidayDetails = $target.closest('.holidayElement').data();
				$('[name=holidayId]', $editModalClone).val($holidayDetails.holidayId);
				$('[name=holidayDate]', $editModalClone).val($holidayDetails.holidayDate);
				$('[name=holidayType]', $editModalClone).val($holidayDetails.holidayType);
				$('[name=holidayName]', $editModalClone).val($holidayDetails.holidayName);
				App.Fields.Picklist.showSelect2ElementView($('select', $editModalClone));
				app.showModalWindow($editModalClone);
			});
		},

		/**
		 * Registers event to delete existing public holiday
		 *
		 * @param container
		 * @return none
		 */
		registerDeleteHolidayEvent(container) {
			let thisInstance = this;
			this.$itemsContainer.on('click', '.deleteHoliday', (e) => {
				app.showConfirmModal({
					title: app.vtranslate('JS_DELETE_RECORD_CONFIRMATION'),
					confirmedCallback: () => {
						let $target = $(e.target);
						let $holidayDetails = $target.closest('.holidayElement').data();
						Settings_PublicHoliday_Js.showProgressive();
						AppConnector.request({
							parent: app.getParentModuleName(),
							module: app.getModuleName(),
							action: 'Holiday',
							mode: 'delete',
							id: $holidayDetails.holidayId
						})
							.done((response) => {
								Settings_PublicHoliday_Js.hideProgressive();
								Settings_Vtiger_Index_Js.showMessage({
									text: response.result.message,
									type: response.result.success ? 'success' : 'error'
								});
								thisInstance.reloadItems();
							})
							.fail((error) => {
								Settings_PublicHoliday_Js.hideProgressive();
								app.showNotify({
									textTrusted: false,
									text: error.toString(),
									type: 'error'
								});
							});
					}
				});
			});
		},

		/**
		 * Registers mass action checkboxes events
		 *
		 * @param none
		 * @return none
		 */
		registerChangeMassSelectionEvent(container) {
			container.on('change', '.selectall', (e) => {
				let checked = $(e.target).is(':checked');
				$('.selectall', container).prop('checked', checked);
				$('.mass-selector', container).prop('checked', checked);
			});
			container.on('change', '.mass-selector', (e) => {
				let $target = $(e.target);
				if ($target.is(':checked')) {
					if ($('.mass-selector:not(:checked)', container).length <= 0) {
						$('.selectall', container).prop('checked', true);
					}
				} else {
					$('.selectall', container).prop('checked', false);
				}
			});
			this.$itemsContainer.on('items.reloaded', (e) => {
				$('.selectall', container).prop('checked', false);
			});
		},

		/**
		 * Registers mass actions event: duplicate and delete
		 *
		 * @param container
		 * @return none
		 */
		registerMassActionEvent(container) {
			let thisInstance = this;
			$('.masscopy', container).click((e) => {
				let isChecked = $('.mass-selector', container).is(':checked');
				if (isChecked) {
					let $duplicateModalClone = thisInstance.$duplicateModal.clone(true, true);
					App.Fields.Picklist.showSelect2ElementView($('select', $duplicateModalClone));
					let recordList = $('.mass-selector:checked', container)
						.map((_, selector) => {
							return $(selector).data('id');
						})
						.toArray();
					$('[name=holidayIds]', $duplicateModalClone).val(JSON.stringify(recordList));
					app.showModalWindow($duplicateModalClone);
				} else {
					Settings_Vtiger_Index_Js.showMessage({
						text: app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD'),
						type: 'info'
					});
				}
			});
			$('.massdelete', container).click((e) => {
				let isChecked = $('.mass-selector', container).is(':checked');
				if (isChecked) {
					app.showConfirmModal({
						title: app.vtranslate('JS_DELETE_RECORD_CONFIRMATION'),
						confirmedCallback: () => {
							let recordList = $('.mass-selector:checked', container)
								.map((idx, selector) => {
									return $(selector).data('id');
								})
								.toArray();
							Settings_PublicHoliday_Js.showProgressive();
							AppConnector.request({
								parent: app.getParentModuleName(),
								module: app.getModuleName(),
								action: 'Holiday',
								mode: 'massDelete',
								records: recordList
							})
								.done((response) => {
									Settings_PublicHoliday_Js.hideProgressive();
									Settings_Vtiger_Index_Js.showMessage({
										text: response.result.message,
										type: response.result.success ? 'success' : 'error'
									});
									thisInstance.reloadItems();
								})
								.fail((error) => {
									Settings_PublicHoliday_Js.hideProgressive();
									app.showNotify({
										textTrusted: false,
										text: error.toString(),
										type: 'error'
									});
								});
						}
					});
				} else {
					Settings_Vtiger_Index_Js.showMessage({
						text: app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD'),
						type: 'info'
					});
				}
			});
		},

		/**
		 * Registers date filter change event
		 *
		 * @param container
		 * @return none
		 */
		registerChangeDateFilterEvent(container) {
			$.each(container.find('.dateFilter'), (idx, dateFilter) => {
				let $dateFilter = $(dateFilter);
				let $form = $dateFilter.closest('form');
				App.Fields.Date.registerRange($dateFilter, { ranges: false });
				$form.validationEngine($.extend({}, app.validationEngineOptions, { binded: true }));
				$dateFilter.on('change', (e) => {
					let isValid = $form.validationEngine('validate');
					if (isValid) {
						this.$activeDateFilter = $dateFilter;
						this.reloadItems();
					}
				});
				$form.on('click', '.js-range-reset', (e) => {
					$dateFilter.val('').trigger('change');
				});
			});
		},

		/**
		 * Registers events for layout
		 *
		 * @param container
		 * @return none
		 */
		registerEvents(container) {
			this.registerModalSubmitEvent(container);
			this.registerAddHolidayEvent(container);
			this.registerEditHolidayEvent(container);
			this.registerDeleteHolidayEvent(container);
			this.registerChangeMassSelectionEvent(container);
			this.registerMassActionEvent(container);
			this.registerChangeDateFilterEvent(container);
		}
	}
);

$(document).ready((e) => {
	new Settings_PublicHoliday_Js();
});
