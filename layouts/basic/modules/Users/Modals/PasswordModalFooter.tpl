{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Users-Modals-PasswordModalFooter -->
	<div class="modal-footer">
		{if ($MODE === 'massReset' || $MODE === 'reset') &&  $ACTIVE_SMTP}
			<button class="btn btn-success" type="submit" name="saveButton"
				{if App\Config::main('systemMode') === 'demo'}disabled="disabled" {/if}>
				<span class="fas fa-redo-alt mr-2"></span><strong>{\App\Language::translate('BTN_RESET_PASSWORD', $MODULE_NAME)}</strong>
			</button>
		{/if}
		{if $MODE === 'change'}
			<button class="btn btn-success" type="submit" name="saveButton"
				{if App\Config::main('systemMode') === 'demo'}disabled="disabled" {/if}>
				<span class="fas fa-redo-alt mr-2"></span><strong>{\App\Language::translate('LBL_CHANGE_PASSWORD', $MODULE_NAME)}</strong>
			</button>
		{/if}
		{if $LOCK_EXIT}
			<a class="btn btn-danger js-post-action" role="button" href="index.php?module=Users&amp;parent=Settings&amp;action=Logout">
				<span class="fas fa-power-off mr-2"></span><strong>{\App\Language::translate('LBL_SIGN_OUT', $MODULE_NAME)}</strong>
			</a>
		{else}
			<button class="btn btn-danger" type="reset" data-dismiss="modal">
				<span class="fas fa-times mr-2"></span><strong>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
			</button>
		{/if}
	</div>
	</form>
	<!-- /tpl-Users-Modals-PasswordModalFooter -->
{/strip}
