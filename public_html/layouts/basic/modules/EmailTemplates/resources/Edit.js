/* {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Edit_Js(
	'EmailTemplates_Edit_Js',
	{},
	{
		loadVariablePanel: function (form) {
			var thisInstance = this;
			if (typeof form === 'undefined') {
				form = this.getForm();
			}
			var panel = form.find('#variablePanel');
			panel.progressIndicator();
			AppConnector.request({
				module: 'EmailTemplates',
				record: app.getRecordId(),
				view: 'VariablePanel',
				type: 'mail',
				selectedModule: form.find('[name="module_name"]').val()
			})
				.done(function (response) {
					panel.html(response);
					thisInstance.afterLoadVariablePanel(panel);
					App.Tools.VariablesPanel.registerRefreshCompanyVariables(panel);
				})
				.fail(function () {
					panel.progressIndicator({ mode: 'hide' });
				});
		},
		afterLoadVariablePanel: function (html) {
			App.Fields.Picklist.showSelect2ElementView(html.find('select.select2'));
			App.Fields.Text.registerCopyClipboard(html);
		},
		registerVariablePanelEvent: function (form) {
			var thisInstance = this;
			if (typeof form === 'undefined') {
				form = this.getForm();
			}
			form
				.find('.js-toggle-panel[data-label="LBL_CONTENT_MAIL"] .blockContent')
				.prepend('<div id="variablePanel" class="row px-0 borderBottom bc-gray-lighter mt-n1"></div>');
			thisInstance.loadVariablePanel(form);
			form.find('[name="module_name"]').on('change', function () {
				thisInstance.loadVariablePanel(form);
			});
		},
		registerBasicEvents: function (container) {
			this._super(container);
			this.registerVariablePanelEvent(container);
			App.Tools.VariablesPanel.registerRefreshCompanyVariables(container);
			App.Tools.VariablesPanel.refreshCompanyVariables(container);
		}
	}
);
