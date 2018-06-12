{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-TwoFactorAuthentication-Index">
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="contents">
			<div class="alert alert-info">
				<h5 class="alert-heading">{\App\Language::translate('LBL_2FA_CONF', $QUALIFIED_MODULE)}</h5>
				<p><strong>{\App\Language::translate('LBL_TOTP_AUTHY_MODE',$QUALIFIED_MODULE)}</strong> - {\App\Language::translate('LBL_TOTP_AUTHY_MODE_DESC',$QUALIFIED_MODULE)}</p>
				<p><strong>{\App\Language::translate('LBL_TOTP_USER_AUTHY_EXCEPTIONS',$QUALIFIED_MODULE)}</strong> - {\App\Language::translate('LBL_TOTP_USER_AUTHY_EXCEPTIONS_DESC',$QUALIFIED_MODULE)}</p>
				<p><strong>{\App\Language::translate('LBL_TOTP_NUMBER_OF_WRONG_ATTEMPTS',$QUALIFIED_MODULE)}</strong> - {\App\Language::translate('LBL_TOTP_NUMBER_OF_WRONG_ATTEMPTS_DESC',$QUALIFIED_MODULE)}</p>
			</div>
		</div>
		<div>
			<form class="js-two-factor-auth__form" method="post" data-js="submit">
				<input type="hidden" name="parent" value="Settings">
				<input type="hidden" name="module" value="{$MODULE}">
				<input type="hidden" name="action" value="SaveAjax">
				<div class="form-group row">
					<label for="methods" class="col-12 col-lg-2 col-form-label">{\App\Language::translate('LBL_TOTP_AUTHY_MODE', $QUALIFIED_MODULE)}</label>
					<div class="col-12 col-lg-4">
						<select class="select2" name="methods" id="methods">
							{foreach from=$AVAILABLE_METHODS item=METHOD}
								{assign var=LBL_METHOD value="LBL_$METHOD"}
								<option value="{$METHOD}" {if $METHOD===$USER_AUTHY_MODE} selected {/if}>{\App\Language::translate($LBL_METHOD, $QUALIFIED_MODULE)}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label for="users" class="col-12 col-lg-2 col-form-label">{\App\Language::translate('LBL_TOTP_USER_AUTHY_EXCEPTIONS', $QUALIFIED_MODULE)}</label>
					<div class="col-12 col-lg-4">
						<select multiple="" name="users" class="select2 form-control" id="users">
							{foreach key=KEY item=USER from=\App\Fields\Owner::getAllUsers()}
								<option value="{$USER['id']}" {if in_array($USER['id'], $USER_EXCEPTIONS)} selected {/if}>{$USER['fullName']}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label for="number-wrong-attempts" class="col-12 col-lg-2 col-form-label">{\App\Language::translate('LBL_TOTP_NUMBER_OF_WRONG_ATTEMPTS', $QUALIFIED_MODULE)}</label>
					<div class="col-12 col-lg-4">
						<input type="text" name="number_wrong_attempts"
							   value="{$USER_AUTHY_TOTP_NUMBER_OF_WRONG_ATTEMPTS}" id="number-wrong-attempts"
							   data-validation-engine="validate[custom[integer]]">
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
