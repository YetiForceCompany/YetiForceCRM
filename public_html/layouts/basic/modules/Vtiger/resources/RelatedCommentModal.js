/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
jQuery.Class("Vtiger_RelatedCommentModal_Js", {
	windowParent: app.getWindowParent(),
	/*
	 * Function to register the click event for generate button
	 */
	registerSubmitEvent: function (container) {
		const self = this;
		container.find('[name="saveButton"]').on('click', function (e) {
			var progressLoader = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var comment = container.find('.comment').val();
			var params = {
				module: self.windowParent.app.getModuleName(),
				record: self.windowParent.app.getRecordId(),
				action: 'RelatedCommentModal',
				mode: 'update',
				comment: comment,
				relid: container.find('.relatedRecord').val(),
				relmodule: container.find('.relatedModuleName').val()
			};
			AppConnector.request(params).then(function (data) {
				Vtiger_Helper_Js.showMessage({text: data.result});
				app.hideModalWindow();
				progressLoader.progressIndicator({mode: 'hide'});
				self.windowParent.Vtiger_Detail_Js.getInstance().reloadTabContent();
			}, function (error) {
				progressLoader.progressIndicator({mode: 'hide'});
			});
		});
	},
	registerEvents: function () {
		var container = jQuery('#modalRelatedCommentModal');
		this.registerSubmitEvent(container);
	}
});
jQuery(function () {
	var instance = new Vtiger_RelatedCommentModal_Js();
	instance.registerEvents();
});
