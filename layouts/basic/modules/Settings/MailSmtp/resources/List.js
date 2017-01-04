/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Settings_Vtiger_List_Js('Settings_MailSmtp_List_Js', {}, {
	container: false,
	getContainer: function () {
		if (this.container == false) {
			this.container = jQuery('div.contentsDiv');
		}
		return this.container;
	},
	registerModalEvents: function (data, url) {
		var form = data.find('form');
		form.on('submit', function (e) {
			e.preventDefault();
			var params = form.serializeFormData();
			app.saveAjax('save', params).then(function (respons) {
				app.hideModalWindow();
				var params = {};
				params['text'] = respons.result['success'];
				Settings_Vtiger_Index_Js.showMessage(params);

			});
		})
	},
	showEditView: function(url){
		app.showModalWindow(null, url, function (data) {
			thisInstance.registerModalEvents(data, url);
		});
	},
	registerEvents: function () {
		var thisInstance = this;
		this._super();
	//	this.showEditView();
		this.getContainer().find('.addRecord').on('click', function (e) {
			var button = jQuery(e.currentTarget);
			var url = button.data('url');
			app.showModalWindow(null, url, function (data) {
				thisInstance.registerModalEvents(data, url);
			});
		});
	}
})
