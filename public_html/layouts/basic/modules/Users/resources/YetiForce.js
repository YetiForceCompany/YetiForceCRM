/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Users_YetiForce_JS',
	{},
	{
		/**
		 * Register events
		 * @param {jQuery} modal Modal container
		 */
		registerEvents: function (modal) {
			let counter = 7000,
				time = counter;
			let progress = modal.find('.js-progress-bar');
			let counterBack = setInterval(() => {
				counter = counter - 100;
				progress.css('width', (counter / time) * 100 + '%');
				if (counter <= 0) {
					clearInterval(counterBack);
					modal.modal('hide');
				}
			}, 100);
		}
	}
);
