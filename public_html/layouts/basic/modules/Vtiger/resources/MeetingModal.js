/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_MeetingModal_JS',
	{},
	{
		/**
		 * Modal container
		 */
		container: false,

		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = modalContainer;
			App.Fields.Text.registerCopyClipboard(this.container, '.js-clipboard');
			Vtiger_Index_Js.registerMailButtons(this.container);
			this.container.on('click', '.js-template-copy', (e) => {
				let frameContainer = $(e.currentTarget.dataset.clipboardTarget).get(0);
				frameContainer.contentDocument.designMode = 'on';
				frameContainer.contentDocument.execCommand('selectAll', false, null);
				frameContainer.contentDocument.execCommand('copy', false, null);
				frameContainer.contentDocument.designMode = 'off';
				app.showNotify({
					text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
					type: 'success'
				});
			});
		}
	}
);
