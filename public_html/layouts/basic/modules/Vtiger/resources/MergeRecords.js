/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
$.Class("Base_MergeRecords_JS", {}, {
	/**
	 * Modal container
	 */
	container: false,
	/**
	 * Register list events
	 */
	registerListEvents: function () {
		this.container.find('[name="record"]').on('change', (e) => {
			var id = $(e.currentTarget).val();
			this.container.find('input[value=' + id + ']').trigger('click');
		});
		this.container.find('[type="submit"]').on('click', (e) => {
			e.preventDefault;
			AppConnector.request(this.container.find('form').serializeFormData()).then(
					function (data) {
						if (data.result === false) {
							Vtiger_Helper_Js.showPnotify({text: app.vtranslate('JS_ERROR')});
						}
						app.hideModalWindow();
						const listInstance = new Vtiger_List_Js();
						listInstance.getListViewRecords();
						Vtiger_List_Js.clearList();
					}
			);
		});
	},
	/**
	 * Register modal events
	 * @param {jQuery} modalContainer
	 */
	registerEvents: function (modalContainer) {
		this.container = $(modalContainer);
		this.registerListEvents();
	}
});
