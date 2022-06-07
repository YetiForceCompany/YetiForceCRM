/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Vtiger_Detail_Js(
	'Documents_Detail_Js',
	{
		//It stores the CheckFileIntegrity response data
		checkFileIntegrityResponseCache: {},

		/*
		 * function to trigger CheckFileIntegrity action
		 * @param: CheckFileIntegrity url.
		 */
		checkFileIntegrity: function (checkFileIntegrityUrl) {
			Documents_Detail_Js.getFileIntegrityResponse(checkFileIntegrityUrl).done(function (data) {
				Documents_Detail_Js.displayCheckFileIntegrityResponse(data);
			});
		},

		/**
		 * function to get the CheckFileIntegrity response data
		 * @returns {Promise}
		 */
		getFileIntegrityResponse: function (params) {
			var aDeferred = jQuery.Deferred();

			//Check in the cache
			if (!jQuery.isEmptyObject(Documents_Detail_Js.checkFileIntegrityResponseCache)) {
				aDeferred.resolve(Documents_Detail_Js.checkFileIntegrityResponseCache);
			} else {
				AppConnector.request(params).done(function (data) {
					//store it in the cache, so that we dont do multiple request
					Documents_Detail_Js.checkFileIntegrityResponseCache = data;
					aDeferred.resolve(Documents_Detail_Js.checkFileIntegrityResponseCache);
				});
			}
			return aDeferred.promise();
		},

		/*
		 * function to display the CheckFileIntegrity message
		 */
		displayCheckFileIntegrityResponse: function (data) {
			var result = data['result'];
			var success = result['success'];
			var message = result['message'];
			var params = {};
			if (success) {
				params = {
					text: message,
					type: 'success'
				};
			} else {
				params = {
					text: message,
					type: 'error'
				};
			}
			Documents_Detail_Js.showNotify(params);
			window.location.href = result['url'];
		},

		//This will show the messages of CheckFileIntegrity using pnotify
		showNotify: function (customParams) {
			var params = {
				title: app.vtranslate('JS_CHECK_FILE_INTEGRITY'),
				text: customParams.text,
				type: customParams.type,
				delay: '2000'
			};
			app.showNotify(params);
		}
	},
	{}
);
