/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class("Vtiger_TreePopup_Js",{

    getInstance: function(){
	    var module = app.getModuleName();
		var className = jQuery('#popUpClassName').val();
		if(typeof className != 'undefined'){
			var moduleClassName = className;
		}else{
			var moduleClassName = module+"_TreePopup_Js";
		}
		var fallbackClassName = Vtiger_TreePopup_Js;
	    if(typeof window[moduleClassName] != 'undefined'){
			var instance = new window[moduleClassName]();
		}else{
			var instance = new fallbackClassName();
		}
	    return instance;
	}

},{
	//holds the event name that child window need to trigger
	eventName : '',
	popupPageContentsContainer : false,
	jstreeInstance : false,

	setEventName : function(eventName) {
		this.eventName = eventName;
	},
	
	getEventName : function() {
		return this.eventName;
	},
	
	getPopupPageContainer : function(){
		if(this.popupPageContentsContainer == false) {
			this.popupPageContentsContainer = jQuery('#treePopupContainer');
		}
		return this.popupPageContentsContainer;

	},
	
	done : function(result, eventToTrigger, window) {
		if(typeof eventToTrigger == 'undefined' || eventToTrigger.length <=0 ) {
			eventToTrigger = 'postSelection'
		}
		if(typeof window == 'undefined'){
			window = self;
		}
		window.close();
        var data = JSON.stringify(result);
        // Because if we have two dollars like this "$$" it's not working because it'll be like escape char(Email Templates)
        data = data.replace(/\$\$/g,"$ $");
		jQuery.triggerParentEvent(eventToTrigger, data);
	},
	
	generateTree : function() {
		var thisInstance = this;
		if(thisInstance.jstreeInstance == false) {
			var treeValues = $('#treePopupValues').val();
			var data = JSON.parse(treeValues);
			thisInstance.jstreeInstance = $("#treePopupContents");
			thisInstance.jstreeInstance.jstree({ 
				core: {
					data: data
				}
			})
			.on('select_node.jstree', function (event, data) {
				thisInstance.registerSelect(data.node);
			});
		}
		return this.jstreeInstance;
	},
	
	registerSelect : function(obj){
		var thisInstance = this;
		var recordData = {id: obj.id, name: obj.text}
		thisInstance.done(recordData, thisInstance.getEventName());
	},
	
	registerEvents: function(){
		this.generateTree();
	}
});
jQuery(document).ready(function() {
	var popupInstance = Vtiger_TreePopup_Js.getInstance();
	var triggerEventName = jQuery('.triggerEventName').val();
	var documentHeight = (jQuery(document).height())+'px';
	jQuery('#popupPageContainer').css('height',documentHeight);
	popupInstance.setEventName(triggerEventName);
	popupInstance.registerEvents();
	Vtiger_Helper_Js.showHorizontalTopScrollBar();
});
