/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
$.Class("Base_RecordConverter_JS", {}, {
	container: false,
	/**
	 *
	 * @returns {{module: string, view: string, convertType: integer, fieldMerge: string, onlyBody: boolean, destinyModule: string, inView: string}}
	 */
	getParams: function () {
		let params = {
			module: this.container.data('module'),
			view: this.container.data('view'),
			convertType: this.container.find('.js-convert-type option:selected').val(),
			fieldMerge: this.container.find('.js-convert-type option:selected').attr('data-field-merge'),
			onlyBody: true,
			destinyModule: this.container.find('.js-convert-type option:selected').attr('data-destiny-module'),
			inView: app.getViewName()
		};
		if (app.getViewName() === 'List') {
			let listInstance = Vtiger_List_Js.getInstance();
			params.selected_ids = listInstance.readSelectedIds(true);
			params.excluded_ids = listInstance.readExcludedIds(true);
			params.cvId = listInstance.getCurrentCvId();
			if (listInstance.getListSearchInstance()) {
				var searchValue = listInstance.getListSearchInstance().getAlphabetSearchValue();
				params.search_params = JSON.stringify(listInstance.getListSearchInstance().getListSearchParams());
				if ((typeof searchValue != "undefined") && (searchValue.length > 0)) {
					params.search_key = listInstance.getListSearchInstance().getAlphabetSearchField();
					params.search_value = searchValue;
					params.operator = 's';
				}
			}
		} else {
			params.selected_ids = app.getRecordId()
		}
		return params;
	},
	/**
	 * Function load modal window
	 * @returns {object}
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
			App.Fields.Picklist.showSelect2ElementView(body.find('.select2'));
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
	 * * @returns {object}
	 */
	registerSubmitForm: function () {
		var thisInstance = this;
		thisInstance.container.on('click', "[name='saveButton']", function (e) {
			let destinyModule = thisInstance.container.find('.js-convert-type option:selected').attr('data-destiny-module');
			let convertType = thisInstance.container.find('.js-convert-type option:selected').val();
			if (convertType) {
				var formData = thisInstance.container.find('form').serializeFormData();
				if (app.getViewName() === 'List') {
					let listInstance = Vtiger_List_Js.getInstance();
					let validationResult = listInstance.checkListRecordSelected();
					if (validationResult != true) {
						var postData = listInstance.getDefaultParams();
						postData.selected_ids = listInstance.readSelectedIds(true);
						postData.excluded_ids = listInstance.readExcludedIds(true);
						postData.cvid = listInstance.getCurrentCvId();

					}
				} else {
					var postData = {
						selected_ids: app.getRecordId(),
					}
				}
				postData.convertType = convertType;
				postData.destinyModule = destinyModule;
				postData.viewInfo = app.getViewName();
				var aDeferred = $.Deferred();
				var progressIndicatorElement = $.progressIndicator({
					blockInfo: {
						enabled: true,
						elementToBlock: thisInstance.container.find('.modal-body')
					}
				});
				AppConnector.request($.extend(formData, postData)).then(function (responseData) {
					progressIndicatorElement.progressIndicator({mode: 'hide'});
					let parseResult = JSON.parse(responseData);
					/*
					if(responseData.result.redirect){
						window.location.href = responseData.result.redirect;
					}*/
					if(parseResult.result.createdRecords){
						Vtiger_Helper_Js.showMessage({
							text: app.vtranslate(parseResult.result.createdRecords),
							type: 'success',
						});
					}
					if (parseResult.result.error) {
						Vtiger_Helper_Js.showMessage({
							text: app.vtranslate(parseResult.result.error),
							type: 'error',
						});
					}
					app.hideModalWindow();
					aDeferred.resolve(responseData);
				}, function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
					progressIndicatorElement.progressIndicator({mode: 'hide'});
				});
				return aDeferred.promise();
			}
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
