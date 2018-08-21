/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

class MultiEmail {
	/**
	 * Create class instance
	 *
	 * @param {HTMLElement|jQuery}
	 */
	constructor(element) {
		const thisInstance = this;
		const inputElement = element;
		this.elements = {};
		this.elements.form = $(element).closest('form').eq(0);
		$(this.elements.form).on(Vtiger_Edit_Js.recordPreSave, (e) => {
			thisInstance.onFormSubmit(inputElement);
		});
	}

	/**
	 * Convert data to json
	 * @param element
	 */
	onFormSubmit(element) {
		let inputObj = $(element).find('input');
		if (inputObj.val().length === 0) {
			$(element).find('input[type=hidden]').val('');
			return;
		}
		let arrTmp = inputObj.val().split(',');
		let arr = [];
		let arrayLength = arrTmp.length;
		for (var i = 0; i < arrayLength; i++) {
			arr.push({e: arrTmp[i]});
		}
		$(element).find('input[type=hidden]').val(JSON.stringify(arr));
	}
}
