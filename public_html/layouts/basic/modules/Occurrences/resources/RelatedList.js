/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
Vtiger_RelatedList_Js(
	'Occurrences_RelatedList_Js',
	{
		/*
		 * function to trigger send Email
		 * @params: send email url , module name.
		 */
		triggerSendEmail: function() {
			let params = Vtiger_RelatedList_Js.relatedListInstance.getDefaultParams();
			Vtiger_List_Js.triggerSendEmail(
				$.extend(params, {
					relatedLoad: true,
					sourceModule: app.getModuleName(),
					sourceRecord: app.getRecordId()
				})
			);
		}
	},
	{}
);
