/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Settings_Vtiger_Announcements_Js",{},{
	
	//Contains Announcement container
	container : false,
	
	//return the container of Announcements
	getContainer : function() {
		if(this.container == false){
			this.container = jQuery('#AnnouncementContainer').find('.contents');
		}
		return this.container;
	},
	
	/*
	 * Function to save the Announcement content
	 */
	saveAnnouncement : function(textAreaElement) {
		var aDeferred = jQuery.Deferred();
		
		var content = textAreaElement.val();
		var params = {
			'module' : app.getModuleName(),
			'parent' : app.getParentModuleName(),
			'action' : 'AnnouncementSaveAjax',
			'announcement' : content
		}
		
		AppConnector.request(params).then(
			function(data) {
				aDeferred.resolve();
			},
			function(error,err){
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	
	/*
	 * Function to register keyUp event for text area to show save button
	 */
	registerKeyUpEvent : function() {
		var container = this.getContainer();
		container.one('keyup', '.announcementContent', function(e) {
			jQuery('.saveAnnouncement', container).removeClass('hide');
		});
	},
	
	registerEvents: function() {
		var thisInstance = this;
		var container = thisInstance.getContainer();
		var textAreaElement = jQuery('.announcementContent', container);
		var saveButton = jQuery('.saveAnnouncement', container);
		
		//register text area fields to autosize
		app.registerEventForTextAreaFields(textAreaElement);
		thisInstance.registerKeyUpEvent();
		
		//Register click event for save button
		saveButton.click(function(e) {
			saveButton.addClass('hide');
			var progressIndicatorElement = jQuery.progressIndicator({
				'position' : 'html',
				'blockInfo' : {
					'enabled' : true
				}
			});
			
			//save the new Announcement
			thisInstance.saveAnnouncement(textAreaElement).then(
				function(data) {
					progressIndicatorElement.progressIndicator({'mode' : 'hide'});
					thisInstance.registerKeyUpEvent();
					var params = {
						text: app.vtranslate('JS_ANNOUNCEMENT_SAVED')
					};
					Settings_Vtiger_Index_Js.showMessage(params);
				},
				function(error){
					//TODO: Handle Error
				}
			);
		})
	}

});

jQuery(document).ready(function(e){
	var instance = new Settings_Vtiger_Announcements_Js();
	instance.registerEvents();
})