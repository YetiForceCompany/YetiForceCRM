/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_Edit_Js("KnowledgeBase_Edit_Js", {}, {
	loadCkEditorElement: function (noteContentElement) {
		var customConfig = {};
		if (noteContentElement.is(':visible')) {
			noteContentElement.removeAttr('data-validation-engine');
			customConfig.filebrowserImageUploadUrl = "libraries/iavupload/iavupload.php";
			customConfig.extraAllowedContent = true;
			customConfig.removeFormatAttributes = '';
			customConfig.removeFormatTags = '';
			var ckEditorInstance = new Vtiger_CkEditor_Js();
			ckEditorInstance.loadCkEditor(noteContentElement, customConfig);
		}
	},
	registerEvents: function () {
		this._super();
	}
})
