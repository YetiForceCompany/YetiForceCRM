/**
 * Settings BusinessHours List
 *
 * @description List scripts for businesshours module
 * @license YetiForce Public License 3.0
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_BusinessHours_List_Js',
	{
		deleteById(id) {
			Vtiger_Helper_Js.showConfirmationBox({ message: app.vtranslate('JS_BUSINESSHOURS_DELETE_CONFIRMATION') }).done(
				e => {
					const instance = Vtiger_List_Js.getInstance();
					const params = $.extend(instance.getDeleteParams(), {
						record: id
					});
					AppConnector.request(params).done(function(data) {
						if (data.success) {
							instance.getListViewRecords();
						}
					});
				}
			);
		}
	},
	{
		calculatePages() {
			return jQuery.Deferred().resolve();
		},
		massUpdatePagination() {}
	}
);
