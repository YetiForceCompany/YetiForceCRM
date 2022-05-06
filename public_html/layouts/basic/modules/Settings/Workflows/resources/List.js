/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Settings_Vtiger_List_Js(
	'Settings_Workflows_List_Js',
	{
		triggerCreate: function (url) {
			var selectedModule = jQuery('#moduleFilter').val();
			if (selectedModule.length > 0) {
				url += '&source_module=' + selectedModule;
			}
			window.location.href = url;
		},

		setChangeStatusTasks: function (e, recordId, status) {
			let changeButtonType = jQuery(e);
			let container = jQuery(e).closest('tr');
			app.showConfirmModal({
				title: app.vtranslate('LBL_STATUS_CONFIRMATION'),
				confirmedCallback: () => {
					var module = app.getModuleName();
					var postData = {
						module: module,
						action: 'TaskAjax',
						mode: 'changeStatusAllTasks',
						record: recordId,
						status: status,
						parent: app.getParentModuleName()
					};
					var deleteMessage = app.vtranslate('JS_TASKS_STATUS_GETTING_CHANGED');
					var progressIndicatorElement = jQuery.progressIndicator({
						message: deleteMessage,
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
					AppConnector.request(postData).done(function (data) {
						progressIndicatorElement.progressIndicator({
							mode: 'hide'
						});
						if (data.success) {
							var count = data.result.count;
							var element = container.find('[data-name="active_tasks"]');
							changeButtonType.hide();
							if (status) {
								element.html('&nbsp;' + count);
								changeButtonType.closest('td').find('.deactiveTasks').show();
							} else {
								element.html('&nbsp;0');
								changeButtonType.closest('td').find('.activeTasks').show();
							}
						} else {
							var params = {
								text: app.vtranslate(data.error.message),
								title: app.vtranslate('JS_LBL_PERMISSION'),
								type: 'error'
							};
							app.showNotify(params);
						}
					});
				}
			});
		}
	},
	{
		registerFilterChangeEvent: function () {
			let thisInstance = this;
			this.topMenuContainer.find('.js-module-filter').on('change', function (e) {
				jQuery('#pageNumber').val('1');
				jQuery('#pageToJump').val('1');
				jQuery('#orderBy').val('');
				jQuery('#sortOrder').val('');
				let params = {
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					sourceModule: jQuery(e.currentTarget).val(),
					orderby: 'sequence'
				};
				//Make the select all count as empty
				jQuery('#recordsCount').val('');
				//Make total number of pages as empty
				jQuery('#totalPageCount').text('');
				thisInstance.getListViewRecords(params).done(function (data) {
					thisInstance.updatePagination();
					thisInstance.registerSortWorkflowActions();
				});
			});
		},

		/*
		 * Function to register the list view row click event
		 */
		registerRowClickEvent: function () {
			this.getListViewContentContainer().on('click', '.listViewEntries', (e) => {
				if ($(e.target).hasClass('js-workflow-up')) return;
				if ($(e.target).hasClass('js-workflow-down')) return;
				if ($(e.target).hasClass('js-drag')) return;
				let editUrl = $(e.currentTarget).find('.js-edit').attr('href');
				if (editUrl) {
					window.location.href = editUrl;
				}
			});
		},

		getDefaultParams: function () {
			var pageNumber = jQuery('#pageNumber').val();
			var module = app.getModuleName();
			var parent = app.getParentModuleName();
			var params = {
				module: module,
				parent: parent,
				page: pageNumber,
				view: 'List',
				sourceModule: jQuery('#moduleFilter').val()
			};
			return params;
		},
		registerImportTemplate: function () {
			jQuery('#importButton').on('click', function () {
				window.location.href = jQuery(this).data('url');
			});
		},
		/**
		 * Register sort workflow actions
		 */
		registerSortWorkflowActions: function () {
			let workflows = this.container.find('.js-workflows-list-actions');
			workflows.sortable({
				containment: workflows,
				items: workflows.find('.js-workflow-action'),
				handle: '.js-drag',
				revert: true,
				tolerance: 'pointer',
				cursor: 'move',
				update: () => {
					this.saveSequence();
					this.updateFirstAndLastRow();
				}
			});
		},
		/**
		 * Save sequence
		 * @param {string|bool} sortType
		 */
		saveSequence: function (sortType = false) {
			let workflows = this.getWorkflowsForUpdate(sortType);
			AppConnector.request({
				module: this.container.find('[name="module"]').length
					? this.container.find('[name="module"]').val()
					: app.getModuleName(),
				parent: app.getParentModuleName(),
				sourceModule: this.topMenuContainer.find('.js-module-filter option:selected').val(),
				action: 'SaveAjax',
				mode: 'sequence',
				workflows: workflows,
				sortType: sortType,
				pageNumber: jQuery('#pageNumber').val()
			})
				.done(function (data) {
					if (data.result.message) {
						app.showNotify(data.result.message);
					}
				})
				.fail(function () {
					app.showNotify({
						text: app.vtranslate('JS_UNEXPECTED_ERROR'),
						type: 'error'
					});
				});
		},
		/**
		 * Get workflows from list
		 * @returns [array]
		 */
		getWorkflowsForUpdate: function () {
			let workflows = [];
			this.container.find('.js-workflow-action').each(function (index) {
				workflows[index] = $(this).data('id');
			});
			return workflows;
		},
		/**
		 * Update first and last row after sortable
		 */
		updateFirstAndLastRow: function () {
			this.container.find('.js-first-workflow').removeClass('js-first-workflow');
			this.container.find('.js-workflows-list-actions tbody tr').first().addClass('js-first-workflow');
			this.container.find('.js-last-workflow').removeClass('js-last-workflow');
			this.container.find('.js-workflows-list-actions tbody tr').last().addClass('js-last-workflow');
		},
		/**
		 * Sort up workflow action
		 */
		registerSortUp: function () {
			this.container.on('click', '.js-workflow-up', (e) => {
				let row = $(e.target).closest('tr');
				if (this.checkIfIsFirstRow(row) && 1 < this.container.find('#pageNumber').val()) {
					this.saveSequence('up');
					location.reload();
				} else {
					row.insertBefore(row.prev('tr'));
					row.removeClass('js-last-workflow');
					this.container.find('.js-workflows-list-actions tbody tr').last().addClass('js-last-workflow');
					this.saveSequence();
				}
			});
		},
		/**
		 * Sort down workflow action
		 */
		registerSortDown: function () {
			this.container.on('click', '.js-workflow-down', (e) => {
				let row = $(e.target).closest('tr');
				if (this.checkIfIsLastRow(row)) {
					this.saveSequence('down');
					location.reload();
				} else {
					row.insertAfter(row.next());
					row.removeClass('js-first-workflow');
					this.container.find('.js-workflows-list-actions tbody tr').first().addClass('js-first-workflow');
					this.saveSequence();
				}
			});
		},
		/**
		 * Check if sortable row is first
		 * @param {jQuery} row
		 * @returns true
		 */
		checkIfIsFirstRow: function (row) {
			return row.hasClass('js-first-workflow');
		},
		/**
		 * Check if sortable row is last
		 * @param {jQuery} row
		 * @returns true
		 */
		checkIfIsLastRow: function (row) {
			return row.hasClass('js-last-workflow');
		},
		/**
		 * Register pagination events
		 */
		registerPageNavigationEvents: function () {
			this._super();
			this.registerSortWorkflowActions();
		},
		registerEvents: function () {
			this.container = this.getListViewContentContainer();
			this._super();
			this.topMenuContainer = this.getListViewTopMenuContainer();
			this.registerFilterChangeEvent();
			this.registerImportTemplate();
			this.registerSortUp();
			this.registerSortDown();
		}
	}
);
