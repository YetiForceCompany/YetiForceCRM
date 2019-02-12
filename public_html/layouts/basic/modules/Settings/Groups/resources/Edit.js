/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Settings_Vtiger_Edit_Js('Settings_Groups_Edit_Js', {}, {
	memberSelectElement: false,

	getMemberSelectElement: function () {
		if (this.memberSelectElement == false) {
			this.memberSelectElement = jQuery('#memberList');
		}
		return this.memberSelectElement;
	},
	/**
	 * Function to register form for validation
	 */
	registerFormForValidation: function () {
		var editViewForm = this.getForm();
		editViewForm.validationEngine(app.getvalidationEngineOptions(true));
	},

	/**
	 * Function to register the submit event of form
	 */
	registerSubmitEvent: function () {
		var thisInstance = this;
		var form = jQuery('#EditView');
		form.on('submit', function (e) {
			if (form.data('submit') == 'true' && form.data('performCheck') == 'true') {
				return true;
			} else {
				if (form.data('jqv').InvalidFields.length <= 0) {
					var formData = form.serializeFormData();
					thisInstance.checkDuplicateName({
						'groupname': formData.groupname,
						'record': formData.record
					}).done(function (data) {
						form.data('submit', 'true');
						form.data('performCheck', 'true');
						form.submit();
					}).fail(function (data, err) {
						var params = {};
						params['text'] = data['message'];
						params['type'] = 'error';
						Settings_Vtiger_Index_Js.showMessage(params);
						return false;
					});
				} else {
					//If validation fails, form should submit again
					form.removeData('submit');
					app.formAlignmentAfterValidation(form);
				}
				e.preventDefault();
			}
		});
	},

	/*
	 * Function to check Duplication of Group Names
	 * returns boolean true or false
	 */
	checkDuplicateName: function (details) {
		var aDeferred = jQuery.Deferred();

		var params = {
			'module': app.getModuleName(),
			'parent': app.getParentModuleName(),
			'action': 'EditAjax',
			'mode': 'checkDuplicate',
			'groupname': details.groupname,
			'record': details.record
		}

		AppConnector.request(params).done(function (data) {
			var response = data['result'];
			var result = response['success'];
			if (result == true) {
				aDeferred.reject(response);
			} else {
				aDeferred.resolve(response);
			}
		}).fail(function (error, err) {
			aDeferred.reject(error, err);
		});
		return aDeferred.promise();
	},
	/**
	 * Register events for section "modules"
	 */
	registerButtonsModule: function(){
		const editViewForm = this.getForm();
		editViewForm.find('.js-modules-select-all, .js-modules-deselect-all').on('click', function(e){
			$('#modulesList option').prop('selected', $(this).hasClass('js-modules-select-all')).parent().trigger('change');
		});
	},
	/**
	 * Function which will handle the registrations for the elements
	 */
	registerEvents: function () {
		this._super();
		this.registerSubmitEvent();
		this.registerButtonsModule()
	}
});
