/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
Integrations_Pbx_Base.driver = 'GenesysWdeWhirly';
/**
 * @classdesc Genesys WDE by Whirly PBX integrations class.
 * @class
 */
window.Integrations_Pbx_GenesysWdeWhirly = class Integrations_Pbx_GenesysWdeWhirly extends Integrations_Pbx_Base {
	/** @inheritdoc */
	constructor(container) {
		super(container);
	}
	/** @inheritdoc */
	performCall(data) {
		this.log('|►| performCall', data);
		AppConnector.request({
			module: 'AppComponents',
			action: 'Pbx',
			mode: 'performCall',
			...data
		}).done((response) => {
			if (response.result.status) {
				app.showNotify({ title: response.result.text, type: 'info' });
				$.ajax({ url: response.result.url, headers: { 'Token-Api': response.result.token } })
					.done((ajax) => {
						this.log('|◄| performCall', ajax);
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
