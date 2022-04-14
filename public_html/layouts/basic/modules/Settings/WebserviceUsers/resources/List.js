/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_WebserviceUsers_List_Js',
	{},
	{
		getDeleteParams: function () {
			return {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'DeleteAjax',
				typeApi: Vtiger_List_Js.getInstance().getActiveTypeApi()
			};
		},
		container: false,
		clipBoardInstances: false,

		getContainer: function () {
			if (!this.container) {
				this.container = jQuery('div.contentsDiv');
			}
			return this.container;
		},
		getActiveTypeApi: function () {
			return this.getContainer().find('.tabApi .active').closest('.tabApi').data('typeapi');
		},
		getListViewRecords: function (urlParams) {
			var aDeferred = jQuery.Deferred();
			if (typeof urlParams === 'undefined') {
				urlParams = {};
			}
			this.reloadTab(urlParams)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				});
			return aDeferred.promise();
		},
		updatePagination: function (pageNumber) {
			pageNumber = typeof pageNumber !== 'undefined' ? pageNumber : 1;
			let params = this.getDefaultParams();
			params.view = 'Pagination';
			params.page = pageNumber;
			params.mode = 'getPagination';
			params.totalCount = this.getContainer().find('.pagination').data('totalCount');
			params.noOfEntries = this.getContainer().find('#noOfEntries').val();
			AppConnector.request(params).done((data) => {
				$('.paginationDiv').html(data);
				this.registerPageNavigationEvents();
			});
		},
		getDefaultParams: function () {
			return {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				page: jQuery('#pageNumber').val(),
				view: 'List',
				orderby: jQuery('#orderBy').val(),
				sortorder: jQuery('#sortOrder').val(),
				typeApi: this.getActiveTypeApi()
			};
		},
		reloadTab: function (urlParams) {
			const aDeferred = jQuery.Deferred();
			if (urlParams == undefined) {
				urlParams = {};
			}
			let tabContainer = this.getContainer().find('.listViewContent');
			let params = jQuery.extend(this.getDefaultParams(), urlParams);
			AppConnector.request(params)
				.done((data) => {
					tabContainer.html(data);
					Vtiger_Header_Js.getInstance().registerFooTable();
					this.registerPageNavigationEvents();
					this.registerClipboard();
					aDeferred.resolve(data);
				})
				.fail(function (textStatus, errorThrown) {
					app.errorLog(textStatus, errorThrown);
					aDeferred.reject(textStatus, errorThrown);
				});
			return aDeferred.promise();
		},
		/**
		 * Register Clipboard
		 */
		registerClipboard: function () {
			if (typeof this.clipBoardInstances === 'object') {
				this.clipBoardInstances.destroy();
			}
			this.clipBoardInstances = App.Fields.Text.registerCopyClipboard(this.getContainer().find('.listViewContent'));
		},
		registerEvents: function () {
			this._super();
			this.getContainer()
				.find('li.tabApi')
				.on('click', (e) => {
					this.reloadTab({ typeApi: e.currentTarget.dataset.typeapi, page: 1 });
				});
			this.registerClipboard();
		}
	}
);
