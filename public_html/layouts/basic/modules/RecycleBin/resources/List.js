/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_List_Js(
	'RecycleBin_List_Js',
	{
		/**
		 * Mass activation trigerred on the list
		 */
		massActivation: function () {
			const self = this;
			app.showConfirmModal({
				title: app.vtranslate('JS_MASS_ACTIVATE'),
				text: app.vtranslate('JS_ACTIVATE_RECORD_DESC'),
				confirmedCallback: () => {
					let params = self.getSelectedRecordsParams(),
						listInstance = Vtiger_List_Js.getInstance(),
						container = listInstance.getListViewContainer();
					params.module = container.find('.js-source-module').val();
					params.state = 'Active';
					params.entityState = 'Trash';
					params.action = 'MassState';
					AppConnector.request(params).done(function (data) {
						if (data && data.result && data.result.notify) {
							Vtiger_Helper_Js.showMessage(data.result.notify);
						}
						listInstance.getListViewRecords({
							module: app.getModuleName(),
							view: 'List',
							sourceModule: container.find('.js-source-module').val()
						});
					});
				}
			});
		},
		/**
		 * Mass delete trigerred on the list
		 */
		massDelete: function () {
			const self = this;
			app.showConfirmModal({
				title: app.vtranslate('JS_MASS_DELETE'),
				text: app.vtranslate('JS_DELETE_ALL_RECYCLE_RECORD_DESC'),
				confirmedCallback: () => {
					let params = self.getSelectedRecordsParams(),
						listInstance = Vtiger_List_Js.getInstance(),
						container = listInstance.getListViewContainer();
					params.module = container.find('.js-source-module').val();
					params.sourceModule = container.find('.js-source-module').val();
					params.action = 'MassDelete';
					params.entityState = 'Trash';
					params.viewname = 'undefined';
					AppConnector.request(params).done(function (data) {
						if (data && data.result && data.result.notify) {
							Vtiger_Helper_Js.showMessage(data.result.notify);
						}
						listInstance.getListViewRecords({
							module: app.getModuleName(),
							view: 'List',
							sourceModule: container.find('.js-source-module').val()
						});
					});
				}
			});
		}
	},
	{
		/**
		 * Register module select
		 */
		registerModuleFilter: function () {
			const self = this,
				container = this.getListViewContainer();
			let filterSelectElement = container.find('.js-source-module');
			filterSelectElement.on('select2:selecting', function (e) {
				self
					.getListViewRecords({
						module: app.getModuleName(),
						view: 'List',
						sourceModule: e.params.args.data.id
					})
					.done(function () {
						self.calculatePages().done(function () {
							container.find('.js-pagination-list').data('totalCount', 0);
							self.updatePagination();
						});
					});
			});
		},
		/**
		 * Function to update pagination page numer
		 * @param {boolean} force
		 */
		updatePaginationAjax(force = false) {
			const self = this,
				listViewPageDiv = this.getListViewContainer();
			let params = self.getDefaultParams(),
				container = listViewPageDiv.find('.paginationDiv');
			Vtiger_Helper_Js.showMessage({
				title: app.vtranslate('JS_LBL_PERMISSION'),
				text: app.vtranslate('JS_GET_PAGINATION_INFO'),
				type: 'info'
			});
			if (container.find('.js-pagination-list').data('total-count') > 0 || force) {
				params.totalCount = '-1';
				params.view = 'Pagination';
				params.mode = 'getPagination';
				params.entityState = 'Trash';
				params.module = listViewPageDiv.find('.js-source-module').val();
				AppConnector.request(params).done(function (data) {
					container.html(data);
					self.registerPageNavigationEvents();
				});
			}
		},
		/**
		 * Register empty recycle button
		 */
		registerEmptyRecycle: function () {
			const self = this,
				container = this.getListViewContainer();
			container.find('.js-recycle-empty').on('click', function () {
				app.showConfirmModal({
					title: app.vtranslate('JS_DELETE_ALL_RECYCLE_RECORD'),
					text: app.vtranslate('JS_DELETE_ALL_RECYCLE_RECORD_DESC'),
					confirmedCallback: () => {
						let progressIndicatorElement = $.progressIndicator();
						AppConnector.request({
							module: 'RecycleBin',
							sourceModule: container.find('.js-source-module').val(),
							action: 'MassDeleteAll',
							sourceView: 'List'
						})
							.done(function (data) {
								progressIndicatorElement.progressIndicator({ mode: 'hide' });
								let result = {
									text: app.vtranslate('JS_FAILED_TO_SAVE')
								};
								if (data && data.result) {
									result = {
										text: app.vtranslate('JS_ADDED_TO_QUEUE'),
										type: 'success'
									};
								}
								Vtiger_Helper_Js.showMessage(result);
								self.getListViewRecords();
							})
							.fail(function (error, err) {
								progressIndicatorElement.progressIndicator({ mode: 'hide' });
							});
					}
				});
			});
		},
		/**
		 * Get record list of actual selected module
		 * @param {string} urlParams
		 * @returns {*|jQuery}
		 */
		getListViewRecords: function (urlParams) {
			let overrideUrlParams = {},
				aDeferred = $.Deferred();
			overrideUrlParams.sourceModule = $('.js-source-module').val();
			urlParams = $.extend(overrideUrlParams, urlParams);
			this._super(urlParams)
				.done(function () {
					aDeferred.resolve();
				})
				.fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				});
			return aDeferred.promise();
		},
		getDefaultParams: function () {
			let params = this._super();
			params.module = $('.js-source-module').val();
			params.entityState = 'Trash';
			return params;
		},
		registerEvents: function () {
			this._super();
			this.registerModuleFilter();
			this.registerEmptyRecycle();
		}
	}
);
