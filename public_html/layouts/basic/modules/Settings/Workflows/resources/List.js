/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
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
			this.topMenuContainer.find('.js-workflow-module-filter').on('change', (e) => {
				this.topMenuContainer.find('.js-workflow-sort-button').removeClass('d-none');
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
				});
			});
		},

		/*
		 * Function to register the list view row click event
		 */
		registerRowClickEvent: function () {
			this.getListViewContentContainer().on('click', '.listViewEntries', (e) => {
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
		 * Register show sort actions modal
		 */
		registerShowSortActionsModal: function () {
			$('.js-workflow-sort-button').on('click', () => {
				let sourceModule = this.topMenuContainer.find('.js-workflow-module-filter option:selected').val();
				let url = 'index.php?module=Workflows&parent=Settings&view=SortActionsModal&sourceModule=' + sourceModule;
				app.showModalWindow(null, url, (modalContainer) => {
					modalContainer.find('.js-modal__save').on('click', (e) => {
						e.preventDefault();
						let progressIndicatorElement = $.progressIndicator({
							position: 'html',
							blockInfo: {
								enabled: true
							}
						});
						AppConnector.request({
							module: this.container.find('[name="module"]').length
								? this.container.find('[name="module"]').val()
								: app.getModuleName(),
							parent: app.getParentModuleName(),
							sourceModule: sourceModule,
							action: 'SaveAjax',
							mode: 'sequenceActions',
							workflowForSort: modalContainer.find('.js-workflow-for-sort').val(),
							workflowBefore: modalContainer.find('.js-workflow-before').val()
						})
							.done((data) => {
								if (data.result.message) {
									app.hideModalWindow();
									progressIndicatorElement.progressIndicator({ mode: 'hide' });
									let params = this.getDefaultParams();
									params.orderby = 'sequence';
									this.getListViewRecords(params);
									app.showNotify({ text: data.result.message });
								}
							})
							.fail(function (error, err) {
								app.errorLog(error, err);
							});
					});
				});
			});
		},
		registerEvents: function () {
			this.container = this.getListViewContentContainer();
			this._super();
			this.topMenuContainer = this.getListViewTopMenuContainer();
			this.registerFilterChangeEvent();
			this.registerImportTemplate();
			this.registerShowSortActionsModal();
		}
	}
);
