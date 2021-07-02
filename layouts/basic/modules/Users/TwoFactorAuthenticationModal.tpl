{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Users-TwoFactorAuthenticationModal -->
<form name="TwoFactorAuthenticationModal" class="form-horizontal validateForm" action="index.php" method="post" autocomplete="off">
	<input type="hidden" name="module" value="{$MODULE_NAME}"/>
	<input type="hidden" name="action" value="TwoFactorAuthentication"/>
	<input type="hidden" name="mode" value="secret"/>
	<input type="hidden" name="secret" value="{$SECRET}"/>
	<div class="modal-body">
		{if $IS_INIT}
			<div class="alert alert-info">
				{\App\Language::translate('LBL_2FA_SECRET_ALREADY_SET', $MODULE_NAME)}
			</div>
		{/if}
		{if $SHOW_OFF}
			<div class="col-sm-12 my-2">
				<label class="mr-3" for="turn-off-2fa">{\App\Language::translate('LBL_2FA_OFF', $MODULE_NAME)}</label>
				<input type="checkbox" name="turn_off_2fa" id="turn-off-2fa"/>
			</div>
		{/if}
		<div class="js-qr-code" data-js="container|css:display">
			<div class="col-sm-12 p-0 pb-3 border-bottom">
				{\App\Language::translate('LBL_2FA_SECRET', $MODULE_NAME)}: <strong>{$SECRET}</strong>
			</div>
			<div class="col-sm-12 p-0 my-2 d-flex justify-content-center">
				{$QR_CODE_HTML}
			</div>
		</div>
		<div class="col-sm-12 pt-3 border-top form-inline js-user-code" data-js="container|css:display">
			<label for="user_code">
				{\App\Language::translate('LBL_AUTHENTICATION_CODE', $MODULE_NAME)}:
			</label>
			<input class="form-control ml-2" id="user_code" type="text" name="user_code" value=""/>
		</div>
		<div class="alert alert-danger alert-dismissible hide js-wrong-code mt-3 mb-0" role="alert" data-js="container|css:display">
			{\App\Language::translate('LBL_2FA_WRONG_CODE', $MODULE_NAME)}
		</div>
		{if App\Config::main('systemMode') === 'demo'}
			<div class="alert alert-info alert-dismissible show mt-3 mb-0" role="alert">
				<strong>{\App\Language::translate('LBL_2FA_TOTP_INFO_IN_DEMO', $MODULE_NAME)}</strong>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		{/if}
			<div class="alert alert-info show mt-3 mb-0" role="alert">
				<a href="https://doc.yetiforce.com/apps/#2FA" target="_blank" class="btn btn-outline-info float-right js-popover-tooltip" data-content="{App\Language::translate('BTM_GOTO_YETIFORCE_DOCUMENTATION')}" rel="noreferrer noopener" data-js="popover">
					<span class="mdi mdi-book-open-page-variant u-fs-lg"></span>
				</a>
				<span class="mdi mdi-information-outline u-fs-38px mr-2 float-left"></span>
				{\App\Language::translate('LBL_2FA_TOTP_DESC', $MODULE_NAME)}<br />
			</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-success" type="submit" name="saveButton"
				{if App\Config::main('systemMode') === 'demo'}disabled{/if}>
			<span class="yfi yfi-full-editing-view mr-1"></span><strong>{\App\Language::translate('BTN_SAVE', $MODULE_NAME)}</strong>
		</button>
		{if !$LOCK_EXIT}
			<button class="btn btn-danger" type="reset" data-dismiss="modal">
				<span class="fas fa-times mr-1"></span><strong>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
			</button>
		{/if}
	</div>
</form>
<!-- /tpl-Users-TwoFactorAuthenticationModal -->
{/strip}
