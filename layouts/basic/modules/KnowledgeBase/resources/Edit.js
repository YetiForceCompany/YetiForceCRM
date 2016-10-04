/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_Edit_Js("KnowledgeBase_Edit_Js", {}, {
	loadCkEditorElement: function (noteContentElement) {
		var customConfig = {};
		if (noteContentElement.is(':visible')) {
			noteContentElement.removeAttr('data-validation-engine');
			customConfig.filebrowserImageUploadUrl = "index.php?module=KnowledgeBase&action=ImageUploadAjax";
			customConfig.extraAllowedContent = true;
			customConfig.removeFormatAttributes = '';
			customConfig.removeFormatTags = '';
			var ckEditorInstance = new Vtiger_CkEditor_Js();
			ckEditorInstance.loadCkEditor(noteContentElement, customConfig);
			CKEDITOR.on('dialogDefinition', function(ev) {
				var dialogName = ev.data.name; 
				var dialogDefinition = ev.data.definition; 
				if (dialogName == 'image') {
					dialogDefinition.onShow = function () {
						var form = $('iframe.cke_dialog_ui_input_file').contents().find('form');
						form.append('<input type="hidden" name="' + csrfMagicName + '" value="' + csrfMagicToken + '">');
					};
				} 
			});
		}
	},
	registerEvents: function () {
		this._super();
	}
})
