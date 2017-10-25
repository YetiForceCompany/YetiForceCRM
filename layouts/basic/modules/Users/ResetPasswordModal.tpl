{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="modal-header">
		<button class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">x</button>
		<h4 class="modal-title">{\App\Language::translate('LBL_RESET_PASSWORD_HEAD', $MODULE_NAME)} - {App\Fields\Owner::getUserLabel($RECORD)}</h4>
	</div>
	<form name="ResetPasswordUsersForm" class="sendByAjax" action="index.php" method="post">
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="action" value="ResetPassword" />
		<input type="hidden" name="record" value="{$RECORD}" />
		<div class="modal-body">
			{if $ACTIVE_SMTP}
				<div class="alert alert-warning" role="alert">{\App\Language::translate('LBL_RESET_PASSWORD_DESC', $MODULE_NAME)}</div>
			{else}
				<div class="alert alert-danger" role="alert">{\App\Language::translate('LBL_RESET_PASSWORD_ERROR', $MODULE_NAME)}</div>
			{/if}
		</div>
		<div class="modal-footer">
			{if $ACTIVE_SMTP}
				<button class="btn btn-success" type="submit" name="saveButton">
					<span class="glyphicon glyphicon-repeat"></span>&nbsp;&nbsp;<strong>{\App\Language::translate('BTN_RESET_PASSWORD', $MODULE_NAME)}</strong>
				</button>
			{/if}
			<button class="btn btn-warning" type="reset" data-dismiss="modal">
				<span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<strong>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
			</button>
		</div>
	</form>		
{/strip}
