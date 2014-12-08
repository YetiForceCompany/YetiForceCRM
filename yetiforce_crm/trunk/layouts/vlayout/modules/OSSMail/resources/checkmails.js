/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

jQuery(function() {
    window.activeCall = false;
    if(getUrlVars()['view'] != 'Popup'){
        startCheckMails();
    }
});
function startCheckMails() {
	var params = {
		'module': 'OSSMailScanner',
		'action': "getConfig"
	};
	window.stop_scan_mails = false;
	AppConnector.request(params).then(
		function(response) {
			if (response.success) {
				var result = response.result;
				if(result.data.time_checking_mail != 0){
					checkMails();
					var refreshIntervalId = setInterval(function() {
						if(window.stop_scan_mails == false){
							checkMails();
						}else{
							clearInterval(refreshIntervalId);
						}
					}, result.data.time_checking_mail*1000);
				}
			}
		}
	);
}
function checkMails() {
	var params = {
		'module': 'OSSMail',
		'action': "checkMails"
	};
	AppConnector.request(params).then(
		function(response) {
			if (response.success && response.success.error != true && response.result.error != true) {
				var result = response.result;
				$('#OSSMailBoxInfo').html(result.html);
			}else{window.stop_scan_mails = true;}
		},
		function(data, err) {}
	);
}
function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
        vars[key] = value;
    });
    return vars;
}