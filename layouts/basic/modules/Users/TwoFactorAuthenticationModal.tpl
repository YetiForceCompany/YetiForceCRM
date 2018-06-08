{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<form name="PasswordUsersForm" class="form-horizontal validateForm tpl-TwoFactorAuthenticationModal" action="index.php" method="post"
		  autocomplete="off">
		<input type="hidden" name="module" value="{$MODULE_NAME}"/>
		<input type="hidden" name="action" value="TwoFactorAuthentication"/>
		<input type="hidden" name="secret" value="{$SECRET}"/>
		<div class="modal-body">
			<div class="col-sm-12 controls">
				{\App\Language::translate('LBL_2FA_SECRET', $MODULE_NAME)}: {$SECRET}
			</div>
			<div class="col-sm-12 controls marginBottom10px marginTop10">
				{$QR_CODE_HTML}
			</div>
			<div class="col-sm-12 controls">
				{\App\Language::translate('LBL_AUTHENTICATION_CODE', $MODULE_NAME)}: <input type="number"
																							name="user_code"
																							value=""/>
			</div>
		</div>
		<div class="modal-body">
			<div class="alert alert-info alert-dismissible hide js-wrong-code" role="alert"
				 data-js="container|css:display">
				{\App\Language::translate('LBL_2FA_WRONG_CODE', $MODULE_NAME)}
			</div>
			{if AppConfig::main('systemMode') === 'demo'}
				<div class="alert alert-info alert-dismissible" role="alert">
					<b>{\App\Language::translate('LBL_2FA_TOTP_INFO_IN_DEMO', $MODULE_NAME)}</b>
				</div>
			{/if}
		</div>
		<div class="modal-footer">
			{if AppConfig::main('systemMode') === 'demo'}
				<div class="alert alert-info alert-dismissible" role="alert">
					<b>{\App\Language::translate('LBL_2FA_TOTP_INFO_IN_DEMO', $MODULE_NAME)}</b>
				</div>
			{/if}
			<button class="btn btn-success" type="submit" name="saveButton"
					{if AppConfig::main('systemMode') === 'demo'}disabled="disabled"{/if}>
				<span class="fas fa-edit mr-1"></span><strong>{\App\Language::translate('BTN_SAVE', $MODULE_NAME)}</strong>
			</button>
			{if !$LOCK_EXIT}
				<button class="btn btn-danger" type="reset" data-dismiss="modal">
					<span class="fas fa-times mr-1"></span><strong>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
				</button>
			{/if}
		</div>
	</form>
{/strip}
