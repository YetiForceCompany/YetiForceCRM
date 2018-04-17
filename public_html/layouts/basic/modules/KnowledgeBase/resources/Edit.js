/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
Vtiger_Edit_Js("KnowledgeBase_Edit_Js", {}, {
	loadEditorElement: function (noteContentElement) {
		const customConfig = {};
		if (noteContentElement.is(':visible')) {
			noteContentElement.removeAttr('data-validation-engine');
			customConfig.filebrowserImageUploadUrl = "index.php?module=KnowledgeBase&action=ImageUploadAjax";
			customConfig.extraAllowedContent = true;
			customConfig.removeFormatAttributes = '';
			customConfig.removeFormatTags = '';
			new App.Fields.Text.Editor(noteContentElement, customConfig);
			CKEDITOR.on('dialogDefinition', function (ev) {
				var dialogName = ev.data.name;
				var dialogDefinition = ev.data.definition;
				if (dialogName === 'image') {
					dialogDefinition.onShow = function () {
						const form = $('iframe.cke_dialog_ui_input_file').contents().find('form');
						form.append('<input type="hidden" name="' + csrfMagicName + '" value="' + csrfMagicToken + '">');
					};
				}
			});
		}
	},
	registerEvents: function () {
		this._super();
	}
});
