{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Users-TwoFactorAuthenticationModal -->
	<form name="TwoFactorAuthenticationModal" class="form-horizontal validateForm" action="index.php" method="post" autocomplete="off">
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="action" value="TwoFactorAuthentication" />
		<input type="hidden" name="mode" value="secret" />
		<input type="hidden" name="secret" value="{$SECRET}" />
		<div class="modal-body">
			{if $SHOW_OFF}
				<div class="col-sm-12 my-2">
					<div class="form-check form-check-inline">
						<label class="mr-3" for="turn-off-2fa">{\App\Language::translate('LBL_2FA_OFF', $MODULE_NAME)}</label>
						<input type="checkbox" name="turn_off_2fa" id="turn-off-2fa" />
					</div>

				</div>
			{/if}
			<div class="js-qr-code" data-js="container|css:display">
				{if !empty($SECRET_OLD)}
					<div class="alert alert-info">
						{\App\Language::translate('LBL_2FA_SECRET_ALREADY_SET', $MODULE_NAME)}
					</div>
				{/if}
				<div class="pb-1">
					<div class="col-sm-6">
						{\App\Language::translate('LBL_2FA_SECRET', $MODULE_NAME)}: ************
						<button type="button" class="btn btn-sm btn-primary js-clipboard ml-2" title="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD')}" data-copy-attribute="clipboard-text" data-clipboard-text="{$SECRET}">
							<span class="fas fa-copy"></span>
						</button>
						{if $SECRET_OLD}
							<button type="button" class="btn btn-sm btn-secondary js-clipboard ml-2" title="{\App\Language::translate('BTN_2FA_SECRET_OLD', $MODULE_NAME)}" data-copy-attribute="clipboard-text" data-clipboard-text="{$SECRET_OLD}">
								<span class="fas fa-copy"></span>
							</button>
						{/if}
					</div>
				</div>
				<div class="col-sm-12 my-2 pt-2 pb-2 d-flex justify-content-center border-top border-bottom">
					{$QR_CODE_HTML}
				</div>
			</div>
			<div class="col-sm-12 pt-2  form-inline js-user-code" data-js="container|css:display">
				<label for="user_code">
					{\App\Language::translate('LBL_AUTHENTICATION_CODE', $MODULE_NAME)}:
				</label>
				<input class="form-control ml-2" id="user_code" type="text" name="user_code" value="" />
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
