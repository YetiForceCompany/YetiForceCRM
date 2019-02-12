/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js('Settings_WebserviceUsers_List_Js', {}, {
	getDeleteParams: function () {
		return {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: "DeleteAjax",
			typeApi: Vtiger_List_Js.getInstance().getActiveTypeApi(),
		};
	},
	container: false,
	getContainer: function () {
		if (this.container == false) {
			this.container = jQuery('div.contentsDiv');
		}
		return this.container;
	},
	getActiveTypeApi: function () {
		return this.getContainer().find('.tabApi.active').data('typeapi');
	},
	getListViewRecords: function (urlParams) {
		var aDeferred = jQuery.Deferred();
		if (typeof urlParams === "undefined") {
			urlParams = {};
		}
		this.reloadTab(urlParams).done(function (data) {
			aDeferred.resolve(data);
		}).fail(function (textStatus, errorThrown) {
			aDeferred.reject(textStatus, errorThrown);
		});
		return aDeferred.promise();
	},
	updatePagination: function (pageNumber) {
		pageNumber = typeof pageNumber !== "undefined" ? pageNumber : 1;
		var thisInstance = this;
		var params = this.getDefaultParams();
		params.view = 'Pagination';
		params.page = pageNumber;
		params.mode = 'getPagination';
		params.totalCount = $('.pagination').data('totalCount');
		params.noOfEntries = jQuery('#noOfEntries').val();
		AppConnector.request(params).done(function (data) {
			jQuery('.paginationDiv').html(data);
			thisInstance.registerPageNavigationEvents();
		});
	},
	getDefaultParams: function () {
		var params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			page: jQuery('#pageNumber').val(),
			view: "List",
			orderby: jQuery('#orderBy').val(),
			sortorder: jQuery("#sortOrder").val(),
			typeApi: this.getActiveTypeApi()
		}
		return params;
	},
	reloadTab: function (urlParams) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		if (urlParams == undefined) {
			urlParams = {};
		}
		var tabContainer = this.getContainer().find('.listViewContent');
		var defaultParams = this.getDefaultParams();
		var params = jQuery.extend(defaultParams, urlParams);
		AppConnector.request(params).done(function (data) {
			tabContainer.html(data);
			Vtiger_Header_Js.getInstance().registerFooTable();
			thisInstance.registerPageNavigationEvents();
			aDeferred.resolve(data);
		}).fail(function (textStatus, errorThrown) {
			app.errorLog(textStatus, errorThrown);
			aDeferred.reject(textStatus, errorThrown);
		});
		return aDeferred.promise();
	},
	registerEvents: function () {
		var thisInstance = this;
		this._super();
		this.getContainer().find('li.tabApi').on('click', function (e) {
			thisInstance.reloadTab({typeApi: jQuery(this).data('typeapi')});
		});
		App.Fields.Text.registerCopyClipboard(this.getContainer());
	}
})
