/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
// Function to generate new password
function passwordStrength(password, translations)
{ 
    if ( password == '' )
        password = document.getElementById( 'OSSPasswords_editView_fieldName_password' ).value;
        
    var desc = new Array();
    if ( translations == '' ) {
        desc[0] = app.vtranslate('Very Weak');    
        desc[1] = app.vtranslate('Weak');
        desc[2] = app.vtranslate('Better');
        desc[3] = app.vtranslate('Medium');
        desc[4] = app.vtranslate('Strong');
        desc[5] = app.vtranslate('Very Strong');
    }
    else {
        var tstring = translations.split( ',' );
        desc[0] = tstring[0];    
        desc[1] = tstring[1];    
        desc[2] = tstring[2];    
        desc[3] = tstring[3];    
        desc[4] = tstring[4];    
        desc[5] = tstring[5];    
    }

    var score   = 0;

    //if password bigger than 6 give 1 point
    if (password.length > 6) score++;

    //if password has both lower and uppercase characters give 1 point
    if ( ( password.match(/[a-z]/) ) && ( password.match(/[A-Z]/) ) ) score++;

    //if password has at least one number give 1 point
    if (password.match(/\d+/)) score++;

    //if password has at least one special caracther give 1 point
    if ( password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) ) score++;

    //if password bigger than 12 give another 1 point
    if (password.length > 12) score++;
    
    // password hidden
    if ( password == '' ) {
        document.getElementById("passwordDescription").innerHTML = app.vtranslate('Enter the password');
        document.getElementById("passwordStrength").className = "strength0";
    }
    else if ( password == '**********' ) {
        document.getElementById("passwordDescription").innerHTML = app.vtranslate('Password is hidden');
        document.getElementById("passwordStrength").className = "strength0";
    }
    else {
        document.getElementById("passwordDescription").innerHTML = desc[score];
        document.getElementById("passwordStrength").className = "strength" + score;
    }
}

function showPassword( record ) {
    var passVal = document.getElementById( "OSSPasswords_editView_fieldName_password" ).value;
    var showPassText = app.vtranslate('LBL_ShowPassword');
    var hidePassText = app.vtranslate('LBL_HidePassword');
    
    if ( $('#show-btn').text() == showPassText ) {
        var params = {
            'module' : "OSSPasswords",
            'action' : "GetPass",
            'record' : record
        }
        
        AppConnector.request(params).then(
            function(data) {
                var response = data['result'];
                if (response['success']) {
                    var el = document.getElementById( "OSSPasswords_editView_fieldName_password" );
                    el.value = response['password'];
                    el.onchange();
					$('#copy-button').removeClass('hide').show();
                }
            },
            function(data,err){
            
            }
        );
                
        // validate password
        passwordStrength('', '');
        
        // change buttons label
        $('#show-btn').text( hidePassText );
    }
    else {
        document.getElementById( "OSSPasswords_editView_fieldName_password" ).value = '**********';
        $('#show-btn').text( showPassText );
        passwordStrength('', '');        
        $('#copy-button').hide();
    }    
}

function showDetailsPassword( record ) {
    var passVal = document.getElementById( "detailPassword" ).innerHTML;
    var showPassText = app.vtranslate('LBL_ShowPassword');
    var hidePassText = app.vtranslate('LBL_HidePassword');
    
    if ( $('#show-btn').text() == showPassText ) {
        var params = {
            'module' : "OSSPasswords",
            'action' : "GetPass",
            'record' : record
        }
        
        AppConnector.request(params).then(
            function(data) {
                var response = data['result'];
                if (response['success']) {
                    var el = document.getElementById( "detailPassword" );
                    el.innerHTML = response['password'];
					$('#copy-button').removeClass('hide').show();
                }
            },
            function(data,err){
            
            }
        );
        
        // change buttons label
        $('#show-btn').text( hidePassText );
    }
    else {
        document.getElementById( "detailPassword" ).innerHTML = '**********';
        $('#show-btn').text( showPassText );
        $('#copy-button').hide();
    }    
}

function showPasswordQuickEdit( record ) {
    var showPassText = app.vtranslate('LBL_ShowPassword');
    var hidePassText = app.vtranslate('LBL_HidePassword');
    
    var params = {
        'module' : "OSSPasswords",
        'action' : "GetPass",
        'record' : record
    }
    
    AppConnector.request(params).then(
        function(data) {
            var response = data['result'];
            if (response['success']) {
                var el = document.getElementById( "detailPassword" );
                el.innerHTML = response['password'];
                $("input[name='password']").val( response['password'] );
				 $('#copy-button').removeClass('hide').show();
            }            
        },
        function(data,err){
        
        }
    );
    
    // change buttons label
    $('#show-btn').text( hidePassText );
}
