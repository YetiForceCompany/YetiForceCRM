/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

'use strict';

jQuery.Class(
	'YetiForce_ArticlePreview_Js',
	{
		/**
		 * Register events
		 */
		registerEvents() {
			ArticlePreviewVueComponent.mount({
				el: '#ArticlePreview',
				state: {
					moduleName: app.getModuleName(),
					recordId: $('#recordId').val()
				}
			});
		}
	},
	{}
);
YetiForce_ArticlePreview_Js.registerEvents();
