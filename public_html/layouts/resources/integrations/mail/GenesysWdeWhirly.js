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
		if (attr['record'] && app.getRecordId() && attr['record'] != app.getRecordId()) {
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
		}).done((response) => {
			if (response.result.status) {
				$.ajax({ url: response.result.url, headers: { 'Token-Api': response.result.token } })
					.done((ajax) => {
						this.log('|◄| sendMail', ajax);
						if (ajax['data']['status'] == 1) {
							app.showNotify({ title: ajax['data']['description'], type: 'success' });
						} else {
							app.showError({
								title: app.vtranslate('JS_UNEXPECTED_ERROR'),
								text: ajax['data']['description']
							});
						}
					})
					.fail((_jqXHR, textStatus) => {
						app.showError({
							title: app.vtranslate('JS_UNEXPECTED_ERROR'),
							text: textStatus
						});
					});
			} else {
				app.showError({ title: app.vtranslate('JS_UNEXPECTED_ERROR'), text: response.result.text });
			}
		});
	}
};
