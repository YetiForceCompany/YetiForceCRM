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

Vtiger_List_Js(
	'Settings_Users_List_Js',
	{
		/*
		 * function to trigger delete record action
		 * @params: delete record url.
		 */
		deleteRecord: function (deleteRecordActionUrl) {
			app.showConfirmModal({
				title: app.vtranslate('JS_DELETE_USER_CONFIRMATION'),
				confirmedCallback: () => {
					AppConnector.request(deleteRecordActionUrl).done(function (data) {
						if (data) {
							let callback = function (data) {
								let params = app.validationEngineOptions;
								params.onValidationComplete = function (form, valid) {
									if (valid) {
										Settings_Users_List_Js.deleteUser(form);
									}
									return false;
								};
								jQuery('#deleteUser').validationEngine(app.validationEngineOptions);
							};
							app.showModalWindow(data, function (data) {
								if (typeof callback == 'function') {
									callback(data);
								}
							});
						}
					});
				}
			});
		},
		deleteUser: function (form) {
			var listInstance = Vtiger_List_Js.getInstance();
			var userid = form.find('[name="userid"]').val();
			var transferUserId = form.find('[name="tranfer_owner_id"]').val();
			var progressInstance = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var params = {
				module: app.getModuleName(),
				action: 'DeleteAjax',
				transfer_user_id: transferUserId,
				userid: userid,
				permanent: jQuery('[name="deleteUserPermanent"]:checked', form).val()
			};
			AppConnector.request(params).done(function (data) {
				if (data.success) {
					app.hideModalWindow();
					progressInstance.progressIndicator({
						mode: 'hide'
					});
					var orderBy = jQuery('#orderBy').val();
					var sortOrder = jQuery('#sortOrder').val();
					var urlParams = {
						viewname: data.result.viewname,
						orderby: orderBy,
						sortorder: sortOrder
					};
					jQuery('#recordsCount').val('');
					jQuery('#totalPageCount').text('');
					listInstance.getListViewRecords(urlParams).done(function () {
						listInstance.updatePagination();
					});
					params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: data.result.message,
						type: 'success'
					};
					app.showNotify(params);
				}
			});
		},
		/*
		 *Function to delete a user permanently
		 *@param userId, event
		 */
		deleteUserPermanently: function (userId, e) {
			e.stopPropagation();
			app.showConfirmModal({
				title: app.vtranslate('JS_DELETE_USER_PERMANENT_CONFIRMATION'),
				confirmedCallback: () => {
					let deleteRecordActionUrl =
						'index.php?module=' +
						app.getModuleName() +
						'&parent=' +
						app.getParentModuleName() +
						'&view=DeleteUser&mode=permanent&record=' +
						userId;
					AppConnector.request(deleteRecordActionUrl).done(function (data) {
						if (data) {
							var callback = function (data) {
								var params = app.validationEngineOptions;
								params.onValidationComplete = function (form, valid) {
									if (valid) {
										var progressInstance = jQuery.progressIndicator({
											position: 'html',
											blockInfo: {
												enabled: true
											}
										});
										var params = {
											module: app.getModuleName(),
											action: 'DeleteAjax',
											userid: userId,
											transfer_user_id: form.find('[name="tranfer_owner_id"]').val(),
											mode: 'permanent'
										};
										app.hideModalWindow();
										AppConnector.request(params).done(function (response) {
											if (response.success) {
												progressInstance.progressIndicator({
													mode: 'hide'
												});
												params = {
													title: app.vtranslate('JS_MESSAGE'),
													text: response.result.message,
													type: 'error'
												};
												app.showNotify(params);
												jQuery('[data-id=' + userId + ']').hide();
											}
										});
									}
									return false;
								};
								jQuery('#deleteUser').validationEngine(app.validationEngineOptions);
							};
							app.showModalWindow(data, function (data) {
								if (typeof callback == 'function') {
									callback(data);
								}
							});
						}
					});
				}
			});
		},
		/*
		 *Function to restore Inactive User
		 *@param userId, event
		 */
		restoreUser: function (userId, e) {
			e.stopPropagation();
			app.showConfirmModal({
				title: app.vtranslate('JS_RESTORE_CONFIRMATION'),
				confirmedCallback: () => {
					let progressInstance = jQuery.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
					AppConnector.request({
						module: app.getModuleName(),
						action: 'SaveAjax',
						record: userId,
						mode: 'restoreUser'
					}).done(function (response) {
						if (response.success) {
							progressInstance.progressIndicator({
								mode: 'hide'
							});
							app.showNotify({
								text: response.result.message,
								type: 'success'
							});
							window.location.href = response.result.listViewUrl;
						}
					});
				}
			});
		},
		/*
		 *Function to mass off 2FA
		 */
		triggerMassOff2FA: function () {
			let url = window.location.href;
			let listInstance = Settings_Vtiger_List_Js.getInstance();
			let validationResult = listInstance.checkListRecordSelected();
			if (validationResult !== true) {
				app.showConfirmModal({
					title: app.vtranslate('JS_2FA_OFF_CONFIRMATION'),
					confirmedCallback: () => {
						let progressIndicatorElement = jQuery.progressIndicator({
							message: app.vtranslate('JS_2FA_OFF_IN_PROGRESS'),
							position: 'html',
							blockInfo: {
								enabled: true
							}
						});
						AppConnector.request({
							module: 'Users',
							action: 'TwoFactorAuthentication',
							selected_ids: listInstance.readSelectedIds(true),
							excluded_ids: listInstance.readExcludedIds(true),
							mode: 'massOff'
						}).done((data) => {
							progressIndicatorElement.progressIndicator({
								mode: 'hide'
							});
							if (data.error) {
								app.showNotify({
									text: app.vtranslate(data.error.message),
									title: app.vtranslate('JS_LBL_PERMISSION'),
									type: 'error'
								});
							}
							window.location.href = url;
						});
					},
					rejectedCallback: () => {
						Vtiger_List_Js.clearList();
					}
				});
			} else {
				listInstance.noRecordSelectedAlert();
			}
		}
	},
	{
		/*
		 * Function to get Page Jump Params
		 */
		getPageJumpParams: function () {
			var module = app.getModuleName();
			var pageCountParams = {
				module: module,
				view: 'ListAjax',
				mode: 'getPageCount',
				search_params: jQuery('#usersFilter').val()
			};
			return pageCountParams;
		},
		/*
		 * Function to register the list view delete record click event
		 */
		registerDeleteRecordClickEvent: function () {
			var listViewContentDiv = this.getListViewContentContainer();
			listViewContentDiv.on('click', '.deleteRecordButton', function (e) {
				var elem = jQuery(e.currentTarget);
				var rowElement = elem.closest('tr');
				var deleteActionUrl = jQuery('[name="deleteActionUrl"]', rowElement).val();
				Settings_Users_List_Js.deleteRecord(deleteActionUrl);
				e.stopPropagation();
			});
		},
		/*
		 *Function to filter Active and Inactive users from Users List View
		 */
		usersFilter: function () {
			$('#usersFilter').on('change', () => {
				const progressInstance = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					module: app.getModuleName(),
					view: 'List',
					parent: app.getParentModuleName(),
					search_params: $('#usersFilter').val()
				}).done((data) => {
					progressInstance.progressIndicator({
						mode: 'hide'
					});
					$('.js-fixed-thead').floatThead('destroy');
					$('#listViewContents').html(data);
					this.updatePaginationFilter();
					let listSearchInstance = this.getListSearchInstance();
					if (listSearchInstance !== false) {
						listSearchInstance.registerEvents();
					} else {
						App.Fields.Picklist.showSelect2ElementView($('#listViewContents').find('select.select2'));
					}
				});
			});
		},
		updatePaginationFilter: function () {
			const self = this,
				container = this.getListViewContainer();
			AppConnector.request({
				page: 1,
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'Pagination',
				mode: 'getPagination',
				search_params: container.find('#usersFilter').val(),
				noOfEntries: container.find('#noOfEntries').val()
			}).done(function (data) {
				container.find('.paginationDiv').html(data);
				self.registerPageNavigationEvents();
			});
		},
		updatePagination: function (pageNumber) {
			pageNumber = typeof pageNumber !== 'undefined' ? pageNumber : 1;
			var thisInstance = this;
			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['view'] = 'Pagination';
			params['page'] = pageNumber;
			params['mode'] = 'getPagination';
			params['search_key'] = this.getAlphabetSearchField();
			params['search_value'] = this.getAlphabetSearchValue();
			params['operator'] = 's';
			params.search_params = JSON.stringify(this.getListSearchInstance().getListSearchParams());
			params['noOfEntries'] = jQuery('#noOfEntries').val();
			AppConnector.request(params).done(function (data) {
				jQuery('.paginationDiv').html(data);
				thisInstance.registerPageNavigationEvents();
			});
		},
		registerEvents: function () {
			this._super();
			this.usersFilter();
			this.registerDeleteRecordClickEvent();
		}
	}
);
