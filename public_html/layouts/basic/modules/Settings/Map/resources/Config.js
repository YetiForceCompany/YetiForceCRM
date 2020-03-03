/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Map_Config_Js',
	{},
	{
		registerTileLayer: function() {
			let tab = $('#TileLayer');
			tab.find('input').on('change', function() {
				AppConnector.request({
					module: 'Map',
					parent: 'Settings',
					action: 'Config',
					mode: 'setTileLayer',
					vale: this.value
				})
					.done(function(data) {
						Vtiger_Helper_Js.showPnotify({
							text: data['result']['message'],
							type: 'success'
						});
					})
					.fail(function() {
						Vtiger_Helper_Js.showPnotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					});
			});
		},
		registerEvents: function() {
			this.registerTileLayer();
		}
	}
);
