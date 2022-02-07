/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_LoginHistory_List_Js',
	{},
	{
		registerFilterChangeEvent: function () {
			var thisInstance = this;
			jQuery('#usersFilter').on('change', function (e) {
				jQuery('#pageNumber').val('1');
				jQuery('#pageToJump').val('1');
				jQuery('#orderBy').val('');
				jQuery('#sortOrder').val('');
				var params = {
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					search_key: 'user_name',
					search_value: jQuery(e.currentTarget).val(),
					page: 1,
					user_name: this.options[this.selectedIndex].getAttribute('name')
				};
				//Make total number of pages as empty
				jQuery('#totalPageCount').text('');
				thisInstance.getListViewRecords(params).done(function (data) {
					thisInstance.updatePagination();
				});
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
				user_name: jQuery('select[id=usersFilter] option:selected').attr('name'),
				search_key: 'user_name',
				search_value: jQuery('#usersFilter').val()
			};

			return params;
		},

		/**
		 * Function to get Page Jump Params
		 */
		getPageJumpParams: function () {
			var module = app.getModuleName();
			var parent = app.getParentModuleName();
			var pageJumpParams = {
				module: module,
				parent: parent,
				action: 'ListAjax',
				mode: 'getPageCount',
				search_value: jQuery('#usersFilter').val(),
				search_key: 'user_name'
			};
			return pageJumpParams;
		},

		updatePagination: function (pageNumber) {
			pageNumber = typeof pageNumber !== 'undefined' ? pageNumber : 1;
			var thisInstance = this;
			var cvId = thisInstance.getCurrentCvId();
			var params = {};
			params['module'] = app.getModuleName();
			if ('Settings' == app.getParentModuleName()) params['parent'] = 'Settings';
			params['view'] = 'Pagination';
			params['viewname'] = cvId;
			params['page'] = pageNumber;
			params['mode'] = 'getPagination';
			params['sourceModule'] = jQuery('#moduleFilter').val();
			params['totalCount'] = $('.pagination').data('totalCount');

			params['search_key'] = 'user_name';
			params['search_value'] = jQuery('#usersFilter').val();
			params['operator'] = 's';

			params['noOfEntries'] = jQuery('#noOfEntries').val();
			AppConnector.request(params).done(function (data) {
				jQuery('.paginationDiv').html(data);
				thisInstance.registerPageNavigationEvents();
			});
		},

		registerEvents: function () {
			this.registerFilterChangeEvent();
			this.registerPageNavigationEvents();
			this.registerEventForTotalRecordsCount();
		}
	}
);
