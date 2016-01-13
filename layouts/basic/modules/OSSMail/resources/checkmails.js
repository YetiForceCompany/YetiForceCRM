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
	if($('#OSSMailBoxInfo').data('numberunreademails') != undefined){
		window.stopScanMails = false;
		if(getUrlVars()['view'] != 'Popup'){
			startCheckMails();
		}
	}
	if($('#OSSMailBoxInfo .dropdown-menu').length > 0){
		registerUserList();
	}
});
function registerUserList() {
	$('#OSSMailBoxInfo .dropdown-menu li').click(function() {
		var params = {
			'module': 'OSSMail',
			'action': "SetUser",
			'user': $(this).data('id'),
		};
		AppConnector.request(params).then(
			function(response) {
				if( app.getModuleName() == 'OSSMail'){
					location.reload();
				}else{
					window.location.href = "index.php?module=OSSMail&view=index";
				}
			}
		);
	});
}
function startCheckMails() {
	var users = [];
	var timeCheckingMails = $('#OSSMailBoxInfo').data('interval')
	$( "#OSSMailBoxInfo .dropdown-menu li" ).each(function( index ) {
		users.push($( this ).data('id'));
	});
	if(users.length > 0){
		checkMails(users);
		var refreshIntervalId = setInterval(function() {
			if(window.stopScanMails == false){
				checkMails(users);
			}else{
				clearInterval(refreshIntervalId);
			}
		}, timeCheckingMails*1000);
	}
}
function checkMails(users) {
	var params = {
		'module': 'OSSMail',
		'action': "checkMails",
		'users': users,
	};
	AppConnector.request(params).then(
		function (response) {
			if (response.success && response.success.error != true && response.result.error != true) {
				var result = response.result;
				$("#OSSMailBoxInfo .dropdown-menu li").each(function (index) {
					var id = $(this).data('id');
					if (jQuery.inArray(id, result)) {
						var num = result[id];
						var text = '';
						if(num > 0 ){
							text = '('+num+')';
						}
						$(this).find('.noMails').text(text);
						$("#OSSMailBoxInfo .mainMail").find('.noMails_'+id).text(text);
					}
				});
			} else {
				window.stopScanMails = true;
			}
		},
		function (data, err) {
			window.stopScanMails = true;
		}
	);
}
function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
        vars[key] = value;
    });
    return vars;
}
