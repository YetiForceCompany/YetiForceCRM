/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

function ApiAddress() {

	this.registerSave = function() {
		var thisInstance = this;
		jQuery('.save').on('click', function(){
			var elements = {};
			jQuery('.api').each(function(){
				var name = jQuery(this).attr('name');
				if( jQuery(this).attr('type') == 'checkbox' )
					elements[name] = jQuery(this).prop('checked') ? 1 : 0;
				else
					elements[name] = jQuery(this).val();
			});

			// validate fields
			if(!thisInstance.registerValidate(elements)){
				return false;
			}
			// default use OpenCage Geocoder
			if(elements['key'])
				elements['nominatim'] = 1;
			elements = jQuery.extend({}, elements);
			var params = {}
			params.data = {module: 'ApiAddress', parent: 'Settings', action: 'SaveConfig', 'elements': elements}
			params.async = false;
			params.dataType = 'json';
			AppConnector.request(params).then(
				function(data) {
					var response = data['result'];
					if ( response['success'] ) {
						if(elements['key']){
							thisInstance.registerReload();
						}
						var parametry = {
							text:  response['message'],
							type: 'success'
							};
						Vtiger_Helper_Js.showPnotify(parametry);
					}
					else{
						var parametry = {
							text:  response['message'],
							type: 'error'
							};
						Vtiger_Helper_Js.showPnotify(parametry);
					}
				},
				function(data,err){
					var parametry = {
					text: app.vtranslate('JS_ERROR'),
					type: 'error'
					};
				Vtiger_Helper_Js.showPnotify(parametry);
				}
			);
		});
	},
	this.registerRemoveConnection = function() {
		var thisInstance = this;
		jQuery('.delete').on('click', function(){
			var elements = {'key':'0', 'nominatim': '0'};
			var params = {}
			params.data = {module: 'ApiAddress', parent: 'Settings', action: 'SaveConfig', 'elements': elements}
			params.async = false;
			params.dataType = 'json';
			AppConnector.request(params).then(
				function(data) {
					var response = data['result'];
					if ( response['success'] ) { 
						thisInstance.registerReload();
						var parametry = {
						text:  response['message'],
						type: 'success'
						};
						Vtiger_Helper_Js.showPnotify(parametry);
					}
					else{
						var parametry = {
							text:  response['message'],
							type: 'error'
							};
						Vtiger_Helper_Js.showPnotify(parametry);
					}
				},
				function(data,err){
					var parametry = {
					text: app.vtranslate('JS_ERROR'),
					type: 'error'
					};
				Vtiger_Helper_Js.showPnotify(parametry);
				}
			);
		});
	},
	this.registerReload = function(){
		var thisInstance = this;
		var progress = $.progressIndicator({
			'message' : app.vtranslate('JS_LOADING_PLEASE_WAIT'),
			'position' : '.contentsDiv',
			'blockInfo' : {
				'enabled' : true
			}
		});

		$.get("index.php?module=ApiAddress&parent=Settings&view=Configuration", function (data) {
			$('.contentsDiv').html(data);
			progress.progressIndicator({'mode': 'hide'});
			thisInstance.registerEvents();
		});
	},
	this.registerValidate = function(elements){
		var thisInstance = this;
		var status = true;
		for( i in elements ){
			if( i == 'min_lenght'){
				var filter = /\D/;
				if (filter.test(elements[i]) || elements[i] == 1 || elements[i] == 0 ){
					var par = {
						text: app.vtranslate('JS_WRONG_NUMBER'),
						type: 'error'
					};
					Vtiger_Helper_Js.showPnotify(par);
					status=false;
				}
			}
			if( i == 'key'){
				var test = "https://api.opencagedata.com/geocode/v1/json?query=test&pretty=1&key="+elements[i];
				$.ajax({
					url: test,
					async: false,
					complete : function(data) {
						if(data.status == 403){
							var parametry = {
								text:  app.vtranslate('Invalid API key'),
								type: 'error'
								};
							Vtiger_Helper_Js.showPnotify(parametry);
							status = false;
						} 
					}
				});
			}
		}
		return status;
	},
	
	this.registerEvents = function() {
        var thisInstance = this;
		thisInstance.registerSave();
		thisInstance.registerRemoveConnection();
	};
}


jQuery(document).ready(function() {
    var dc = new ApiAddress();
    dc.registerEvents();
})