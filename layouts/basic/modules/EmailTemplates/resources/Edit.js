/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_Edit_Js("EmailTemplates_Edit_Js", {}, {

	loadVariablePanel: function (form) {
		var thisInstance = this;
		if (typeof form == 'undefined') {
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
		}).then(function (response) {
			panel.html(response);
			thisInstance.afterLoadVariablePanel(panel);
		}, function (data, err) {
			panel.progressIndicator({mode: 'hide'});
		})
	},
	afterLoadVariablePanel: function (html) {
		app.showSelect2ElementView(html.find('select.select2'));
	},
	registerVariablePanelEvent: function (form) {
		var thisInstance = this;
		if (typeof form == 'undefined') {
			form = this.getForm();
		}
		form.find('.blockContainer[data-label="LBL_CONTENT_MAIL"] .blockContent').prepend('<div id="variablePanel" class="col-md-12 paddingLRZero borderBottom bc-gray-lighter"></div>');
		thisInstance.loadVariablePanel(form);
		form.find('[name="module_name"]').on('change', function (e) {
			thisInstance.loadVariablePanel(form);
		});
	},
	registerBasicEvents: function (container) {
		this._super(container);
		this.registerVariablePanelEvent(container);
		app.registerCopyClipboard();
	}
})
