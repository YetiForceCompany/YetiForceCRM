/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_Edit_Js("KnowledgeBase_Edit_Js", {}, {
	setCKEDTIOR: function() {
		var editor = $('.ckEditorSource').attr('id');
		CKEDITOR.instances[editor].removeAllListeners();
		delete CKEDITOR.instances[editor];
		CKEDITOR.replace(editor, {
			"filebrowserImageUploadUrl": "libraries/iavupload/iavupload.php"
		});
		CKEDITOR.config.extraAllowedContent = true;
		CKEDITOR.config.removeFormatAttributes = '';
		CKEDITOR.config.removeFormatTags = '';
	},
	registerEvents: function () {
		this._super();
		this.setCKEDTIOR();
	}
})
