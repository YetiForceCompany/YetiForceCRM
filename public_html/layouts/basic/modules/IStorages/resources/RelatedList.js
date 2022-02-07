/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_RelatedList_Js('IStorages_RelatedList_Js', {
	registerEditQty: function () {
		let thisInstance = this;
		let element = this.content.find('.js-edit-qtyproductinstock');
		element.validationEngine(app.validationEngineOptions);
		element.on('change', (e) => {
			e.stopPropagation();
			let element = $(e.currentTarget);
			element.formatNumber();
			if (!element.validationEngine('validate')) {
				AppConnector.request({
					module: thisInstance.parentModuleName,
					record: element.closest('.js-list__row').data('id'),
					action: 'RelationAjax',
					mode: 'setQtyProducts',
					src_record: thisInstance.parentRecordId,
					related_module: thisInstance.moduleName,
					qty: element.val()
				}).done(function (responseData) {
					if (responseData.result) {
						app.showNotify({
							text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
							type: 'success'
						});
					}
				});
			}
		});
	},
	registerPostLoadEvents: function () {
		this._super();
		this.registerEditQty();
	}
});
