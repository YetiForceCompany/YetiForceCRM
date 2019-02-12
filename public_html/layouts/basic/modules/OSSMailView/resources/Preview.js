/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("OSSMailView_Preview_Js", {
	printMail: function () {
		var content = window.open();
		$(".emailPreview > div").each(function (index) {
			if ($(this).hasClass('content')) {
				var inframe = $("#emailPreview_Content").contents();
				content.document.write(inframe.find('body').html() + "<br />");
			} else {
				content.document.write($.trim($(this).text()) + "<br />");
			}
		});
		content.print();
	},
}, {
	registerEvents: function () {
		var container = jQuery('div.mainBody');
		Vtiger_Index_Js.registerMailButtons(container);
	}
});
