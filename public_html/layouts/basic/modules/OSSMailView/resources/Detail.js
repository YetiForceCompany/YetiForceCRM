/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Detail_Js(
	'OSSMailView_Detail_Js',
	{
		printMail: function () {
			var subject = $('#subject').val();
			var from = $('#from_email').val();
			var to = $('#to_email').val();
			var cc = $('#cc_email').val();
			var date = jQuery('#createdtime').val();
			var body = $('#content').html();
			var content = window.open();
			content.document.write('<b>' + app.vtranslate('Subject') + ': ' + subject + '</b><br />');
			content.document.write('<br />' + app.vtranslate('From') + ': ' + from + '<br />');
			content.document.write(app.vtranslate('To') + ' :' + to + '<br />');
			if (cc != null) {
				content.document.write(app.vtranslate('CC') + ': ' + cc + '<br />');
			}
			content.document.write(app.vtranslate('Date') + ': ' + date + '<br />');
			content.document.write('<hr/>' + body + '<br />');
			content.print();
		}
	},
	{
		registerEvents: function () {
			this._super();
			Vtiger_Index_Js.registerMailButtons($('.detailViewContainer .js-btn-toolbar'));
		}
	}
);
