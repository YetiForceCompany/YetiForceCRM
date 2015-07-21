{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ********************************************************************************/
-->*}
<style>
#login-area .bg-div{ 
background: url(layouts/vlayout/skins/images/bg.png?{uniqid()}) no-repeat;
}
@media (min-width: 768px) { .visible-phone{ display: none;} }
@media (max-width: 767px) { .visible-phone{ display: block;}  }
</style>
{strip}
{assign var="CompanyDetails" value=getCompanyDetails()}
{assign var="MODULE" value='Users'}
<div class="login_page login_blue">
	<div class=" login-container">
		<div id="login-area" class="login-area">
			<div class="visible-phone">
				<div class="alert alert-block">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<h4>{vtranslate('LBL_MOBILE_VERSION_TITLE',$MODULE)}</h4>
					{vtranslate('LBL_MOBILE_VERSION_DESC',$MODULE)}
					<a class="btn btn-primary" href="modules/Mobile/">{vtranslate('LBL_MOBILE_VERSION_BUTTON',$MODULE)}</a>

				</div>
			</div>		
			<div class="logo">
				<img title="{$CompanyDetails['companyname']}" src="storage/Logo/{$CompanyDetails['logoname']}" alt="{$CompanyDetails['companyname']}">
			</div>
			<div class="forgotPassword hide">
				<h4>{vtranslate('Recover password',$MODULE)}:</h4>
			</div>
			<div class="login-box bg-div" id="loginDiv">
				<div class="login-form-content">
					<form class="form-horizontal row login-form" style="margin:0;" action="index.php?module=Users&action=Login" method="POST">
						<div class="col-md-9 main-panel paddingLRZero">
							<div class="username col-md-12 paddingLRZero">
								<div class="col-md-2 paddingLRZero">
									<img title="{vtranslate('LBL_USER',$MODULE)}" src="layouts/vlayout/skins/images/login.png?{uniqid()}" alt="{vtranslate('LBL_USER',$MODULE)}">
								</div>
								<div class="col-md-10 paddingLRZero">
									<input class="form-control" type="text" title="{vtranslate('LBL_USER',$MODULE)}" id="username" name="username" {if vglobal('systemMode') == 'demo'}value="demo"{/if} placeholder="{vtranslate('LBL_USER',$MODULE)}">
								</div>
								
							</div>
							<div class="password col-md-12 paddingLRZero">
								<div class="col-md-2 paddingLRZero">
									<img title="{vtranslate('Password',$MODULE)}" src="layouts/vlayout/skins/images/pass.png?{uniqid()}" alt="{vtranslate('Password',$MODULE)}">
								</div>
								<div class="col-md-10 paddingLRZero">
									<input class="form-control" type="password" title="{vtranslate('Password',$MODULE)}" id="password" name="password" {if vglobal('systemMode') == 'demo'}value="demo"{/if} placeholder="{vtranslate('Password',$MODULE)}">
								</div>
								
							</div>
						</div>
						<div class="col-md-3 main-panel paddingLRZero">
							<button type="submit" style="background: url(layouts/vlayout/skins/images/button.png?{uniqid()}) no-repeat;" class="btn btn-primary sbutton">Sign in</button>
						</div>
					</form>
				</div>
				<div class="row">
					<div class="forgotpass">
						<div class="">
							<a href="#" id="forgotpass" >{vtranslate('ForgotPassword',$MODULE)}?</a>
						</div>
					</div>
					<div class="col-md-12 nomargin">
						{if isset($smarty.request.error) && $smarty.request.error eq 1}
							<div class="alert alert-warning">
								<p>{vtranslate('Invalid username or password.',$MODULE)}</p>
							</div>
						{/if}
						{if isset($smarty.request.error) && $smarty.request.error eq 2}
							<div class="alert alert-warning">
								<p>{vtranslate('Too many failed login attempts.',$MODULE)}</p>
							</div>
						{/if}
						{if isset($smarty.request.fpError)}
							<div class="alert alert-warning">
								<p>{vtranslate('Invalid Username or Email address.',$MODULE)}</p>
							</div>
						{/if}
						{if isset($smarty.request.status)}
							<div class="alert alert-success">
								<p>{vtranslate('Mail has been sent to your inbox, please check your e-mail.',$MODULE)}</p>
							</div>
						{/if}
						{if isset($smarty.request.statusError)}
							<div class="alert alert-warning">
								<p>{vtranslate('Outgoing mail server was not configured.',$MODULE)}</p>
							</div>
						{/if}
					</div>
				</div>
			</div>
			<div class="login-box hide bg-div" id="forgotPasswordDiv">
				<div class="login-form-content">
					<form class="form-horizontal row login-form" style="margin:0;" action="modules/Users/actions/ForgotPassword.php" method="POST">
						<div class="col-md-9 main-panel paddingLRZero">
							<div class="username col-md-12 paddingLRZero">
								<div class="col-md-2 paddingLRZero">
									<img title="{vtranslate('LBL_USER',$MODULE)}" src="layouts/vlayout/skins/images/login.png?{uniqid()}" alt="{vtranslate('LBL_USER',$MODULE)}">
								</div>
								<div class="col-md-10 paddingLRZero">
									<input type="text" class="form-control" title="{vtranslate('LBL_USER',$MODULE)}" id="username" name="user_name" placeholder="{vtranslate('LBL_USER',$MODULE)}">
								</div>
								
							</div>
							<div class="password col-md-12 paddingLRZero">
								<div class="col-md-2 paddingLRZero">
									<img title="{vtranslate('LBL_EMAIL',$MODULE)}" src="layouts/vlayout/skins/images/email.png?{uniqid()}" alt="{vtranslate('LBL_EMAIL',$MODULE)}">
								</div>
								<div class="col-md-10 paddingLRZero">
									<input type="text" class="form-control" autocomplete="off" title="{vtranslate('LBL_EMAIL',$MODULE)}" id="password" name="emailId" placeholder="Email">
								</div>
							</div>
						</div>
						<div class="col-md-3 main-panel paddingLRZero">
							<button type="submit" id="retrievePassword" style="background: url(layouts/vlayout/skins/images/button.png?{uniqid()}) no-repeat;" class="btn btn-primary sbutton">Retrieve Password</button>
						</div>
					</form>
					<div class="col-md-12 backButtonBox">
						<a href="#" id="backButton" >{vtranslate('LBL_TO_CRM',$MODULE)}</a>
					</div>
					<div class="col-md-12 nomargin"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function(){
		jQuery("button.close").click(function() {
			jQuery(".visible-phone").hide();
		});
		jQuery("a#forgotpass").click(function() {
			jQuery("#loginDiv").hide();
			jQuery("#forgotPasswordDiv").removeClass('hide');
			jQuery("#forgotPasswordDiv").show();
		});
		
		jQuery("a#backButton").click(function() {
			jQuery("#loginDiv").removeClass('hide');
			jQuery("#loginDiv").show();
			jQuery("#forgotPasswordDiv").hide();
		});
		
		jQuery("input[name='retrievePassword']").click(function (){
			var username = jQuery('#user_name').val();
			var email = jQuery('#emailId').val();
			var email1 = email.replace(/^\s+/,'').replace(/\s+$/,'');
			var emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/ ;
			var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/ ;
			
			if(username == ''){
				alert('Please enter valid username');
				return false;
			} else if(!emailFilter.test(email1) || email == ''){
				alert('Please enater valid email address');
				return false;
			} else if(email.match(illegalChars)){
				alert( "The email address contains illegal characters.");
				return false;
			} else {
				return true;
			}
		});
	});
</script>
{/strip}
