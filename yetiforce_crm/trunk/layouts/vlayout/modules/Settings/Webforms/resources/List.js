/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Settings_Vtiger_List_Js("Settings_Webforms_List_Js",{
	
	/**
	 * Function to hadle showform
	 * @params: show form url
	 */
	showForm : function(event,showFormUrl){
		event.stopPropagation();
		AppConnector.request(showFormUrl).then(
			function(data){
				var callback = function(container){
					var showFormContents = container.find('pre').html();
					showFormContents = showFormContents + '<script  type="text/javascript">'+
					'window.onload = function() { '+
					'var N=navigator.appName, ua=navigator.userAgent, tem;'+
					'var M=ua.match(/(opera|chrome|safari|firefox|msie)\\/?\\s*(\\.?\\d+(\\.\\d+)*)/i);'+
					'if(M && (tem= ua.match(/version\\/([\\.\\d]+)/i))!= null) M[2]= tem[1];'+
					 'M=M? [M[1], M[2]]: [N, navigator.appVersion, "-?"];'+
					'var browserName = M[0];'+

						'var form = document.forms[0], '+
						'inputs = form.elements; '+
						'form.onsubmit = function() { '+
							'var required = [], att, val; '+
							'for (var i = 0; i < inputs.length; i++) { '+
								'att = inputs[i].getAttribute("required"); '+
								'val = inputs[i].value; '+
								'type = inputs[i].type; '+
								'if(type == "email") {'+
									'if(val != "") {'+
										'var elemLabel = inputs[i].getAttribute("label");'+
										'var emailFilter = /^[_/a-zA-Z0-9]+([!"#$%&()*+,./:;<=>?\\^_`{|}~-]?[a-zA-Z0-9/_/-])*@[a-zA-Z0-9]+([\\_\\-\\.]?[a-zA-Z0-9]+)*\\.([\\-\\_]?[a-zA-Z0-9])+(\\.?[a-zA-Z0-9]+)?$/;'+
										'var illegalChars= /[\\(\\)\\<\\>\\,\\;\\:\\\"\\[\\]]/ ;'+
										'if (!emailFilter.test(val)) {'+
											'alert("For "+ elemLabel +" field please enter valid email address"); return false;'+
										'} else if (val.match(illegalChars)) {'+
											'alert(elemLabel +" field contains illegal characters");return false;'+
										'}'+
									'}'+
								'}'+
								'if (att != null) { '+
										'if (val.replace(/^\\s+|\\s+$/g, "") == "") { '+
												'required.push(inputs[i].getAttribute("label")); '+
										'} '+
								'} '+
							'} '+
							'if (required.length > 0) { '+
								'alert("The following fields are required: " + required.join()); '+
								'return false; '+
							'} '+
							'var numberTypeInputs = document.querySelectorAll("input[type=number]");'+
							'for (var i = 0; i < numberTypeInputs.length; i++) { '+
                                'val = numberTypeInputs[i].value;'+
                                'var elemLabel = numberTypeInputs[i].getAttribute("label");'+
                                'if(val != "") {'+
									'var intRegex = /^[+-]?\\d+$/;'+ 
									'if (!intRegex.test(val)) {'+
										'alert("For "+ elemLabel +" field please enter valid number"); return false;'+
									'}'+
                                '}'+
							'}';
                    if(container.find('[name=isCaptchaEnabled]').val() == true) {
                        captchaPublicKey = container.find('[name=VTIGER_RECAPTCHA_PUBLIC_KEY]').val();
                        showFormContents = Settings_Webforms_List_Js.getCaptchaCode(showFormContents,captchaPublicKey);
                    } else {
                        showFormContents = showFormContents +
						'}; '+
                        '}'+
					'</script>';
                    }
					container.find('#showFormContent').text(showFormContents);
					container.find('pre').remove();
					container.find('code').remove();
					app.showScrollBar(container.find('#showFormContent'), {'height':'350px'});
				}
				app.showModalWindow(data,callback);
			},
			function(error){
			}
		)
	},
    
    /**
     * Function get Captcha Code
     * @param <string> showFormContents
     * @return <string> showFormContents
     */
    getCaptchaCode : function(showFormContents,captchaPublicKey) {
        var captchaContents = '<script type="text/javascript">'+
        'var RecaptchaOptions = { theme : "clean" };' +
        '</script>'+
        '<script type="text/javascript"'+
        'src="http://www.google.com/recaptcha/api/challenge?k='+captchaPublicKey+'">'+
        '</script>'+
        '<noscript>'+
            '<iframe src="http://www.google.com/recaptcha/api/noscript?k='+captchaPublicKey+'">'+
                'height="300" width="500" frameborder="0"></iframe><br>'+
            '<textarea name="recaptcha_challenge_field" rows="3" cols="40">'+
            '</textarea>'+
            '<input type="hidden" name="recaptcha_response_field" value="manual_challenge">'+
        '</noscript>';
        showFormContents = showFormContents.replace('<div id="captchaField"></div>',captchaContents);
        showFormContents = showFormContents +
                'var recaptchaValidationValue = document.getElementById("recaptcha_validation_value").value;'+
                'if (recaptchaValidationValue!= true){'+
                    'var recaptchaResponseElement = document.getElementsByName("recaptcha_response_field")[0].value;'+
                    'var recaptchaChallengeElement = document.getElementsByName("recaptcha_challenge_field")[0].value;'+
                    'var captchaUrl = document.getElementById("captchaUrl").value;'+
                    'var url = captchaUrl+"?recaptcha_response_field="+recaptchaResponseElement;'+
                    'url = url + "&recaptcha_challenge_field="+recaptchaChallengeElement+"&callback=JSONPCallback";'+
                    'jsonp.fetch(url);'+
                    'return false;'+
                '}'+
            '}; '+
        '};'+
        'var jsonp = {' +
            'callbackCounter: 0,'+

            'fetch: function(url) {'+
                'url = url +"&callId="+this.callbackCounter;'+
                'var scriptTag = document.createElement("SCRIPT");'+
                'scriptTag.src = url;'+
                'scriptTag.async = true;'+
                'scriptTag.id = "JSONPCallback_"+this.callbackCounter;'+
                'scriptTag.type = "text/javascript";'+
                'document.getElementsByTagName("HEAD")[0].appendChild(scriptTag);'+
                'this.callbackCounter++;'+
            '}'+
        '};'+
        'function JSONPCallback(data) {'+
            'if(data.result.success == true) {'+
                'document.getElementById("recaptcha_validation_value").value = true;'+
                'var form = document.forms[0];'+
                'form.submit();'+
            '} else {'+
                'document.getElementById("recaptcha_reload").click();'+
                'alert("you entered wrong captcha");'+
            '}'+
            'var element = document.getElementById("JSONPCallback_"+data.result.callId);'+
            'element.parentNode.removeChild(element);'+
        '}'+
        '</script>';
  
        return showFormContents;
    }
},{
	
	/*
	 * function to trigger delete record action
	 * @params: delete record url.
	 */
    DeleteRecord : function(deleteRecordActionUrl) {
		var thisInstance = this;
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(function(data) {
				AppConnector.request(deleteRecordActionUrl+'&ajaxDelete=true').then(
				function(data){
					if(data.success == true){
						var params = {
						text: app.vtranslate('JS_WEBFORM_DELETED_SUCCESSFULLY')
					};
					Settings_Vtiger_Index_Js.showMessage(params);
					jQuery('#recordsCount').val('');
					jQuery('#totalPageCount').text('');
					thisInstance.getListViewRecords().then(function(){
						thisInstance.updatePagination();
					});
					}else{
						Vtiger_Helper_Js.showPnotify(data.error.message);
					}
				});
			},
			function(error, err){
			}
		);
	},
	
	/*
	 * function to load the contents from the url through pjax
	 */
	loadContents : function(url) {
		var aDeferred = jQuery.Deferred();
		AppConnector.requestPjax(url).then(
			function(data){
				jQuery('.contentsDiv').html(data);
				aDeferred.resolve(data);
			},
			function(error, err){
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	
	/**
	 * Function to register events
	 */
	registerEvents : function(){
		this._super();
	}
})