/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Edit_Js(
	'SMSNotifier_Edit_Js',
	{},
	{
		/**
		 * Load variables panel
		 * @param {jQuery} form
		 */
		loadVariablePanel: function (form) {
			let panel = form.find('#variablePanel');
			let reletedField = form.find('[name="related_to"]');
			let sourceRecord = parseInt(reletedField.val());
			let sourceModule = reletedField.closest('.fieldValue ').find('input[name="popupReferenceModule"]').val();
			if (!sourceRecord) {
				panel.html('');
				return false;
			}

			panel.progressIndicator();
			AppConnector.request({
				module: 'SMSNotifier',
				record: app.getRecordId(),
				view: 'VariablePanel',
				type: 'sms',
				sourceRecord: sourceRecord,
				selectedModule: sourceModule
			})
				.done((response) => {
					panel.html(response);
					this.afterLoadVariablePanel(panel);
					App.Tools.VariablesPanel.registerRefreshCompanyVariables(panel);
				})
				.fail(() => {
					panel.progressIndicator({ mode: 'hide' });
				});
		},
		/**
		 * Events after load variables panel
		 * @param {jQuery} html
		 */
		afterLoadVariablePanel: function (html) {
			App.Fields.Picklist.showSelect2ElementView(html.find('select.select2'));
			App.Fields.Text.registerCopyClipboard(html);
		},
		/**
		 * Register variables panel events
		 * @param {jQuery} form
		 */
		registerVariablePanelEvent: function (form) {
			if (typeof form === 'undefined') {
				form = this.getForm();
			}
			form
				.find('[name="message"]')
				.closest('.js-block-content')
				.prepend('<div id="variablePanel" class="row px-0 borderBottom bc-gray-lighter mt-n1"></div>');
			this.loadVariablePanel(form);
			form.find('[name="target"]').on('change', () => {
				this.loadVariablePanel(form);
			});
		},
		/**
		 * Register phone change
		 */
		registerPhoneChange: function () {
			this.getForm()
				.find('.js-phone-change')
				.on('click', function (e) {
					let phoneField = $(e.currentTarget).closest('.fieldValue').find('input[name]');
					let value = e.currentTarget.dataset.value;
					if (phoneField.length && value) {
						phoneField.val(value).trigger('focusout');
					}
				});
		},
		/**
		 * Register basic events
		 * @param {jQuery} container
		 */
		registerBasicEvents: function (container) {
			this._super(container);
			this.registerVariablePanelEvent(container);
			App.Tools.VariablesPanel.registerRefreshCompanyVariables(container);
			App.Tools.VariablesPanel.refreshCompanyVariables(container);
			new App.Fields.Text.Completions(container.find('[name="message"]'), {
				completionsCollection: { emojis: true },
				autolink: false
			});
			this.registerPhoneChange();
		}
	}
);
