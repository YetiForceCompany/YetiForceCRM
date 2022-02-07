/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Vtiger_RelatedCommentModal_Js', {
	windowParent: app.getWindowParent(),
	/*
	 * Function to register the click event for generate button
	 */
	registerSubmitEvent: function (container) {
		const self = this;
		container.find('[name="saveButton"]').on('click', function (e) {
			var progressLoader = $.progressIndicator({
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
			AppConnector.request(params)
				.done(function (data) {
					Vtiger_Helper_Js.showMessage({ text: data.result });
					app.hideModalWindow();
					progressLoader.progressIndicator({ mode: 'hide' });
					self.windowParent.Vtiger_Detail_Js.getInstance().reloadTabContent();
				})
				.fail(function (error) {
					progressLoader.progressIndicator({ mode: 'hide' });
				});
		});
	},
	registerEvents: function () {
		var container = $('#modalRelatedCommentModal');
		new App.Fields.Text.Completions(container.find('.js-completions'));
		this.registerSubmitEvent(container);
	}
});
$(function () {
	var instance = new Vtiger_RelatedCommentModal_Js();
	instance.registerEvents();
});
