/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_Mail_List_Js',
	{},
	{
		registerAcceptanceEvent: function () {
			var list = jQuery('.listViewEntriesDiv');
			list.on('click', '.acceptanceRecord', function (e) {
				var elem = this;
				var id = $(this).closest('tr').data('id');
				var progressIndicator = jQuery.progressIndicator();
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'SaveAjax',
					mode: 'acceptanceRecord',
					id: id
				})
					.done(function (data) {
						progressIndicator.progressIndicator({ mode: 'hide' });
						Settings_Vtiger_Index_Js.showMessage({ text: data.result.message });
						$(elem).remove();
					})
					.fail(function (error) {
						progressIndicator.progressIndicator({ mode: 'hide' });
					});
			});
		},
		massDeleteAction: function () {
			$('.massDelete').on('click', function () {
				let listInstance = Settings_Vtiger_List_Js.getInstance();
				let validationResult = listInstance.checkListRecordSelected();
				if (validationResult != true) {
					app.showConfirmModal({
						title: app.vtranslate('LBL_MASS_DELETE_CONFIRMATION'),
						confirmedCallback: () => {
							let params = {};
							params['module'] = app.getModuleName();
							params['parent'] = app.getParentModuleName();
							params['action'] = 'MassDelete';
							params['selected_ids'] = listInstance.readSelectedIds(true);
							let deleteMessage = app.vtranslate('JS_RECORDS_ARE_GETTING_DELETED');
							let progressIndicatorElement = jQuery.progressIndicator({
								message: deleteMessage,
								position: 'html',
								blockInfo: {
									enabled: true
								}
							});
							AppConnector.request(params).done(function (data) {
								progressIndicatorElement.progressIndicator({
									mode: 'hide'
								});
								if (data && data.result && data.result.notify) {
									Vtiger_Helper_Js.showMessage(data.result.notify);
								}
								listInstance.postMassDeleteRecords();
								if (data.error) {
									app.showNotify({
										text: app.vtranslate(data.error.message),
										title: app.vtranslate('JS_LBL_PERMISSION'),
										type: 'error'
									});
								}
							});
						},
						rejectedCallback: () => {
							Vtiger_List_Js.clearList();
						}
					});
				} else {
					listInstance.noRecordSelectedAlert();
				}
			});
		},
		registerFilterChangeEvent: function () {
			var thisInstance = this;
			jQuery('#mailQueueFilter').on('change', function (e) {
				jQuery('#pageNumber').val('1');
				jQuery('#pageToJump').val('1');
				jQuery('#orderBy').val('');
				jQuery('#sortOrder').val('');
				var params = {
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					orderby: jQuery(e.currentTarget).val(),
					page: 1
				};
				//Make total number of pages as empty
				jQuery('#totalPageCount').text('');
				thisInstance.getListViewRecords(params).done(function (data) {
					thisInstance.updatePagination();
				});
			});
		},
		getParams: function () {
			var listViewContainer = this.getListViewContainer();
			var searchParams = {};
			listViewContainer.find('input.listSearchContributor, select.listSearchContributor').each(function (i, obj) {
				if ($(obj).val() != null) {
					var column = $(obj).attr('name');
					searchParams[column] = {};
					searchParams[column]['value'] = $(obj).val();
				}
			});

			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				page: 1,
				view: 'List',
				searchParams: searchParams
			};
			return params;
		},
		registerListSearch: function () {
			var thisInstance = this;
			var listViewContainer = this.getListViewContainer();
			listViewContainer.find('input.listSearchContributor').on('keypress', function (e) {
				if (e.keyCode == 13) {
					var params = thisInstance.getParams();
					jQuery('#totalPageCount').text('');
					thisInstance.getListViewRecords(params).done(function (data) {
						thisInstance.updatePagination();
					});
				}
			});
			listViewContainer.find('[data-trigger="listSearch"]').on('click', function (e) {
				var params = thisInstance.getParams();
				thisInstance.getListViewRecords(params).done(function (data) {
					thisInstance.updatePagination();
				});
			});
		},
		registerListViewSelect: function () {
			if (app.getMainParams('autoRefreshListOnChange') == '1') {
				var thisInstance = this;
				var listViewContainer = this.getListViewContainer();
				listViewContainer.on('change', '.listViewEntriesTable select', function (e) {
					var params = thisInstance.getParams();
					thisInstance.getListViewRecords(params).done(function (data) {
						thisInstance.updatePagination();
					});
				});
			}
		},
		registerEvents: function () {
			this._super();
			this.registerFilterChangeEvent();
			this.massDeleteAction();
			this.registerAcceptanceEvent();
			this.registerListSearch();
			this.registerListViewSelect();
		}
	}
);
