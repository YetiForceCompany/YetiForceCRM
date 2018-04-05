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
			on: {
				instanceReady: function (evt) {
					evt.editor.on('blur', function () {
						evt.editor.updateElement();
					});
				}
			}
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
