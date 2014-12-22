/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

var AppConnector = {

	/**
	 * Sends a pjax request (push state +ajax)
	 * The function is deferred. it will be resolved on success and error on failure
	 *  Success - if request is success it will send you data that it recieved
	 *  error - it will send two parameters first gives string regarding error
	 *                   Second gives you error object if exists
	 *
	 *  @return - deferred promise
	 */
	requestPjax : function(params) {
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
	request : function(params) {
		return AppConnector._request(params, false);
	},

	
	_request : function(params, pjaxMode) {
		var aDeferred = jQuery.Deferred();

		if(typeof pjaxMode == 'undefined') {
			pjaxMode = false;
		}

		if(typeof params == 'undefined') params = {};
	
		//caller has send only data
		if(typeof params.data == 'undefined') {
			if(typeof params == 'string') {
				var callerParams = params;
				if(callerParams.indexOf('?')!== -1) {
					var callerParamsParts = callerParams.split('?')
					callerParams = callerParamsParts[1];
				}
			}else{
				callerParams = jQuery.extend({}, params);
			}
			params = {};
			params.data = callerParams;
		}
		//Make the request as post by default
		if(typeof params.type == 'undefined') params.type = 'POST';

		//By default we expect json from the server
		if(typeof params.dataType == 'undefined'){
			var data = params.data;
			//view will return html
			params.dataType='json';
			if(data.hasOwnProperty('view')){
				params.dataType='html';
			}
			else if (typeof data == 'string' && data.indexOf('&view=') !== -1) {
				params.dataType='html';
			}
			
			if(typeof params.url != 'undefined' && params.url.indexOf('&view=')!== -1) {
				params.dataType='html';
			}
		}

		//If url contains params then seperate them and make them as data
		if(typeof params.url != 'undefined' && params.url.indexOf('?')!== -1) {
			var urlSplit = params.url.split('?');
			var queryString = urlSplit[1];
			params.url = urlSplit[0];
			var queryParameters = queryString.split('&');
			for(var index=0; index<queryParameters.length; index++) {
				var queryParam = queryParameters[index];
				var queryParamComponents = queryParam.split('=');
				params.data[queryParamComponents[0]] = queryParamComponents[1];
			}
		}

		if(typeof params.url == 'undefined' ||  params.url.length <= 0){
			 params.url = 'index.php';
		}


		var success = function(data,status,jqXHR) {
			aDeferred.resolve(data);
		}

		var error = function(jqXHR, textStatus, errorThrown){
			aDeferred.reject(textStatus, errorThrown);
		}
		
		if(pjaxMode) {
			if(typeof params.container == 'undefined') params.container = '#pjaxContainer';

			params.type = 'GET';

			var pjaxContainer = jQuery('#pjaxContainer');
			//Clear contents existing before
			if(params.container == '#pjaxContainer') {
				pjaxContainer.html('');
			}

			jQuery(document).on('pjax:success', function(event, data,status,jqXHR){
				pjaxContainer.html('');
				success(data,status,jqXHR);
			})
			
			jQuery(document).on('pjax:error', function(event, jqXHR, textStatus, errorThrown){
				pjaxContainer.html('');
				error(jqXHR, textStatus, errorThrown);
			})
			jQuery.pjax(params);

		}else{
			params.success = success;

			params.error = error;
			jQuery.ajax(params);
		}

		return aDeferred.promise();
	}

}

