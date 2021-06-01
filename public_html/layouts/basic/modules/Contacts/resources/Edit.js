/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Vtiger_Edit_Js(
	'Contacts_Edit_Js',
	{},
	{
		/**
		 * Function to check for Portal User
		 */
		checkForPortalUser: function (form) {
			var element = jQuery('[name="portal"]', form);
			var response = element.is(':checked');
			var primaryEmailField = jQuery('[name="email"]');
			var primaryEmailValue = primaryEmailField.val();
			if (response) {
				if (primaryEmailField.length == 0) {
					app.showNotify({
						text: app.vtranslate('JS_PRIMARY_EMAIL_FIELD_DOES_NOT_EXISTS'),
						type: 'error'
					});
					return false;
				}
				if (primaryEmailValue == '') {
					app.showNotify({
						text: app.vtranslate('JS_PLEASE_ENTER_PRIMARY_EMAIL_VALUE_TO_ENABLE_PORTAL_USER'),
						type: 'info'
					});
					return false;
				}
			}
			return true;
		},

		/**
		 * Function to register recordpresave event
		 */
		registerRecordPreSaveEvent: function (form) {
			var thisInstance = this;
			if (typeof form === 'undefined') {
				form = this.getForm();
			}

			form.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
				var result = thisInstance.checkForPortalUser(form);
				if (!result) {
					e.preventDefault();
				}
			});
		},

		registerBasicEvents: function (container) {
			this._super(container);
			this.registerRecordPreSaveEvent(container);
		}
	}
);
