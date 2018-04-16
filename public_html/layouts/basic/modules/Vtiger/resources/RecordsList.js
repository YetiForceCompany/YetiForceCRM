/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
$.Class("Base_RecordsList_JS", {}, {
	selectEvent: false,
	listSearchInstance: false,
	moduleName: false,
	container: false,
	setSelectEvent: function (cb) {
		this.selectEvent = cb;
	},
	getParams: function () {
		let params = {
			module: this.moduleName,
			view: this.container.data('view'),
			src_module: this.container.find('.parentModule').val(),
			src_record: this.container.find('.sourceRecord').val(),
			src_field: this.container.find('.sourceField').val(),
			related_parent_module: this.container.find('.relatedParentModule').val(),
			related_parent_id: this.container.find('.relatedParentId').val(),
			page: this.container.find('.pageNumber').val(),
			orderby: this.container.find('.orderBy').val(),
			sortorder: this.container.find('.sortOrder').val(),
			multi_select: this.container.find('.multi_select').val(),
			onlyBody: true
		}
		let searchValue = this.listSearchInstance.getAlphabetSearchValue();
		params['search_params'] = JSON.stringify(this.listSearchInstance.getListSearchParams(true));
		if ((typeof searchValue !== "undefined") && (searchValue.length > 0)) {
			params['search_key'] = this.listSearchInstance.getAlphabetSearchField();
			params['search_value'] = searchValue;
			params['operator'] = 's';
		}
		return params;
	},
	loadRecordList: function (params) {
		let body = this.container.find('.modal-body')
		let completeParams = this.getParams();
		console.log($.extend(completeParams, params));
		var aDeferred = $.Deferred();
		var progressIndicatorElement = $.progressIndicator({
			blockInfo: {
				enabled: true,
				elementToBlock: body
			}
		});
		AppConnector.request($.extend(completeParams, params)).then(function (responseData) {
			console.log(responseData);
			progressIndicatorElement.progressIndicator({mode: 'hide'});
			body.html(responseData);
			aDeferred.resolve(responseData);
		}, function (textStatus, errorThrown) {
			aDeferred.reject(textStatus, errorThrown);
			progressIndicatorElement.progressIndicator({mode: 'hide'});
		});
		return aDeferred.promise();
	},
	registerListSearch: function () {
		this.listSearchInstance = YetiForce_ListSearch_Js.getInstance(this.container, false, this, this.moduleName);
		this.listSearchInstance.setViewName('RecordsList');
	},
	registerListEvents: function () {
		var thisInstance = this;
		thisInstance.container.on('click', '.listViewEntries', function (e) {
			let row = $(this);
			app.hideModalWindow(false, thisInstance.container.parent().attr('id'));
			thisInstance.selectEvent(row.data());
		});
	},
	registerEvents: function (modalContainer) {
		this.container = modalContainer;
		this.moduleName = this.container.data('module');
		this.registerListSearch();
		this.registerListEvents();
		// console.log('registerEvents', modalContainer);
	}
});