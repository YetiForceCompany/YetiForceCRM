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
{strip}
	{assign var="COMPANY_DETAILS" value=App\Company::getInstanceById()}
	{assign var="MODULE" value='Users'}
	<div class="container">
		<div id="login-area" class="login-area">
			<div class="login-space"></div>
			<div class="logo">
				<img title="{$COMPANY_DETAILS->get('name')}" height="{$COMPANY_DETAILS->get('logo_login_height')}px" class="logo" src="{$COMPANY_DETAILS->getLogo('logo_login')->get('imageUrl')}" alt="{$COMPANY_DETAILS->get('name')}">
			</div>
			<div class="" id="loginDiv">
				<div class='fieldContainer marginLeft0 marginRight0 row col-md-12'>
					<form class="login-form" action="index.php?module=Users&action=Login" method="POST" {if !AppConfig::security('LOGIN_PAGE_REMEMBER_CREDENTIALS')}autocomplete="off"{/if}>
						<div class='marginLeft0  marginRight0 row col-xs-10'>
							<div class="form-group first-group has-feedback">
								<label for="username" class="sr-only">{vtranslate('LBL_USER',$MODULE)}</label>
								<input name="username" type="text" id="username" class="form-control input-lg" {if vglobal('systemMode') == 'demo'}value="demo"{/if} placeholder="{vtranslate('LBL_USER',$MODULE)}" required="" {if !AppConfig::security('LOGIN_PAGE_REMEMBER_CREDENTIALS')}autocomplete="off"{/if} autofocus="">
								<span class="adminIcon-user form-control-feedback" aria-hidden="true"></span>
							</div>
							<div class="form-group {if $LANGUAGE_SELECTION || $LAYOUT_SELECTION}first-group {/if} has-feedback">
								<label for="password" class="sr-only">{vtranslate('Password',$MODULE)}</label>
								<input name="password" type="password" class="form-control input-lg" title="{vtranslate('Password',$MODULE)}" id="password" name="password" {if vglobal('systemMode') == 'demo'}value="demo"{/if} {if !AppConfig::security('LOGIN_PAGE_REMEMBER_CREDENTIALS')}autocomplete="off"{/if} placeholder="{vtranslate('Password',$MODULE)}">
								<span class="userIcon-OSSPasswords form-control-feedback" aria-hidden="true"></span>
							</div>
							{assign var=COUNTERFIELDS value=2}
							{if $LANGUAGE_SELECTION}
								{assign var=COUNTERFIELDS value=$COUNTERFIELDS+1}
								<div class="form-group {if $LAYOUT_SELECTION}first-group {/if}">
									<select class="input-lg form-control" title="{vtranslate('LBL_CHOOSE_LANGUAGE',$MODULE)}" name="language">
										{foreach item=VALUE key=KEY from=Vtiger_Language_Handler::getAllLanguages()}
											<option value="{Vtiger_Util_Helper::toSafeHTML($KEY)}">{$VALUE}</option>
										{/foreach}
									</select>	
								</div>
							{/if}
							{if $LAYOUT_SELECTION}
								{assign var=COUNTERFIELDS value=$COUNTERFIELDS+1}
								<div class="form-group">
									<select class="input-lg form-control" title="{vtranslate('LBL_SELECT_LAYOUT',$MODULE)}" name="layout">
										{foreach item=VALUE key=KEY from=Yeti_Layout::getAllLayouts()}
											<option value="{Vtiger_Util_Helper::toSafeHTML($KEY)}">{$VALUE}</option>
										{/foreach}
									</select>	
								</div>
							{/if}
						</div>
						<div class='col-xs-2 marginRight0' >
							<button class="btn btn-lg btn-primary btn-block heightDiv_{$COUNTERFIELDS}" type="submit" title="{vtranslate('LBL_SIGN_IN', $MODULE_NAME)}">
								<strong>></strong>
							</button>
						</div>
					</form>
				</div>
				{if AppConfig::security('RESET_LOGIN_PASSWORD')}
					<div class="form-group">
						<div class="">
							<a href="#" id="forgotpass" >{vtranslate('ForgotPassword',$MODULE)}?</a>
						</div>
					</div>
				{/if}
				<div class="form-group col-xs-12 noPadding">
					{if $ERROR eq 1}
						<div class="alert alert-warning">
							<p>{vtranslate('Invalid username or password.',$MODULE)}</p>
						</div>
					{/if}
					{if $ERROR eq 2}
						<div class="alert alert-warning">
							<p>{vtranslate('Too many failed login attempts.',$MODULE)}</p>
						</div>
					{/if}
					{if $FPERROR}
						<div class="alert alert-warning">
							<p>{vtranslate('Invalid Username or Email address.',$MODULE)}</p>
						</div>
					{/if}
					{if $STATUS}
						<div class="alert alert-success">
							<p>{vtranslate('LBL_MAIL_WAITING_TO_SENT',$MODULE)}</p>
						</div>
					{/if}
					{if $STATUS_ERROR}
						<div class="alert alert-warning">
							<p>{vtranslate('Outgoing mail server was not configured.',$MODULE)}</p>
						</div>
					{/if}
				</div>
			</div>	
			{if AppConfig::security('RESET_LOGIN_PASSWORD')}
				<div class="hide" id="forgotPasswordDiv">
					<div class='fieldContainer marginLeft0 marginRight0 row col-md-12'>
						<form class="login-form" action="modules/Users/actions/ForgotPassword.php" method="POST">
							<div class='marginLeft0  marginRight0 row col-xs-10'>	
								<div class="form-group first-group has-feedback">
									<label for="username" class="sr-only">{vtranslate('LBL_USER',$MODULE)}</label>
									<input type="text" class="form-control input-lg" title="{vtranslate('LBL_USER',$MODULE)}" id="username" name="user_name" placeholder="{vtranslate('LBL_USER',$MODULE)}">
									<span class="adminIcon-user form-control-feedback" aria-hidden="true"></span>
								</div>
								<div class="form-group has-feedback">
									<label for="emailId" class="sr-only">{vtranslate('LBL_EMAIL',$MODULE)}</label>
									<input type="text" class="form-control input-lg" autocomplete="off" title="{vtranslate('LBL_EMAIL',$MODULE)}" id="emailId" name="emailId" placeholder="Email">
									<span class="glyphicon glyphicon-envelope form-control-feedback" aria-hidden="true"></span>
								</div>
							</div>
							<div class='col-xs-2 marginRight0' >
								<button type="submit" style='height:102px' id="retrievePassword" class="btn btn-lg btn-primary btn-block sbutton" title="Retrieve Password">
									{*vtranslate('LBL_SEND',$MODULE)*}
									<strong>></strong>
								</button>
							</div>
						</form>
					</div>
					<div class="login-text form-group">
						<a href="#" id="backButton" >{vtranslate('LBL_TO_CRM',$MODULE)}</a>
					</div>
				</div>
			{/if}
		</div>
	</div>
	<script>
		jQuery(document).ready(function () {
			jQuery("button.close").click(function () {
				jQuery(".visible-phone").css('visibility', 'hidden');
			});
			jQuery("a#forgotpass").click(function () {
				jQuery("#loginDiv").hide();
				jQuery("#forgotPasswordDiv").removeClass('hide');
				jQuery("#forgotPasswordDiv").show();
			});

			jQuery("a#backButton").click(function () {
				jQuery("#loginDiv").removeClass('hide');
				jQuery("#loginDiv").show();
				jQuery("#forgotPasswordDiv").hide();
			});

			jQuery("input[name='retrievePassword']").click(function () {
				var username = jQuery('#user_name').val();
				var email = jQuery('#emailId').val();
				var email1 = email.replace(/^\s+/, '').replace(/\s+$/, '');
				var emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/;
				var illegalChars = /[\(\)\<\>\,\;\:\\\"\[\]]/;

				if (username == '') {
					alert('Please enter valid username');
					return false;
				} else if (!emailFilter.test(email1) || email == '') {
					alert('Please enater valid email address');
					return false;
				} else if (email.match(illegalChars)) {
					alert("The email address contains illegal characters.");
					return false;
				} else {
					return true;
				}
			});
		});
	</script>
{/strip}
