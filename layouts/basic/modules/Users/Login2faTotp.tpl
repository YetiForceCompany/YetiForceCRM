{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Users-Login2faTotp -->
	{assign var="MODULE" value='Users'}
	<div class="container">
		<div id="login-area" class="login-area">
			<div class="login-space"></div>
			<div class="logo mb-2">
				<img title="Logo" height="256px" class="logo" alt="Logo" src="{\App\Layout::getPublicUrl('layouts/resources/Logo/logo')}">
			</div>
			<div id="loginDiv">
				{if !$IS_BLOCKED_IP}
					<form class="login-form" action="index.php?module=Users&action=Login" method="POST"
						autocomplete="off">
						<div class='fieldContainer mx-0 form-row col-md-12'>
							{if !empty($MESSAGE)}
								<div class='mx-0 col-sm-10 alert alert-warning'>
									{$MESSAGE}
								</div>
							{/if}
							<div class='mx-0 alert alert-info'>
								<h4 class="alert-heading">{\App\Language::translate('LBL_2FA_TITLE',$MODULE)}</h4>
								<p>{\App\Language::translate('LBL_2FA_DESCRIPTION',$MODULE)}</p>
							</div>
							<div class='mx-0 col-sm-10'>
								<label for="user_code"
									class="sr-only">{\App\Language::translate('LBL_USER',$MODULE)}</label>
								<div class="input-group form-group first-group">
									<input name="user_code" type="number" id="user-code"
										class="form-control form-control-lg"
										placeholder="{\App\Language::translate('PLL_AUTHY_TOTP',$MODULE)}"
										required="" autocomplete="off"
										data-validation-engine="validate[custom[integer]]">
									<div class="input-group-append">
										<div class="input-group-text"><i class="fas fa-key"></i></div>
									</div>
								</div>
							</div>
							<div class="col-sm-2">
								<button class="btn btn-lg btn-primary btn-block heightButtonPhone"
									type="submit" title="{\App\Language::translate('LBL_SIGN_IN', $MODULE_NAME)}">
									<strong><span class="fas fa-chevron-right"></span></strong>
								</button>
							</div>
						</div>
						<input name="fingerprint" type="hidden" id="fingerPrint" value="">
					</form>
				{/if}
			</div>
		</div>
	</div>
	<!-- /tpl-Users-Login2faTotp -->
{/strip}
