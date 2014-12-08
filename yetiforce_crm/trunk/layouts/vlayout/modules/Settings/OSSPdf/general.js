/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery(document).ready(function ($) {    
    // modal is greyed out if z-index is low
    $("#myModal").css("z-index", "999999999");
    
    // Hide modal if "Okay" is pressed
    $('#myModal .okay-button').click(function() {
        var disabled = $('#confirm').attr('disabled');
        if(typeof disabled == 'undefined') {
            $('#myModal').modal('hide');
            $('#uninstall #EditView').submit();
        }
    });
  ///////////////
  
  jQuery('.linkes').click(function(){
	var modeVal = jQuery(this).data('mode'); 
	var formoduleVal = jQuery(this).data('formodule'); 
  
  
var params = {}
            params.data = {module: 'OSSPdf', action: 'ButtonsSettings', mode: modeVal, formodule: formoduleVal}
            params.async = false;
            params.dataType = 'json';
            AppConnector.request(params).then(
                function(data) {
                    var response = data['result'];
                    if ( response['success'] ) {
						var params = {};
		params['module'] = 'OSSPdf';
		params['view'] = 'Index';
		params['parent'] = 'Settings';
		AppConnector.request(params).then(
			function(data) {
			//var d=new Date();
			jQuery('.contentsDiv').html(data);
			general();
			}
		);
                    }
                    else {
                        var params = {
                            text: app.vtranslate('message'),
                            animation: 'show',
                            type: 'error',
                            sticker: false,
                            hover_sticker: false,
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    }
                },
                function(data,err){
                
                }
            );
			
		return false;
	});
    // enable/disable confirm button
    $('#status').change(function() {
        $('#confirm').attr('disabled', !this.checked);
    });
	
		
});
	function general(){
		jQuery('.linkes').click(function(){
	var modeVal = jQuery(this).data('mode'); 
	var formoduleVal = jQuery(this).data('formodule'); 
  
  
var params = {}
            params.data = {module: 'OSSPdf', action: 'ButtonsSettings', mode: modeVal, formodule: formoduleVal}
            params.async = false;
            params.dataType = 'json';
            AppConnector.request(params).then(
                function(data) {
                    var response = data['result'];
                    if ( response['success'] ) {
						var params = {};
		params['module'] = 'OSSPdf';
		params['view'] = 'Index';
		params['parent'] = 'Settings';
		AppConnector.request(params).then(
			function(data) {
			//var d=new Date();
			jQuery('.contentsDiv').html(data);
			general();
			}
		);
                    }
                    else {
                        var params = {
                            text: app.vtranslate('message'),
                            animation: 'show',
                            type: 'error',
                            sticker: false,
                            hover_sticker: false,
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    }
                },
                function(data,err){
                
                }
            );
		return false;
	});
	}

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