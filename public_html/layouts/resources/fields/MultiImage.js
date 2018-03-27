/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

class MultiImage {
	/**
	 * Register multi image upload
	 *
	 * @param {jQuery.Class} thisInstance - instance of class
	 */
	register(container) {
		$(document).bind('drop dragover', function (e) {
			// prevent default browser drop behaviour
			e.preventDefault();
		});
		const fileUploads = $('.c-multi-image .c-multi-image__file', container).toArray();
		fileUploads.forEach((fileUploadInput) => {
			new FileUpload(fileUploadInput);
		});
	}

}