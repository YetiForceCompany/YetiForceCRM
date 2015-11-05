/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class("Vtiger_CkEditor_Js",{},{
	
	/*
	 *Function to set the textArea element 
	 */
	setElement : function(element){
		this.element = element;
		return this;
	},
	
	/*
	 *Function to get the textArea element
	 */
	getElement : function(){
		return this.element;
	},
	
	/*
	 * Function to return Element's id atrribute value
	 */
	getElementId :function(){
		var element = this.getElement();
		return element.attr('id');
	},
	/*
	 * Function to get the instance of ckeditor
	 */
	
	getCkEditorInstanceFromName :function(){
		var elementName = this.getElementId();
		return CKEDITOR.instances[elementName];
	},
    
    /***
     * Function to get the plain text
     */
    getPlainText : function() {
        var ckEditorInstnace = this.getCkEditorInstanceFromName();
        return ckEditorInstnace.document.getBody().getText();
    },
	/*
	 * Function to load CkEditor
	 * @params : element: element on which CkEditor has to be loaded, config: custom configurations for ckeditor
	 */
	loadCkEditor : function(element,customConfig){
		
		this.setElement(element);
		var instance = this.getCkEditorInstanceFromName();
		var elementName = this.getElementId();
		var config = {}
        
		if(typeof customConfig != 'undefined'){
			var config = jQuery.extend(config,customConfig);
		}
		if(instance)
		{
			CKEDITOR.remove(instance);
		}
		
		
    
		CKEDITOR.replace( elementName,config);
	},
	
	/*
	 * Function to load contents in ckeditor textarea
	 * @params : textArea Element,contents ;
	 */
	loadContentsInCkeditor : function(contents){
		var editor = this.getCkEditorInstanceFromName();
		var editorData = editor.getData();
		var replaced_text = editorData.replace(editorData, contents); 
		editor.setData(replaced_text);	
	}
});
    