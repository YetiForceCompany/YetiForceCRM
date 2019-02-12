{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Modals-Footer modal-footer">
		{if ($MODE === 'massReset' || $MODE === 'reset') &&  $ACTIVE_SMTP}
			<button class="btn btn-success" type="submit" name="saveButton"
					{if AppConfig::main('systemMode') === 'demo'}disabled="disabled"{/if}>
				<span class="fas fa-redo-alt mr-1"></span><strong>{\App\Language::translate('BTN_RESET_PASSWORD', $MODULE_NAME)}</strong>
			</button>
		{/if}
		{if $MODE === 'change'}
			<button class="btn btn-success" type="submit" name="saveButton"
					{if AppConfig::main('systemMode') === 'demo'}disabled="disabled"{/if}>
				<span class="fas fa-redo-alt mr-1"></span><strong>{\App\Language::translate('LBL_CHANGE_PASSWORD', $MODULE_NAME)}</strong>
			</button>
		{/if}
		{if !$LOCK_EXIT}
			<button class="btn btn-danger" type="reset" data-dismiss="modal">
				<span class="fas fa-times mr-1"></span><strong>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
			</button>
		{/if}
	</div>
	</form>
{/strip}
