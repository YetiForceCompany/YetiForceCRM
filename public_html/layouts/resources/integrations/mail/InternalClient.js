/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
Integrations_Mail_Base.driver = 'InternalClient';
/**
 * @classdesc Internal client mail integrations class.
 * @class
 */
window.Integrations_Mail_InternalClient = class Integrations_Mail_InternalClient extends Integrations_Mail_Base {
	/** @inheritdoc */
	sendMail(attr) {
		this.log('|►| sendMail', attr);
		if (attr['record'] && app.getRecordId() && attr['record'] !== app.getRecordId()) {
			attr['crmModule'] = app.getModuleName();
			attr['crmRecord'] = app.getRecordId();
		}
		AppConnector.request({
			dataType: 'json',
			data: {
				module: 'AppComponents',
				action: 'Mail',
				mode: 'sendMail',
				...attr
			}
		}).done(function (response) {
			if (response.result.status) {
				if (attr['record'] && app.getRecordId() && attr['record'] !== app.getRecordId()) {
					response.result.url += '&crmModule=' + attr['crmModule'] + '&crmRecord=' + attr['crmRecord'];
				}
				Vtiger_Index_Js.sendMailWindow(response.result.url, response.result.popup);
			} else {
				app.showError({ title: app.vtranslate('JS_UNEXPECTED_ERROR'), text: response.result.text });
			}
		});
	}
};
