/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
// show/hide password
$('.show_pass').click( function(e) {
    var id = $(this).attr('id').substr(4); 
    showRelatedListPassword( id, '' );
    
    return false;
});

// related modules
function showRelatedListPassword( record ) {  
    var passVal = $('#'+record).html(); // current value of password
    // button labels
    var showPassText = app.vtranslate('LBL_ShowPassword');
    var hidePassText = app.vtranslate('LBL_HidePassword');
    
    // if password is hashed, show it
    if ( passVal == '**********' ) {
        var params = {
            'module' : "OSSPasswords",
            'action' : "GetPass",
            'record' : record
        }
        var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
        AppConnector.request(params).then(
            function(data) {
                var response = data['result'];
                if (response['success']) {
                    // show password
                    $('#'+record).html( response['password'] );
                    // change button title to 'Hide Password'
                    $('a#btn_'+record+' span').attr( 'title', hidePassText );
                    // change icon
                    $('a#btn_'+record+' span').removeClass( 'adminIcon-passwords-encryption' );
                    $('a#btn_'+record+' span').addClass( 'glyphicon-lock' );
                    // show copy to clipboard button
                    $('a#copybtn_'+record).removeClass('hide');
                }
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
            },
            function(data,err){
            
            }
        );
    }
    // if password is not hashed, hide it
    else { 
        // hide password
        $('#'+record).html( '**********' );
        // change button title to 'Show Password'
        $('a#btn_'+record+' span').attr( 'title', showPassText );
        // change icon
        $('a#btn_'+record+' span').removeClass( 'glyphicon-lock' );
        $('a#btn_'+record+' span').addClass( 'adminIcon-passwords-encryption' );
        // hide copy to clipboard button
        $('a#copybtn_'+record).addClass('hide');
    }
}

// code to copy password to clipboardData
var clip2 = new ZeroClipboard( 
    $('[id^=copybtn_]'), {
    moviePath: "libraries/jquery/ZeroClipboard/ZeroClipboard.swf"
});

clip2.on( 'complete', function(client, args) {
    // notification about copy to clipboard
    var params = {
        text: app.vtranslate('LBL_NotifPassCopied'),
        animation: 'show',
        title: app.vtranslate('LBL_NotifPassTitle'),
        type: 'success'
    };
    Vtiger_Helper_Js.showPnotify(params);
});
