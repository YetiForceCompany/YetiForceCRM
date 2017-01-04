/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Settings_Vtiger_List_Js("Settings_Mail_List_Js", {}, {
	/*
	 * Function to register the list view container
	 */
	getContainer: function () {
		if (this.listViewContainer == false) {
			this.listViewContainer = jQuery('div.listViewContentDiv');
		}
		return this.listViewContainer;
	},
	registerAcceptanceEvent: function () {
		var thisInstance = this;
		var list = jQuery('.listViewEntriesDiv');
		list.on('click', '.acceptanceRecord', function (e) {
			var elem = this;
			var id = $(this).closest('tr').data('id');
			var progressIndicator = jQuery.progressIndicator();
			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['action'] = 'SaveAjax';
			params['mode'] = 'acceptanceRecord';
			params['id'] = id
			AppConnector.request(params).then(
					function (data) {
						progressIndicator.progressIndicator({'mode': 'hide'});
						var params = {};
						params['text'] = data.result.message;
						Settings_Vtiger_Index_Js.showMessage(params);
						$(elem).remove()
					},
					function (error) {
						progressIndicator.progressIndicator({'mode': 'hide'});
					}
			);
		});
	},
	massDeleteAction: function () {
		$('.massDelete').on("click", function () {
			var listInstance = Settings_Vtiger_List_Js.getInstance();
			var validationResult = listInstance.checkListRecordSelected();
			if (validationResult != true) {
				var selectedIds = listInstance.readSelectedIds(true);
				var excludedIds = listInstance.readExcludedIds(true);
				var cvId = listInstance.getCurrentCvId();
				var message = app.vtranslate('LBL_MASS_DELETE_CONFIRMATION');
				Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
						function (e) {
							var params = {};
							params['module'] = app.getModuleName();
							params['parent'] = app.getParentModuleName();
							params['action'] = 'MassDelete';
							params['selected_ids'] = selectedIds;
							//	var deleteURL = url + '&viewname=' + cvId + '&selected_ids=' + selectedIds + '&excluded_ids=' + excludedIds;
							var deleteMessage = app.vtranslate('JS_RECORDS_ARE_GETTING_DELETED');
							var progressIndicatorElement = jQuery.progressIndicator({
								'message': deleteMessage,
								'position': 'html',
								'blockInfo': {
									'enabled': true
								}
							});
							AppConnector.request(params).then(
									function (data) {
										progressIndicatorElement.progressIndicator({
											'mode': 'hide'
										});
										listInstance.postMassDeleteRecords();
										if (data.error) {
											var params = {
												text: app.vtranslate(data.error.message),
												title: app.vtranslate('JS_LBL_PERMISSION')
											}
											Vtiger_Helper_Js.showPnotify(params);
										}
									},
									function (error) {
										console.log('Error: ' + error)
									}
							);
						},
						function (error, err) {
							Vtiger_List_Js.clearList();
						})

			} else {
				listInstance.noRecordSelectedAlert();
			}
		});

	},
	registerFilterChangeEvent: function () {
		var thisInstance = this;
		jQuery('#mailQueueFilter').on('change', function (e) {
			jQuery('#pageNumber').val("1");
			jQuery('#pageToJump').val('1');
			jQuery('#orderBy').val('');
			jQuery("#sortOrder").val('');
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				'orderby': jQuery(e.currentTarget).val(),
				'page': 1,
			}
			//Make total number of pages as empty
			jQuery('#totalPageCount').text("");
			thisInstance.getListViewRecords(params).then(
					function (data) {
						thisInstance.updatePagination();
					}
			);
		});
	},
	getParams: function () {
		var listViewContainer = this.getContainer();
		var searchParams = {};
		listViewContainer.find('input.listSearchContributor, select.listSearchContributor').each(function (i, obj) {
			if ($(obj).val() != null) {
				var column = $(obj).attr("name")
				searchParams[column] = {};
				searchParams[column]['value'] = $(obj).val();
			}
		});
		
		var params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			page: 1,
			view: "List",
			searchParams: searchParams
		}
		return params
	},
	registerListSearch: function () {
		var thisInstance = this;
		var listViewContainer = this.getContainer();
		listViewContainer.find('input.listSearchContributor').on('keypress', function (e) {
			if (e.keyCode == 13) {
				var params = thisInstance.getParams();
				jQuery('#totalPageCount').text("");
				thisInstance.getListViewRecords(params).then(
						function (data) {
							thisInstance.updatePagination();
						}
				);
			}
		});
	},
	registerListViewSelect: function () {
		var thisInstance = this;
		var listViewContainer = this.getContainer();
		listViewContainer.on('change','.listViewEntriesTable select', function (e){
			var params = thisInstance.getParams();
			
			thisInstance.getListViewRecords(params).then(
					function (data) {
						thisInstance.updatePagination();
					}
			);
		});
	},
	registerEvents: function () {
		this._super();
		this.registerFilterChangeEvent();
		this.massDeleteAction();
		this.registerAcceptanceEvent();
		this.registerListSearch();
		this.registerListViewSelect();
	}
});
