/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'SMSNotifier_MassSMS_Js',
	{},
	{
		/** Modal container */
		container: false,
		/** Message field */
		messageField: false,

		/**
		 * Register save
		 */
		registerSave: function () {
			this.container.find('.js-modal__save').on('click', (e) => {
				e.preventDefault();
				let form = this.container.find('form');
				if (form.validationEngine('validate')) {
					let progress = $.progressIndicator({
						message: app.vtranslate('JS_SAVE_LOADER_INFO'),
						blockInfo: { enabled: true }
					});
					AppConnector.request(form.serializeFormData())
						.done((response) => {
							app.showNotify({
								textTrusted: false,
								text: response.result.message + ` (${response.result.count})`,
								type: 'info'
							});
							app.hideModalWindow();
						})
						.fail(() => {
							app.showNotify({
								title: app.vtranslate('JS_ERROR'),
								type: 'error'
							});
						})
						.always(() => {
							progress.progressIndicator({ mode: 'hide' });
						});
				}
			});
		},
		/**
		 * Register templates
		 */
		registerTemplate: function () {
			let templateField = this.container.find('select#template');
			templateField.on('change', (e) => {
				if (e.target.value) {
					this.messageField.val(e.target.value);
				}
			});
		},

		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = modalContainer;
			this.messageField = this.container.find('[name="message"]');
			this.registerTemplate();
			this.registerSave();
			new App.Fields.Text.Completions(this.messageField, {
				completionsCollection: { emojis: true },
				autolink: false
			});
			App.Fields.MultiImage.register(this.container);
		}
	}
);
