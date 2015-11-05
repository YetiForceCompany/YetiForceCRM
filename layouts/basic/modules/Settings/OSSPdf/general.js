/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class('Settings_OSSPdf_Index_Js', {}, {

	registerEvents : function() {
		this.registerLinkes();
	},

	registerLinkes : function() {
		var thisInstance = this;
		$('.linkes').click(function () {
			var modeVal = $(this).data('mode');
			var formoduleVal = $(this).data('formodule');
			var params = {};
			params.data = {module: 'OSSPdf', action: 'ButtonsSettings', mode: modeVal, formodule: formoduleVal}
			params.async = false;
			params.dataType = 'json';
			AppConnector.request(params).then(function (data) {
				var response = data['result'];
				if (response['success']) {
					var params = {};
					params['module'] = 'OSSPdf';
					params['view'] = 'Index';
					params['parent'] = 'Settings';
					AppConnector.request(params).then(function (data) {
							$('.contentsDiv').html(data);
							thisInstance.registerLinkes();
							app.changeSelectElementView($('.contentsDiv'));
						}
					);
				} else {
					var params = {
						text: app.vtranslate('message'),
						animation: 'show',
						type: 'error',
						sticker: false,
						hover_sticker: false,
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
			});
			return false;
		});
	},

});

function chooseSubTab(id) {
	var counter = document.getElementById('number_of_functions').value;

	for (i = 0; i < counter; i++)
	{
		document.getElementById('subtab' + i).setAttribute("class", "dvtUnSelectedCell");
		document.getElementById('subtab_fields' + i).style.display = "none";
	}
	document.getElementById('subtab' + id).setAttribute("class", "dvtSelectedCell");
	document.getElementById('subtab_fields' + id).style.display = "";
}

function pointat() {
	t = document.getElementById("acceptbutton");
	t.style.cursor = "pointer";
}

function pointout() {
	t = document.getElementById("acceptbutton");
	t.style.cursor = "default";
}
