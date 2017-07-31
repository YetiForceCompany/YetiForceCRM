/* {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} */
Vtiger_Detail_Js("HelpDesk_Detail_Js", {
	setAccountsReference: function () {
		var params = {module: "Accounts", view: "Popup", src_module: "HelpDesk", src_record: app.getRecordId()};
		var popupInstance = Vtiger_Popup_Js.getInstance();
		popupInstance.show(params, function (data) {
			var responseData = JSON.parse(data);
			$.each(responseData, function (key, element) {
				var instance = Vtiger_Detail_Js.getInstance();
				instance.saveFieldValues({
					field: "parent_id",
					value: key
				}).then(function (response) {
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
			}).then(function (response) {
				location.reload();
			});
		});
	},
	registerEvents: function () {
		this._super();
		this.registerSetServiceContracts();
	}
});
