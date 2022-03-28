/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 *************************************************************************************/
'use strict';

Vtiger_Edit_Js(
	'Products_Edit_Js',
	{},
	{
		registerEventForUsageunit: function () {
			this.checkUsageUnit();
			$('select[name="usageunit"]').on('change', this.checkUsageUnit);
		},
		checkUsageUnit: function () {
			var selectUsageunit = $('select[name="usageunit"]');
			var inputQtyPerUnit = $('input[name="qty_per_unit"]');
			var value = selectUsageunit.val();
			if (value === 'pack') {
				inputQtyPerUnit.prop('disabled', false);
			} else {
				inputQtyPerUnit.prop('disabled', true);
			}
		},
		registerEvents: function () {
			this._super();
			this.registerEventForUsageunit();
		}
	}
);
