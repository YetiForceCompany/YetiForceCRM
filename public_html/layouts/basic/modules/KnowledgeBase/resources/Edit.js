/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Edit_Js(
	'KnowledgeBase_Edit_Js',
	{},
	{
		loadEditorElement: function (noteContentElement) {
			const customConfig = {};
			if (noteContentElement.is(':visible')) {
				noteContentElement.removeAttr('data-validation-engine');
				customConfig.extraAllowedContent = true;
				customConfig.removeFormatAttributes = '';
				customConfig.removeFormatTags = '';
				App.Fields.Text.Editor.register(noteContentElement, customConfig);
			}
		},
		registerEvents: function () {
			this._super();
		}
	}
);
