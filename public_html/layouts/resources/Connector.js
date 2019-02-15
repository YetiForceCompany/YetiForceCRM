/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

window.AppConnector = {

	/**
	 * Sends a pjax request (push state +ajax)
	 * The function is deferred. it will be resolved on success and error on failure
	 *  Success - if request is success it will send you data that it recieved
	 *  error - it will send two parameters first gives string regarding error
	 *                   Second gives you error object if exists
	 *
	 *  @return - deferred promise
	 */
	requestPjax: function (params) {
		return AppConnector._request(params, true);
	},

	/**
	 *  Sends ajax request to the specified url.
	 *  The function is deferred. it will be resolved on success and error on failure
	 *  Success - if request is success it will send you data that it recieved
	 *  error - it will send two parameters first gives string regarding error
	 *                   Second gives you error object if exists
	 *
	 *  @return - deferred promise
	 */
	request: function (params, rawData) {
		return AppConnector._request(params, false, rawData);
	},

	_request: function (params, pjaxMode, rawData) {
		const aDeferred = jQuery.Deferred();
		if (typeof rawData === "undefined") {
			rawData = false;
		}
		if (typeof pjaxMode === "undefined") {
			pjaxMode = false;
		}
		if (typeof params === "undefined") {
			params = {};
		}
		let fullUrl = '',
			index,
			callerParams;
		//caller has send only data
		if (typeof params.data === "undefined" || rawData) {
			if (typeof params === 'string') {
				callerParams = fullUrl = params;
				index = callerParams.indexOf('?');
				if (index !== -1) {
					let subStr = callerParams.substr(0, index + 1);//need to replace only "index.php?" or "?"
					callerParams = callerParams.replace(subStr, '');
				}
			} else {
				callerParams = $.extend({}, params);
			}
			params = {};
			params.data = callerParams;
		}
		//Make the request as post by default
		if (typeof params.type === "undefined" || rawData)
			params.type = 'POST';
		if (typeof params.jsonp === "undefined" || rawData)
			params.jsonp = false;

		//By default we expect json from the server
		if (typeof params.dataType === "undefined" || rawData) {
			let data = params.data;
			//view will return html
			params.dataType = 'json';
			if (data.hasOwnProperty('view')) {
				params.dataType = 'html';
			} else if (typeof data === 'string' && data.indexOf('&view=') !== -1) {
				params.dataType = 'html';
			}
			if (typeof params.url !== "undefined" && params.url.indexOf('&view=') !== -1) {
				params.dataType = 'html';
			}
		}
		//If url contains params then seperate them and make them as data
		if (typeof params.url !== "undefined" && params.url.indexOf('?') !== -1) {
			fullUrl = params.url;
			let urlSplit = params.url.split('?'),
				queryString = urlSplit[1];
			params.url = urlSplit[0];
			let queryParameters = queryString.split('&');
			for (index = 0; index < queryParameters.length; index++) {
				let queryParam = queryParameters[index],
					queryParamComponents = queryParam.split('=');
				params.data[queryParamComponents[0]] = queryParamComponents[1];
			}
		}
		if (typeof params.url === "undefined" || params.url.length <= 0) {
			params.url = 'index.php';
		}
		params.success = function (data, status, jqXHR) {
			if (data !== null && typeof data === 'object' && data.error) {
				app.errorLog(data.error);
				if (data.error.message) {
					Vtiger_Helper_Js.showMessage({
						text: data.error.message,
						type: 'error'
					});
				}
			}
			aDeferred.resolve(data);
		};
		params.error = function (jqXHR, textStatus, errorThrown) {
			let action = jqXHR.getResponseHeader('yf-action');
			if (action === 'logout') {
				window.location.href = 'index.php';
			}
			if (CONFIG.debug) {
				if (jqXHR.status === 406) {
					let sep = "-".repeat(150);
					console.warn("%cYetiForce debug mode!!!", "color: red; font-family: sans-serif; font-size: 1.5em; font-weight: bolder; text-shadow: #000 1px 1px;");
					console.error('Error: ' + errorThrown, '\n' + sep + '\nTrace:\n' + sep + '\n' + (jqXHR.responseJSON ? jqXHR.responseJSON.error.trace : ''), '\n' + sep + '\nParams:\n' + sep + '\n' + JSON.stringify(params, null, '\t'));
				} else {
					app.errorLog(jqXHR, textStatus, errorThrown);
				}
			}
			aDeferred.reject(textStatus, errorThrown, jqXHR);
		};
		jQuery.ajax(params);
		if (pjaxMode) {
			if (typeof params.data.historyUrl !== 'undefined') {
				fullUrl = params.data.historyUrl;
			}
			if (fullUrl === '') {
				fullUrl = 'index.php?' + $.param(params.data);
			} else if (fullUrl.indexOf('index.php?') === -1) {
				fullUrl = 'index.php?' + fullUrl;
			}
			if (history.pushState && fullUrl !== '') {
				const currentHref = window.location.href;
				if (!history.state) {
					history.replaceState(currentHref, "title 1", currentHref);
				}
				history.pushState(fullUrl, "title 2", fullUrl);
			}
		}
		return aDeferred.promise();
	},

	requestForm: function (url, params) {
		var newEle = '<form action=' + url + ' method="POST">';
		if (typeof csrfMagicName !== "undefined") {
			newEle += '<input type="hidden" name="' + csrfMagicName + '"  value=\'' + csrfMagicToken + '\'>';
		}
		if (typeof params !== "undefined") {
			jQuery.each(params, function (index, value) {
				newEle += '<input type="hidden" name="' + index + '"  value=\'' + value + '\'>';
			});
		}
		newEle += '</form>';
		var form = new jQuery(newEle);
		form.appendTo('body').submit();
	},
}

