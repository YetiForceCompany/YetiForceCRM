/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
class Vtiger_CkEditor_Js {

	constructor(element, params) {
		if (typeof element !== 'undefined') {
			this.loadCkEditor(element, params);
		}
	}

	/*
	 *Function to set the textArea element 
	 */
	setElement(element) {
		this.element = $(element);
		return this;
	}

	/*
	 *Function to get the textArea element
	 */
	getElement() {
		return this.element;
	}

	/*
	 * Function to return Element's id atrribute value
	 */
	getElementId() {
		return this.getElement().attr('id');
	}


	/*
	 * Function to get the instance of ckeditor
	 */

	getCkEditorInstanceFromName() {
		return CKEDITOR.instances[this.getElementId()];
	}


	/***
	 * Function to get the plain text
	 */
	getPlainText() {
		return this.getCkEditorInstanceFromName().document.getBody().getText();
	}


	/*
	 * Function to load CkEditor
	 * @param {HTMLElement|jQuery} element on which CkEditor has to be loaded
	 * @param {Object} customConfig custom configurations for ckeditor
	 */
	loadCkEditor(element, customConfig) {
		element = $(element).get(0);// we want Dom HTMLElement not wprapped by jQuery - we will wrap this elsewhere
		this.setElement(element);
		const instance = this.getCkEditorInstanceFromName();
		let config = {
			fullPage: false,
			allowedContent: true,
			removeButtons: '',
			scayt_autoStartup: false,
			enterMode: CKEDITOR.ENTER_BR,
			shiftEnterMode: CKEDITOR.ENTER_P,
			on: {
				instanceReady: function (evt) {
					evt.editor.on('blur', function () {
						evt.editor.updateElement();
					});
				}
			},
			extraPlugins: 'colorbutton,colordialog,find,selectall,showblocks,div,print,font,justify,bidi',
			toolbar: 'Full',
			toolbar_Full: [
				{name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']},
				{name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt']},
				{name: 'links', items: ['Link', 'Unlink']},
				{name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak']},
				{name: 'tools', items: ['Maximize', 'ShowBlocks']},
				{name: 'paragraph', items: ['Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv']},
				{name: 'document', items: ['Source', 'Print']},
				'/',
				{name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize']},
				{name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']},
				{name: 'colors', items: ['TextColor', 'BGColor']},
				{
					name: 'paragraph',
					items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']
				},
				{name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat']},
			],
			toolbar_Min: [
				{name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']},
				{name: 'colors', items: ['TextColor', 'BGColor']},
				{name: 'tools', items: ['Maximize']},
				{
					name: 'paragraph',
					items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']
				},
				{name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat']},
			]
		};
		if (typeof customConfig !== 'undefined') {
			config = $.extend(config, customConfig);
		}
		if (instance) {
			CKEDITOR.remove(instance);
		}
		CKEDITOR.replace(element, config);
	}
	/*
	 * Function to load contents in ckeditor textarea
	 * @params : textArea Element,contents ;
	 */
	loadContentsInCkeditor(contents) {
		const editor = this.getCkEditorInstanceFromName();
		editor.setData(editor.getData().replace(editorData, contents));
	}
}