/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_RelatedList_Js("PriceBooks_RelatedList_Js", {}, {
	registerEditListPrice: function () {
		let thisInstance = this;
		let element = this.content.find('.js-edit-listprice');
		element.validationEngine(app.validationEngineOptions);
		element.on('change', function (e) {
			e.stopPropagation();
			let element = $(this);
			element.formatNumber();
			if (!element.validationEngine('validate')) {
				AppConnector.request({
					module: thisInstance.parentModuleName,
					record: element.closest('.js-list__row').data('id'),
					action: 'RelationAjax',
					mode: 'addListPrice',
					src_record: thisInstance.parentRecordId,
					related_module: thisInstance.moduleName,
					price: element.val()
				}).done(function (responseData) {
					if (responseData.result) {
						Vtiger_Helper_Js.showPnotify({text: app.vtranslate('JS_SAVE_NOTIFY_OK'), type: 'success'});
					}
				});
			}
		});
	},
	registerPostLoadEvents: function () {
		this._super();
		this.registerEditListPrice();
	},
});
