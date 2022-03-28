{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Users-Login.Default -->
	{assign var="MODULE" value='Users'}
	<div class="container">
		<div id="login-area" class="login-area">
			<div class="login-space"></div>
			<div class="logo mb-3">
				<img class="logo__img" title="Logo" class="logo" alt="Logo" src="{\App\Layout::getPublicUrl('layouts/resources/Logo/logo')}">
			</div>
			{if !empty(\Config\Main::$loginPageAlertMessage)}
				<div class="alert {if empty(\Config\Main::$loginPageAlertType)}alert-danger{else}{\Config\Main::$loginPageAlertType}{/if} mb-3 px-3 py-1 text-center" role="alert">
					<i class="{if empty(\Config\Main::$loginPageAlertIcon)}fas fa-exclamation-triangle{else}{\Config\Main::$loginPageAlertIcon}{/if}"></i>
					<span class="font-weight-bold mx-2">{\Config\Main::$loginPageAlertMessage}</span>
					<i class="{if empty(\Config\Main::$loginPageAlertIcon)}fas fa-exclamation-triangle{else}{\Config\Main::$loginPageAlertIcon}{/if}"></i>
				</div>
			{/if}
			<div class="" id="loginDiv">
				{if !$IS_BLOCKED_IP}
					<form class="login-form row" action="index.php?module=Users&action=Login" method="POST"
						{if !App\Config::security('LOGIN_PAGE_REMEMBER_CREDENTIALS')}autocomplete="off" {/if}>
						<div class='fieldContainer mx-0 form-row col-md-12'>
							<div class='mx-0 col-sm-10'>
								<label for="username" class="sr-only">{\App\Language::translate('LBL_USER',$MODULE)}</label>
								<div class="input-group form-group first-group">
									<input name="username" type="text" id="username" class="form-control form-control-lg" placeholder="{\App\Language::translate('LBL_USER',$MODULE)}"
										{if \App\Config::main('systemMode') === 'demo'}value="demo" {/if}
										required="" {if !App\Config::security('LOGIN_PAGE_REMEMBER_CREDENTIALS')}autocomplete="off" {/if}>
									<div class="input-group-append">
										<div class="input-group-text"><i class="fas fa-user"></i></div>
									</div>
								</div>
								<label for="password" class="sr-only">{\App\Language::translate('Password',$MODULE)}</label>
								<div class="input-group form-group {if $LANGUAGE_SELECTION || $LAYOUT_SELECTION}first-group {/if}">
									<input name="password" type="password" class="form-control form-control-lg" title="{\App\Language::translate('Password',$MODULE)}" id="password"
										{if \App\Config::main('systemMode') === 'demo'}value="demo" {/if} {if !App\Config::security('LOGIN_PAGE_REMEMBER_CREDENTIALS')}autocomplete="off" {/if}
										placeholder="{\App\Language::translate('Password',$MODULE)}">
									<div class="input-group-append">
										<div class="input-group-text"><i class="fas fa-briefcase"></i></div>
									</div>
								</div>
								{assign var=COUNTERFIELDS value=2}
								{if $LANGUAGE_SELECTION}
									{assign var=COUNTERFIELDS value=$COUNTERFIELDS+1}
									{assign var=DEFAULT_LANGUAGE value=App\Config::main('default_language')}
									<div class="input-group input-group-lg form-group mb-0 {if $LAYOUT_SELECTION}first-group {/if}">
										<select name="loginLanguage" class="form-control-lg form-control" title="{\App\Language::translate('LBL_CHOOSE_LANGUAGE',$MODULE)}">
											{foreach item=VALUE key=KEY from=\App\Language::getAll()}
												<option {if $KEY eq $DEFAULT_LANGUAGE} selected {/if} value="{\App\Purifier::encodeHtml($KEY)}">{$VALUE}</option>
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
										<select name="layout" class="form-control-lg form-control" title="{\App\Language::translate('LBL_SELECT_LAYOUT',$MODULE)}">
											{foreach item=VALUE key=KEY from=\App\Layout::getAllLayouts()}
												<option value="{\App\Purifier::encodeHtml($KEY)}">{$VALUE}</option>
											{/foreach}
										</select>
									</div>
								{/if}
							</div>
							<div class="col-sm-2">
								<button type="submit" class="btn btn-lg btn-primary btn-block heightButtonPhone heightDiv_{$COUNTERFIELDS}" title="{\App\Language::translate('LBL_SIGN_IN', $MODULE_NAME)}">
									<strong><span class="fas fa-chevron-right"></span></strong>
								</button>
							</div>
						</div>
						<input name="fingerprint" type="hidden" id="fingerPrint" value="">
					</form>
					{if App\Config::security('RESET_LOGIN_PASSWORD') && App\Mail::getDefaultSmtp()}
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
							<div class="col-md-12 d-flex justify-content-center"><span class="fas fa-minus-circle fontSizeIcon"></span></div>
							<div class="col-md-12">
								<p>{\App\Language::translate('LBL_IP_IS_BLOCKED',$MODULE_NAME)}</p>
							</div>
						</div>
					{/if}
				</div>
			</div>
			{if App\Config::security('RESET_LOGIN_PASSWORD') && App\Mail::getDefaultSmtp()}
				<div class="d-none" id="forgotPasswordDiv">
					<form class="forgot-form row js-forgot-password" data-js="container">
						<div class="fieldContainer mx-0 form-row col-md-12">
							<div class="login-form mx-0 form-row col-sm-12">
								<label for="emailId" class="sr-only">{\App\Language::translate('LBL_EMAIL',$MODULE)}</label>
								<div class="input-group form-group mb-1 js-email-content" data-js="container">
									<div class="input-group-prepend">
										<div class="input-group-text"><i class="fas fa-envelope"></i></div>
									</div>
									<input type="text" class="form-control form-control-lg" id="emailId" name="email" placeholder="{\App\Language::translate('LBL_EMAIL',$MODULE)}" required="">
								</div>
								<button type="submit" id="retrievePassword" class="btn btn-lg btn-primary btn-block py-2 u-fs-19px" title="Retrieve Password">
									<i class="fas fa-exchange-alt mr-2"></i>{\App\Language::translate('BTN_RESET_PASSWORD',$MODULE)}
								</button>
							</div>
						</div>
					</form>
					<div class="alert d-none js-alert-password mt-2" role="alert">
						<span class="js-alert-text" data-js="container"></span>
					</div>
					<div class="login-text form-group">
						<a href="#" id="backButton">{\App\Language::translate('LBL_TO_CRM',$MODULE)}</a>
					</div>
				</div>
			{/if}
		</div>
	</div>
	</body>

	</html>
	<!-- /tpl-Users-Login.Default -->
{/strip}
