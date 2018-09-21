/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class("Base_RecordState_JS", {}, {
	/**
	 * Register modal events
	 * @param {jQuery} modalContainer
	 */
	registerEvents(modalContainer) {
		modalContainer.find('form').on('submit', function (e) {
			e.preventDefault();
			const progressIndicator = $.progressIndicator({position: 'html', blockInfo: {enabled: true}});
			AppConnector.request($(this).serializeFormData()).done((response) => {
				window.location.href = response.result;
			})
		});
	}
});