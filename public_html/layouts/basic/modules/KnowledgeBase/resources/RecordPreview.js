/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

'use strict';

jQuery.Class(
	'YetiForce_RecordPreview_Js',
	{
		/**
		 * Register events
		 */
		registerEvents() {
			RecordPreview.mount({
				el: '#RecordPreview',
				options: {
					moduleName: 'KnowledgeBase',
					recordId: $('#recordId').val()
				}
			});
		},
		showRecordPreview(container) {
			container.find('#quasar-css').on('load', function() {
				container.removeClass('d-none');
			});
		}
	},
	{}
);
YetiForce_RecordPreview_Js.registerEvents();
