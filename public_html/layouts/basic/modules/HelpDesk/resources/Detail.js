/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Detail_Js("HelpDesk_Detail_Js", {
	setAccountsReference: function () {
		app.showRecordsList({
			module: "Accounts",
			src_module: "HelpDesk",
			src_record: app.getRecordId()
		}, (modal, instance) => {
			instance.setSelectEvent((responseData) => {
				Vtiger_Detail_Js.getInstance().saveFieldValues({
					field: "parent_id",
					value: responseData.id
				}).done(function (response) {
					location.reload();
				});
			});
		});
	},
}, {
	registerSetServiceContracts: function () {
		var thisInstance = this;
		$('.selectServiceContracts').on('click', 'ul li', function (e) {
			var element = jQuery(e.currentTarget);
			thisInstance.saveFieldValues({
				setRelatedFields: true,
				field: "servicecontractsid",
				value: element.data('id')
			}).done(function (response) {
				location.reload();
			});
		});
	},
	registerEvents: function () {
		this._super();
		this.registerSetServiceContracts();
	}
});
