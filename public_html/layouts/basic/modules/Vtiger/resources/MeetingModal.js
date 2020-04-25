/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
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
		},
	}
);
