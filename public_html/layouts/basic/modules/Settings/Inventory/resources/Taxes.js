/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Inventory_Index_Js(
	'Settings_Inventory_Taxes_Js',
	{},
	{
		/*
		 * Function to add the Details in the list after saving
		 */
		addDetails: function (details) {
			let container = jQuery('#inventory'),
				currency = jQuery('#currency'),
				symbol = '%',
				table = $('.inventoryTable', container),
				defaultCheck = '';
			if (currency.length > 0) {
				currency = JSON.parse(currency.val());
				symbol = currency.currency_symbol;
			}
			if (details.default === 1) {
				table.find('.default').prop('checked', false);
				defaultCheck = 'checked';
			}
			let trElement = $(
				`<tr class="opacity" data-id="${details.id}">
				<td class="textAlignCenter ${details.row_type}"><label class="name">${details.name}</label></td>
				<td class="textAlignCenter ${details.row_type}"><span class="value">${details.value} ${symbol}</span></td>
				<td class="textAlignCenter ${details.row_type}"><input class="status js-update-field mt-2" checked type="checkbox" data-field-name="status"></td>
				<td class="textAlignCenter ${details.row_type}">
					<div class="float-right  w-50 d-flex justify-content-between mr-2">
						<input class="default js-update-field mt-2" ${defaultCheck} data-field-name="default" type="checkbox">
						<div class="actions">
							<button class="btn btn-info btn-sm text-white editInventory u-cursor-pointer" data-url="${details._editurl}">
								<span title="Edycja" class="yfi yfi-full-editing-view alignBottom"></span>
							</button>
							<button class="removeInventory u-cursor-pointer btn btn-danger btn-sm text-white" data-url="${details._editurl}">
								<span title="Usuń" class="fas fa-trash-alt alignBottom"></span>
							</button>
						</div>
					</div>
				</td>
			</tr>`
			);
			table.append(trElement);
		}
	}
);
