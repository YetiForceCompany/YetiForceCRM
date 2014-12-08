<!--/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ******************************************************************************* */-->

<!DOCTYPE html>
<html>
    <head>
        <style type="text/css">
            body{
               font-family: Tahoma, "Trebuchet MS","Lucida Grande",Verdana !important;
				background: #F5FAEE !important;/*#f1f6e8;*/
				color : #555 !important;
				font-size: 85% !important;
				height: 98% !important;
            }
			hr{
				border: 1px solid #ddd;
				margin: 13px 0;
			}
            #container{
                min-width:280px;
                width:50%;
                margin-top:2%;
            }
            #content{
                padding:8px 20px;
                border:1px solid #ddd;
                background:#fff;
                border-radius:5px;
            }
            #footer{
                float:right;
            }
            #footer p{
                text-align:right;
                margin-right:20px;
            }
			.button-container a{
				text-decoration: none;
			}
			.button-container{
				float: right;
			}
			.button-container .btn{
				margin-left: 15px;
				min-width: 100px;
				font-weight: bold;
			}
			.logo{
				padding: 15px 0 ;
			}
        </style>
		<script language='JavaScript'>
			function checkPassword () {
				var password = document.getElementById('password').value;
				var confirmPassword = document.getElementById('confirmPassword').value;
				if(password == '' && confirmPassword == ''){
					alert('Please enter new Password');
					return false;
				} else if(password != confirmPassword) {
					alert('Password and Confirm Password should be same');
					return false;
				}else{
					return true;
				}
			}
		</script>
    </head>
    <body>
        <div id="container">
            <div class="logo">
				<img  src="{$LOGOURL}" alt="{$TITLE}" style="height: 4em;width: 12em;"><br><br><br>
			</div>
			<div>
                            {if $LINK_EXPIRED neq 'true'}   
				<div id="content">
					<span><b>{vtranslate('LBL_CHANGE_PASSWORD',$MODULE)}</b></span>
					<hr>
					<div id="changePasswordBlock" align='left'>
						<form name="changePassword" id="changePassword" action="{$TRACKURL}" method="post" accept-charset="utf-8">
							<input type="hidden" name="username" value="{$USERNAME}">
                                                        <input type="hidden" name="shorturl_id" value="{$SHORTURL_ID}"> 
                                                        <input type="hidden" name="secret_hash" value="{$SECRET_HASH}"> 
							<table align='center'>
								<tr>
									<td><label class="control-label" for="password">{vtranslate('LBL_NEW_PASSWORD',$MODULE)}</label></td>
									<td><input type="password" id="password" name="password" placeholder="New Password"></td>
								</tr>
								<tr><td></td></tr>
								<tr>
									<td><label class="control-label" for="confirm_password">{vtranslate('LBL_CONFIRM_PASSWORD',$MODULE)}</label></td>
									<td><input type="password" id="confirmPassword" name="confirmPassword"  placeholder="Confirm Password"></td>
								</tr>
								<tr><td></td></tr>
								<tr>
									<td></td>
									<td><input type="submit" id="btn" value="Submit" onclick="return checkPassword();"/></td>
								</tr>
							</table>
						</form>
					</div>
					{else} 
                                            <div id="content"> 
                                                {vtranslate('LBL_PASSWORD_LINK_EXPIRED_OR_INVALID_PASSWORD', $MODULE)} 
                                            </div>
                                         {/if}
				</div>
			</div>
            
        </div>
	</div>
</div>
</body>
</html>
