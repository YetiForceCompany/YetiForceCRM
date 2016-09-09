/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_EditFieldByModal_Js("Assets_EditFieldByModal_Js", {}, {
	registerInvoicesTabFromAcocunt: function () {
		var thisInstance = this;
		var form = this.getForm();
		var relatedRecord = form.find('.relatedRecord')
		if (!relatedRecord.length) {
			return false;
		}
		var params = {
			'module': form.find('.relatedModuleBasic').val(),
			'view': 'Detail',
			'mode': 'showRelatedList',
			'relatedModule': form.find('.relatedModule').val(),
			'limit': 'no_limit',
			'record': relatedRecord.val()
		}
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'replace',
			'blockInfo': {
				'enabled': true,
				'elementToBlock': form.find('.relatedRecordsContents')
			}
		});
		AppConnector.request(params).then(
				function (data) {
					data = jQuery(data);
					data.find('.relatedContents .relationDelete, .relatedContents .favorites').remove();
					var totalCount = data.find('table .listViewEntries');
					var searchButton = data.find('.searchField');
					if (searchButton.length) {
						searchButton.closest('tr').remove();
					}
					if (totalCount.length == 0) {
						form.find('.relatedRecordsContents .message').removeClass('hide');
						progressIndicatorElement.progressIndicator({'mode': 'hide'})
						return;
					} else if (totalCount > 5) {
						app.showScrollBar(form.find('.relatedContents'), {
							height: '250px'
						});
					}
					form.find('.relatedRecordsContents').append(data.find('.relatedContents').append('<br>'));
					form.on('click', '.relatedContents .listViewEntries td', function (e) {
						var target = jQuery(e.target);
						var row = target.closest('tr');
						var inventoryRow = row.next();
						if (inventoryRow.hasClass('listViewInventoryEntries') && !target.closest('div').hasClass('actions') && !target.is('a') && !target.is('input')) {
							inventoryRow.toggleClass('hide');
						}
					});
					progressIndicatorElement.progressIndicator({'mode': 'hide'})
				},
				function (error) {
				}
		);
	},
	registerEvents: function () {
		this._super();
		this.registerInvoicesTabFromAcocunt();
	}

});
