/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_RelatedList_Js(
	'PriceBooks_RelatedList_Js',
	{
		triggerMassMargin: function () {
			const self = Vtiger_RelatedList_Js.relatedListInstance;
			let selected_ids = self.readSelectedIds(true),
				excluded_ids = self.readExcludedIds(true);
			if (self.checkListRecordSelected() !== true) {
				app.showModalWindow({
					url: 'index.php?module=PriceBooks&view=SpecifyMargin',
					cb: (modalContainer) => {
						modalContainer.find('.js-modal__save').on('click', (e) => {
							let element = modalContainer.find('.js-margin');
							let resultOfValidation = Vtiger_NumberUserFormat_Validator_Js.invokeValidation(element);
							if (typeof resultOfValidation !== 'undefined') {
								element.validationEngine('showPrompt', resultOfValidation, '', 'topLeft', true);
								e.preventDefault();
							} else {
								element.validationEngine('hideAll');
								let postData = self.getCompleteParams();
								delete postData.view;
								postData.selected_ids = selected_ids;
								postData.excluded_ids = excluded_ids;
								postData.mode = 'specifyMargin';
								postData.action = 'RelationAjax';
								postData.relatedModule = self.moduleName;
								postData.record = self.getParentId();
								postData.margin = element.val();
								AppConnector.request(postData).done((response) => {
									if (response.success) {
										Vtiger_Detail_Js.reloadRelatedList();
										app.hideModalWindow();
									}
								});
							}
							return false;
						});
					}
				});
			} else {
				self.noRecordSelectedAlert();
			}
		}
	},
	{
		registerEditListPrice: function () {
			let thisInstance = this;
			let element = this.content.find('.js-edit-listprice');
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
						mode: 'addListPrice',
						src_record: thisInstance.parentRecordId,
						related_module: thisInstance.moduleName,
						price: element.val()
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
			this.registerEditListPrice();
		}
	}
);
