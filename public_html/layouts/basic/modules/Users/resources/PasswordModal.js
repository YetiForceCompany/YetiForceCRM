/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

$.Class("Users_PasswordModal_JS", {},
	{
		registerValidatePassword: function(form) {
			form.on('click', '.js-validate-password', function(e) {
				AppConnector.request({
					module: 'Users',
					action: 'VerifyData',
					mode: 'validatePassword',
					password: form.find('[name="' + $(e.currentTarget).data('field') + '"]').val()
				}).done(function(data) {
					if (data.success && data.result) {
						Vtiger_Helper_Js.showMessage({
							text: data.result.message,
							type: data.result.type
						});
					}
				});
			});
		},
		registerEvents: function(modal) {
			this.registerValidatePassword(modal);
		}
	}
);
