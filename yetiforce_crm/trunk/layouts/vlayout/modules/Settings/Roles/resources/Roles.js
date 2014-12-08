/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
var Settings_Roles_Js = {
	
	newPriviliges : false,
	
	initDeleteView: function() {
		jQuery('#roleDeleteForm').validationEngine(app.validationEngineOptions);
		
		jQuery('[data-action="popup"]').on('click',function(e) {
			e.preventDefault();
			var target = $(e.currentTarget);
			var field  = target.data('field');
			
			// TODO simiplify by pushing the retrieveSelectedRecords to library
			var popupInstance = Vtiger_Popup_Js.getInstance();
			popupInstance.show(target.data('url'));
			popupInstance.retrieveSelectedRecords(function(data) {
				try {
					data = JSON.parse(data);
				} catch (e) {}
				
				if (typeof data == 'object') {
					jQuery('[name="'+field+'_display"]').val(data.label);
					data = data.value;
				}
				jQuery('[name="'+field+'"]').val(data);
			});
		});
		
		jQuery('#clearRole').on('click',function(e){
			jQuery('[name="transfer_record_display"]').val('');
		});
	},
	
	initPopupView: function() {
		jQuery('.roleEle').click(function(e){
			var target = $(e.currentTarget);
			// jquery_windowmsg plugin expects second parameter to be string.
			jQuery.triggerParentEvent('postSelection', JSON.stringify({value: target.closest('li').data('roleid'), label: target.text()}));
			self.close();
		});
	},
	
	initEditView: function() {
		
		function applyMoveChanges(roleid, parent_roleid) {
			var params = {
				module: 'Roles',
				action: 'MoveAjax',
				parent: 'Settings',
				record: roleid,
				parent_roleid: parent_roleid
			}
			
			AppConnector.request(params).then(function(res) {
				if (!res.success) {
					alert(app.vtranslate('JS_FAILED_TO_SAVE'));
					window.location.reload();
				}
			});
		}
		
		jQuery('[rel="tooltip"]').tooltip();
		
		function modalActionHandler(event) {
			var target = $(event.currentTarget);
			app.showModalWindow(null, target.data('url'),function(data){
				Settings_Roles_Js.initDeleteView();
			});
		}
		
		jQuery('[data-action="modal"]').click(modalActionHandler);
		
		jQuery('.toolbar').hide();
		
		jQuery('.toolbar-handle').bind('mouseover', function(e){
			var target = $(e.currentTarget);
			jQuery('.toolbar', target).css({display: 'inline'});
		});
		jQuery('.toolbar-handle').bind('mouseout', function(e){
			var target = $(e.currentTarget);
			jQuery('.toolbar', target).hide();
		});
		
		jQuery('.draggable').draggable({
			containment: '.treeView',
			start : function(event, ui) {
				var container = jQuery(ui.helper);
				var referenceid = container.data('refid');
				var sourceGroup = jQuery('[data-grouprefid="'+referenceid+'"]');
				var sourceRoleId = sourceGroup.data('roleid');
				if(sourceRoleId == 'H5' || sourceRoleId == 'H2') {
					var params = {};
					params.title = app.vtranslate('JS_PERMISSION_DENIED');
					params.text = app.vtranslate('JS_NO_PERMISSIONS_TO_MOVE');
					params.type = 'error';
					Settings_Vtiger_Index_Js.showMessage(params);
				}
			},
			helper: function(event) {
				var target = $(event.currentTarget);
				var targetGroup = target.closest('li');
				var timestamp = +(new Date());

				var container = $('<div/>');
				container.data('refid', timestamp);
				container.html(targetGroup.clone());

				// For later reference we shall assign the id before we return
				targetGroup.attr('data-grouprefid', timestamp);

				return container;
			}
		});
		jQuery('.droppable').droppable({
			hoverClass: 'btn-primary',
			tolerance: 'pointer',
			drop: function(event, ui) {
				var container = $(ui.helper);
				var referenceid = container.data('refid');
				var sourceGroup = $('[data-grouprefid="'+referenceid+'"]');
				
				var thisWrapper = $(this).closest('div');

				var targetRole  = thisWrapper.closest('li').data('role');
				var targetRoleId= thisWrapper.closest('li').data('roleid');
				var sourceRole   = sourceGroup.data('role');
				var sourceRoleId = sourceGroup.data('roleid');

				// Attempt to push parent-into-its own child hierarchy?
				if (targetRole.indexOf(sourceRole) == 0) {
					// Sorry
					return;
				}
				//Attempt to move the roles CEO and Sales Person
				if (sourceRoleId == 'H5' || sourceRoleId == 'H2') {
					return;
				}
				sourceGroup.appendTo(thisWrapper.next('ul'));

				applyMoveChanges(sourceRoleId, targetRoleId);
			}
		});
	},
	
	registerShowNewProfilePrivilegesEvent : function() {
		jQuery('[name="profile_directly_related_to_role"]').on('change',function(e){
			var target = jQuery(e.currentTarget);
			var hanlder = target.data('handler');
			if(hanlder == 'new'){
				Settings_Roles_Js.getProfilePriviliges();return false;
			}
			var container = jQuery('[data-content="'+ hanlder + '"]');
			jQuery('[data-content]').not(container).fadeOut('slow',function(){
				container.fadeIn('slow');
			});
		})
	},
	
	onLoadProfilePrivilegesAjax : function() {
		jQuery('[name="profile_directly_related_to_role"]:checked').trigger('change');
	},
	
	getProfilePriviliges : function() {
		var content = jQuery('[data-content="new"]');
		var profileId = jQuery('[name="profile_directly_related_to_role_id"]').val();
		var params = {
			module : 'Profiles',
			parent: 'Settings',
			view : 'EditAjax',
			record : profileId
		}
		if(Settings_Roles_Js.newPriviliges == true) {
			jQuery('[data-content="existing"]').fadeOut('slow',function(){
				content.fadeIn('slow');
			});
			return false;
		}
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		AppConnector.request(params).then(function(data) {
			content.find('.fieldValue').html(data);
			app.changeSelectElementView(jQuery('#directProfilePriviligesSelect'), 'select2');
			Settings_Roles_Js.registerExistingProfilesChangeEvent();
			progressIndicatorElement.progressIndicator({
				'mode' : 'hide'
			});
			Settings_Roles_Js.newPriviliges = true;
			jQuery('[data-content="existing"]').fadeOut('slow',function(){
				content.fadeIn('slow',function(){
				});
			});
		})
	},
	
	registerExistingProfilesChangeEvent : function() {
		jQuery('#directProfilePriviligesSelect').on('change',function(e) {
			var profileId = jQuery(e.currentTarget).val();
			var params = {
				module : 'Profiles',
				parent: 'Settings',
				view : 'EditAjax',
				record : profileId
			}
			var progressIndicatorElement = jQuery.progressIndicator({
				'position' : 'html',
				'blockInfo' : {
					'enabled' : true
				}
			});
			
			AppConnector.request(params).then(function(data) {
				jQuery('[data-content="new"]').find('.fieldValue').html(data);
				progressIndicatorElement.progressIndicator({
					'mode' : 'hide'
				});
				app.changeSelectElementView(jQuery('#directProfilePriviligesSelect'), 'select2');
				Settings_Roles_Js.registerExistingProfilesChangeEvent();
			});
		});
	},
	
	registerSubmitEvent : function() {
		var thisInstance = this;
		var form = jQuery('#EditView');
		form.on('submit',function(e) {
			if(form.data('submit') == 'true' && form.data('performCheck') == 'true') {
				return true;
			} else {
				if(jQuery('[data-handler="existing"]').is(':checked')){
					var selectElement = jQuery('#profilesList');
					var select2Element = app.getSelect2ElementFromSelect(selectElement);
					var result = Vtiger_MultiSelect_Validator_Js.invokeValidation(selectElement);
					if(result != true){
						select2Element.validationEngine('showPrompt', result , 'error','bottomLeft',true);
						e.preventDefault();
						return;
					} else {
						select2Element.validationEngine('hide');
					}
				} 
				
				if(form.data('jqv').InvalidFields.length <= 0) {
					var formData = form.serializeFormData();
					thisInstance.checkDuplicateName({
						'rolename' : formData.rolename,
						'record' : formData.record
					}).then(
						function(data){
							form.data('submit', 'true');
							form.data('performCheck', 'true');
							form.submit();
							jQuery.progressIndicator({
								'blockInfo' : {
								'enabled' : true
								}
							});
						},
						function(data, err){
							var params = {};
							params['text'] = data['message'];
							params['type'] = 'error';
							Settings_Vtiger_Index_Js.showMessage(params);
							return false;
						}
					);
				} else {
					//If validation fails, form should submit again
					form.removeData('submit');
					// to avoid hiding of error message under the fixed nav bar
					app.formAlignmentAfterValidation(form);
				}
				e.preventDefault();
			}
		});
	},
	
	/*
	 * Function to check Duplication of Role Names
	 * returns boolean true or false
	 */

	checkDuplicateName : function(details) {
		var aDeferred = jQuery.Deferred();
		
		var params = {
		'module' : app.getModuleName(),
		'parent' : app.getParentModuleName(),
		'action' : 'EditAjax',
		'mode'   : 'checkDuplicate',
		'rolename' : details.rolename,
		'record' : details.record
		}
		
		AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				var result = response['success'];
				if(result == true) {
					aDeferred.reject(response);
				} else {
					aDeferred.resolve(response);
				}
			},
			function(error,err){
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	
	registerEvents : function() {
		Settings_Roles_Js.initEditView();
		Settings_Roles_Js.registerShowNewProfilePrivilegesEvent();
		Settings_Roles_Js.onLoadProfilePrivilegesAjax();
		Settings_Roles_Js.registerSubmitEvent();
	}
}
jQuery(document).ready(function(){
	Settings_Roles_Js.registerEvents();
})