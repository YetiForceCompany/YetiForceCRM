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
	<!-- tpl-Users-Login.Default -->
	{assign var="MODULE" value='Users'}
	<div class="container">
		<div id="login-area" class="login-area">
			<div class="login-space"></div>
			<div class="logo">
				<img title="Logo" height="256px" class="logo" alt="Logo"
					 src="{\App\Layout::getPublicUrl('layouts/resources/Logo/logo')}">
			</div>
			<div class="" id="loginDiv">
				{if !$IS_BLOCKED_IP}
					<form class="login-form" action="index.php?module=Users&action=Login" method="POST"
						  {if !AppConfig::security('LOGIN_PAGE_REMEMBER_CREDENTIALS')}autocomplete="off"{/if}>
						<div class='fieldContainer mx-0 form-row col-md-12'>
							<div class='mx-0 col-sm-10'>
								<label for="username"
									   class="sr-only">{\App\Language::translate('LBL_USER',$MODULE)}</label>
								<div class="input-group form-group first-group">
									<input name="username" type="text" id="username"
										   class="form-control form-control-lg"
										   {if \AppConfig::main('systemMode') === 'demo'}value="demo"{/if}
										   placeholder="{\App\Language::translate('LBL_USER',$MODULE)}"
										   required="" {if !AppConfig::security('LOGIN_PAGE_REMEMBER_CREDENTIALS')}autocomplete="off"{/if}
										   autofocus="">
									<div class="input-group-append">
										<div class="input-group-text"><i class="fas fa-user"></i></div>
									</div>
								</div>
								<label for="password"
									   class="sr-only">{\App\Language::translate('Password',$MODULE)}</label>
								<div class="input-group form-group {if $LANGUAGE_SELECTION || $LAYOUT_SELECTION}first-group {/if}">
									<input name="password" type="password" class="form-control form-control-lg"
										   title="{\App\Language::translate('Password',$MODULE)}" id="password"
										   name="password"
										   {if \AppConfig::main('systemMode') === 'demo'}value="demo"{/if} {if !AppConfig::security('LOGIN_PAGE_REMEMBER_CREDENTIALS')}autocomplete="off"{/if}
										   placeholder="{\App\Language::translate('Password',$MODULE)}">
									<div class="input-group-append">
										<div class="input-group-text"><i class="fas fa-briefcase"></i></div>
									</div>
								</div>
								{assign var=COUNTERFIELDS value=2}
								{if $LANGUAGE_SELECTION}
									{assign var=COUNTERFIELDS value=$COUNTERFIELDS+1}
									{assign var=DEFAULT_LANGUAGE value=AppConfig::main('default_language')}
									<div class="input-group input-group-lg form-group mb-0 {if $LAYOUT_SELECTION}first-group {/if}">
										<select class="form-control-lg form-control"
												title="{\App\Language::translate('LBL_CHOOSE_LANGUAGE',$MODULE)}"
												name="loginLanguage">
											{foreach item=VALUE key=KEY from=\App\Language::getAll()}
												<option {if $KEY eq $DEFAULT_LANGUAGE} selected {/if}
														value="{\App\Purifier::encodeHtml($KEY)}">{$VALUE}</option>
											{/foreach}
										</select>
										<div class="input-group-append">
											<div class="input-group-text"><i class="fas fa-language"></i></div>
										</div>
									</div>
								{/if}
								{if $LAYOUT_SELECTION}
									{assign var=COUNTERFIELDS value=$COUNTERFIELDS+1}
									<div class="form-group mb-0">
										<select class="form-control-lg form-control"
												title="{\App\Language::translate('LBL_SELECT_LAYOUT',$MODULE)}"
												name="layout">
											{foreach item=VALUE key=KEY from=\App\Layout::getAllLayouts()}
												<option value="{\App\Purifier::encodeHtml($KEY)}">{$VALUE}</option>
											{/foreach}
										</select>
									</div>
								{/if}
							</div>
							<div class="col-sm-2">
								<button class="btn btn-lg btn-primary btn-block heightButtonPhone heightDiv_{$COUNTERFIELDS}"
										type="submit" title="{\App\Language::translate('LBL_SIGN_IN', $MODULE_NAME)}">
									<strong><span class="fas fa-chevron-right"></span></strong>
								</button>
							</div>
						</div>
						<input name="fingerprint" type="hidden" id="fingerPrint" value="">
					</form>
					{if AppConfig::security('RESET_LOGIN_PASSWORD') && App\Mail::getDefaultSmtp()}
						<div class="form-group">
							<div class="">
								<a href="#" id="forgotpass">{\App\Language::translate('ForgotPassword',$MODULE)}?</a>
							</div>
						</div>
					{/if}
				{/if}
				<div class="form-group col-12 p-0">
					{if !empty($MESSAGE)}
						<div class="alert {if $MESSAGE_TYPE === 'success'}alert-success{elseif $MESSAGE_TYPE === 'error'}alert-danger{else}alert-warning{/if}">
							<p>{$MESSAGE}</p>
						</div>
					{/if}
					{if $IS_BLOCKED_IP}
						<div class="alert alert-danger form-row">
							<div class="col-md-12 d-flex justify-content-center"><span
										class="fas fa-minus-circle fontSizeIcon"></span></div>
							<div class="col-md-12"><p>{\App\Language::translate('LBL_IP_IS_BLOCKED',$MODULE_NAME)}</p>
							</div>
						</div>
					{/if}
				</div>
			</div>
			{if AppConfig::security('RESET_LOGIN_PASSWORD') && App\Mail::getDefaultSmtp()}
				<div class="d-none" id="forgotPasswordDiv">
					<form class="forgot-form" action="index.php?module=Users&action=ForgotPassword" method="POST">
						<div class="fieldContainer mx-0 form-row col-md-12">
							<div class="login-form mx-0 form-row col-sm-10">
								<label for="usernameFp"
									   class="sr-only">{\App\Language::translate('LBL_USER',$MODULE)}</label>
								<div class="input-group form-group first-group">
									<input type="text" class="form-control form-control-lg"
										   title="{\App\Language::translate('LBL_USER',$MODULE)}" id="usernameFp"
										   name="user_name"
										   placeholder="{\App\Language::translate('LBL_USER',$MODULE)}">
									<div class="input-group-append">
										<div class="input-group-text"><i class="fas fa-user"></i></div>
									</div>
								</div>
								<label for="emailId"
									   class="sr-only">{\App\Language::translate('LBL_EMAIL',$MODULE)}</label>
								<div class="input-group form-group">
									<input type="text" class="form-control form-control-lg" autocomplete="off"
										   title="{\App\Language::translate('LBL_EMAIL',$MODULE)}" id="emailId"
										   name="emailId" placeholder="Email">
									<div class="input-group-append">
										<div class="input-group-text"><i class="fas fa-envelope"></i></div>
									</div>
								</div>
							</div>
							<div class="col-sm-2">
								<button type="submit" id="retrievePassword"
										class="btn btn-lg btn-primary btn-block sbutton heightDiv_5"
										title="Retrieve Password">
									{*\App\Language::translate('LBL_SEND',$MODULE)*}
									<strong>></strong>
								</button>
							</div>
						</div>
					</form>
					<div class="login-text form-group">
						<a href="#" id="backButton">{\App\Language::translate('LBL_TO_CRM',$MODULE)}</a>
					</div>
				</div>
			{/if}
		</div>
	</div>
	<script>
		jQuery(document).ready(function () {
			jQuery("#fingerPrint").val(new DeviceUUID().get());
			jQuery("button.close").on('click', function () {
				jQuery(".visible-phone").css('visibility', 'hidden');
			});
			jQuery("a#forgotpass").on('click', function () {
				jQuery("#loginDiv").hide();
				jQuery("#forgotPasswordDiv").removeClass('d-none');
				jQuery("#forgotPasswordDiv").show();
			});
			jQuery("a#backButton").on('click', function () {
				jQuery("#loginDiv").removeClass('d-none');
				jQuery("#loginDiv").show();
				jQuery("#forgotPasswordDiv").hide();
			});
			jQuery("form.forgot-form").on('submit', function (event) {
				if ($("#usernameFp").val() === "" || $("#emailId").val() === "") {
					event.preventDefault();
				}
			});
		});
	</script>
	<!-- /tpl-Users-Login.Default -->
{/strip}
