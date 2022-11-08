/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.Settings_Mail_Config_Js = class {
	/**
	 * Register events
	 */
	registerEvents() {
		this.container = $('.contentsDiv');
		App.Fields.Text.Editor.register(this.container.find('.js-editor'), {
			height: '20em'
		});
		App.Tools.VariablesPanel.registerRefreshCompanyVariables(this.container);
		App.Fields.MultiEmail.register(this.container);
		App.Fields.Text.registerCopyClipboard(this.container.find('.js-container-variable'));
		app.registerBlockToggleEvent(this.container);
	}
};
