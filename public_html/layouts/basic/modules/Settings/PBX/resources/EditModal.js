/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_EditModal_Js(
	'Settings_PBX_EditModal_Js',
	{},
	{
		registerEvents: function () {
			this._super();
			var container = this.getForm();
			container.find('[name="type"]').on('change', function (e) {
				if (this.value) {
					AppConnector.request({
						module: app.getModuleName(),
						parent: 'Settings',
						view: 'EditModal',
						type: this.value,
						connectorConfig: true
					}).done(function (html) {
						container.find('.editModalContent').html($(html).find('.editModalContent').html());
					});
				} else {
					container.find('.editModalContent').html('');
				}
			});
		}
	}
);
