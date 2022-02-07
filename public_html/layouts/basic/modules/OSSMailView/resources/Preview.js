/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'OSSMailView_Preview_Js',
	{
		printMail: function () {
			var content = window.open();
			$('.emailPreview > div').each(function (index) {
				if ($(this).hasClass('content')) {
					let html = $(this).find('iframe').attr('srcdoc');
					content.document.write(html + '<br />');
				} else {
					content.document.write($.trim($(this).text()) + '<br />');
				}
			});
			content.print();
		}
	},
	{
		registerEvents: function () {
			let container = jQuery('div.mainBody');
			Vtiger_Index_Js.registerMailButtons(container);
		}
	}
);
