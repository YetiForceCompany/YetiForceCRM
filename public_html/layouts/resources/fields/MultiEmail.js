/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

class MultiEmail {
	constructor(element) {
		const thisInstance = this;
		const inputElement = element;
		this.elements = {};
		this.elements.form = $(element).closest('form').eq(0);
		$(this.elements.form).on('submit', () => {
			thisInstance.onFormSubmit(inputElement);
		});
	}

	onFormSubmit(element) {
		let inputObj = $(element).find('input');
		inputObj.val(JSON.stringify(inputObj.val()));
		console.log('onFormSubmit');
	}
}