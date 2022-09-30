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
		}).done(function (response) {
			if (response.result.status) {
				app.showNotify({ title: response.result.text, type: 'info' });
				$.ajax({ url: response.result.url }).fail(function (_jqXHR, textStatus) {
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
