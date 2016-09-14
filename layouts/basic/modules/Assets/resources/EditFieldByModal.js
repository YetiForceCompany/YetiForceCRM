/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_EditFieldByModal_Js("Assets_EditFieldByModal_Js", {}, {
	relatedRecord: false,
	registerTabFromAcocunt: function () {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var form = this.getForm();
		var relatedRecord = form.find('.relatedRecord')
		if (!relatedRecord.length) {
			aDeferred.resolve(form);
			return false;
		}
		var modules = form.find('.relatedModule').val();
		modules = modules ? $.parseJSON(modules) : [];
		for (var i in modules) {
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': 'replace',
				'blockInfo': {
					'enabled': true,
					'elementToBlock': form.find('.relatedRecordsContents')
				}
			});
			thisInstance.getRelatedData(modules[i]).then(function (data) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'})
				aDeferred.resolve(form);
			});
		}
		return aDeferred.promise();
	},
	getRelatedData: function (module) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var moduleNameToLower = module.toLowerCase();
		var form = this.getForm();
		var params = {
			'module': form.find('.relatedModuleBasic').val(),
			'view': 'Detail',
			'mode': module == 'ModComments' ? 'showRecentComments' : 'showRelatedList',
			'relatedModule': module,
			'limit': 'no_limit',
			'record': this.relatedRecord ? this.relatedRecord : form.find('.relatedRecord').val()
		}
		AppConnector.request(params).then(
				function (data) {
					data = jQuery(data);
					var container = form.find('.relatedRecordsContents #' + moduleNameToLower)
					if (module == 'ModComments') {
						thisInstance.showCommentData(data.find('.commentsBody'), container);
					} else {
						thisInstance.showRelatedData(data, container);
					}
					aDeferred.resolve(container);
				},
				function (error) {
				}
		);
		return aDeferred.promise();
	},
	registerHierarchyAcocunt: function () {
		var thisInstance = this;
		var form = this.getForm();
		var hierarchyId = form.find('.hierarchyId').val();
		if (!hierarchyId) {
			return false;
		}
		var params = {
			module: this.moduleName,
			fields: form.find('.hierarchyField').val(),
			view: 'GetHierarchy',
			record: hierarchyId
		}
		var hierarchyContainer = form.find('.hierarchyContainer');
		AppConnector.request(params).then(
				function (data) {
					data = jQuery(data).find('.modal-body');
					data.find('tr').removeClass('bgAzure').addClass('cursorPointer');
					data.find('tr[data-id="' + hierarchyId + '"]').addClass('bgAzure');
					thisInstance.registerHierarchyEvent(data);
					hierarchyContainer.html(data);
				}
		);
	},
	registerHierarchyEvent: function (data) {
		var thisInstance = this;
		data.find('tr').on('click', function (e) {
			var trElement = jQuery(e.currentTarget);
			trElement.closest('tbody').find('tr').removeClass('bgAzure');
			trElement.addClass('bgAzure');
			thisInstance.relatedRecord = trElement.data('id');
			thisInstance.registerTabFromAcocunt().then(function () {
				thisInstance.relatedRecord = false;
			});
		});
	},
	showCommentData: function (data, container) {
		var thisInstance = this;
		data.addClass('commentContainer');
		data.find('.commentActionsContainer').remove();
		container.html(data);
		if (data.find('.singleComment').length > 6) {
			app.showScrollBar(container.children(), {
				height: '350px'
			});
		}
	},
	showRelatedData: function (data, container) {
		var thisInstance = this;
		var form = this.getForm();
		data.find('.relatedContents .relationDelete, .relatedContents .favorites').remove();
		var totalCount = data.find('table .listViewEntries');
		var searchButton = data.find('.searchField');
		if (searchButton.length) {
			searchButton.closest('tr').remove();
		}
		if (totalCount.length == 0) {
			var message = form.find('.message').clone(true, true);
			container.html(message.removeClass('message hide'));
			return false;
		}
		container.html(data.find('.relatedContents'));
		if (totalCount.length > 10) {
			app.showScrollBar(container.children(), {
				height: '300px'
			});
		}
		container.find('.relatedContents .listViewEntries td').on('click', function (e) {
			var target = jQuery(e.target);
			var row = target.closest('tr');
			var inventoryRow = row.next();
			if (inventoryRow.hasClass('listViewInventoryEntries') && !target.closest('div').hasClass('actions') && !target.is('a') && !target.is('input')) {
				inventoryRow.toggleClass('hide');
			}
		});
	},
	registerEvents: function () {
		this._super();
		this.registerHierarchyAcocunt();
		this.registerTabFromAcocunt();
	}

});
