/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Detail_Js("Products_Detail_Js", {}, {
	/**
	 * Function to register event for image graphics
	 */
	registerEventForImageGraphics: function () {
		var imageContainer = jQuery('#imageContainer');
		imageContainer.cycle({
			fx: 'curtainX',
			sync: false,
			speed: 1000,
			timeout: 20
		});
		imageContainer.find('img').on('mouseenter', function () {
			imageContainer.cycle('pause');
		}).on('mouseout', function () {
			imageContainer.cycle('resume');
		});
	},
	registerEvents: function () {
		this._super();
		this.registerEventForImageGraphics();
	}
});
