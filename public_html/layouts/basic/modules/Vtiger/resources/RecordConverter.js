/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
$.Class("Base_RecordConverter_JS", {}, {
	moduleName: false,
	container: false,
	setSelectEvent: function (cb) {
		this.selectEvent = cb;
	},
	/**
	 * Function get params
	 * @returns {{module: *, view: *, convertType: *, fieldMerge: *, onlyBody: boolean, destinyModule: *}}
	 */
	getParams: function () {
		var params = {
			module: this.container.data('module'),
			view: this.container.data('view'),
			convertType: this.container.find('.js-convert-type option:selected').val(),
			fieldMerge: this.container.find('.js-convert-type option:selected').attr('data-field-merge'),
			onlyBody: true,
			destinyModule: this.container.find('.js-convert-type option:selected').attr('data-destiny-module'),
			inView: app.getViewName(),
		};
		if (app.getViewName() === 'List') {
			let listInstance = Vtiger_List_Js.getInstance();
			params.selected_ids = listInstance.readSelectedIds(true);
			params.excluded_ids = listInstance.readExcludedIds(true);
			params.cvId = listInstance.getCurrentCvId();
			/*
			 if (listViewInstance.getListSearchInstance()) {
			 var searchValue = listViewInstance.getListSearchInstance().getAlphabetSearchValue();
			 postData.search_params = JSON.stringify(listViewInstance.getListSearchInstance().getListSearchParams());
			 if ((typeof searchValue != "undefined") && (searchValue.length > 0)) {
			 postData['search_key'] = listViewInstance.getListSearchInstance().getAlphabetSearchField();
			 postData['search_value'] = searchValue;
			 postData['operator'] = 's';
			 }
			 }
			 */

		} else {
			params.selected_ids = app.getRecordId()
		}
		return params;
	},
	/**
	 * Function load modal window
	 * @returns {*}
	 */
	loadModalWindow: function () {
		let body = this.container.find('.modal-body')
		var aDeferred = $.Deferred();
		var progressIndicatorElement = $.progressIndicator({
			blockInfo: {
				enabled: true,
				elementToBlock: body
			}
		});
		AppConnector.request(this.getParams()).then(function (responseData) {
			progressIndicatorElement.progressIndicator({mode: 'hide'});
			body.html(responseData);
			aDeferred.resolve(responseData);
		}, function (textStatus, errorThrown) {
			aDeferred.reject(textStatus, errorThrown);
			progressIndicatorElement.progressIndicator({mode: 'hide'});
		});
		return aDeferred.promise();
	},
	/**
	 * Function listener to change convert type
	 */
	registerChangeConvertType: function () {
		var thisInstance = this;
		thisInstance.container.on('change', '.js-convert-type', function (e) {
			thisInstance.loadModalWindow();
		});
	},
	/**
	 * Function listener to send a form
	 */
	registerSubmitForm: function () {
		var thisInstance = this;
		thisInstance.container.on('click', "[name='saveButton']", function (e) {
			var redirectToEdit = thisInstance.container.find('.js-convert-type option:selected').attr('data-redirect-to-edit');
			var destinyModule = thisInstance.container.find('.js-convert-type option:selected').attr('data-destiny-module');
			var convertType = thisInstance.container.find('.js-convert-type option:selected').val();
			if (redirectToEdit) {
				window.location.href = 'index.php?module=' + destinyModule + '&view=Edit&recordConverter=' + convertType + '&sourceId=' + app.getRecordId() + '&sourceModule=' + app.getModuleName();
				return false;
			}
			let formData = thisInstance.container.find('form').serializeFormData();
			if (app.getViewName() === 'List') {
				let listInstance = Vtiger_List_Js.getInstance();
				let validationResult = listInstance.checkListRecordSelected();
				if (validationResult != true) {
					var postData = listInstance.getDefaultParams();
					postData.convertType = convertType;
					postData.destinyModule = destinyModule;
					postData.selected_ids = listInstance.readSelectedIds(true);
					postData.excluded_ids = listInstance.readExcludedIds(true);
					postData.cvid = listInstance.getCurrentCvId();


				}
			} else {
				var postData = {
					selected_ids: app.getRecordId(),
					convertType: convertType,
					destinyModule: destinyModule
				}
			}
			var aDeferred = $.Deferred();
			var progressIndicatorElement = $.progressIndicator({
				blockInfo: {
					enabled: true,
					elementToBlock: thisInstance.container.find('.modal-body')
				}
			});
			AppConnector.request($.extend(formData, postData)).then(function (responseData) {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
				aDeferred.resolve(responseData);
			}, function (textStatus, errorThrown) {
				aDeferred.reject(textStatus, errorThrown);
				progressIndicatorElement.progressIndicator({mode: 'hide'});
			});
			return aDeferred.promise();
		});
	},
	/**
	 * Register events function
	 * @param modalContainer
	 */
	registerEvents: function (modalContainer) {
		this.container = modalContainer;
		this.registerChangeConvertType();
		this.registerSubmitForm();
	}
});
