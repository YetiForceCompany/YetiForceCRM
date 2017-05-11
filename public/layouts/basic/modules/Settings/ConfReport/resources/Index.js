/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

jQuery.Class("Settings_ConfReport_Index_Js", {}, {
	/*
	 * Shows or hides block informing about supported currencies by presently chosen bank
	 */
	registerTestButton: function (container) {
		container.find('.testSpeed').on('click', function () {
			var progress = jQuery.progressIndicator({
				message: app.vtranslate('JS_SPEED_TEST_START'),
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				parent: 'Settings',
				module: 'ConfReport',
				view: 'Speed'
			}).then(function (response) {
				app.showModalWindow(response, function (data) {
					
				});
				progress.progressIndicator({mode: 'hide'});
			}, function (data, err) {
				progress.progressIndicator({mode: 'hide'});
			})

		});
	},
	/**
	 * Register events
	 */
	registerEvents: function () {
		var container = jQuery('.contentsDiv');
		this.registerTestButton(container);
	}
});
