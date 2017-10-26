{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="modal-header">
		<button class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">x</button>
		<h4 class="modal-title">{\App\Language::translate($MODE_TITLE, $MODULE_NAME)} - {App\Fields\Owner::getUserLabel($RECORD)}</h4>
	</div>
	<form name="PasswordUsersForm" class="form-horizontal sendByAjax validateForm" action="index.php" method="post" autocomplete="off">
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="action" value="Password" />
		<input type="hidden" name="mode" value="{$MODE}" />
		<input type="hidden" name="record" value="{$RECORD}" />
		<div class="modal-body">
			{if $MODE === 'reset'}
				{if $ACTIVE_SMTP}
					<div class="alert alert-warning" role="alert">{\App\Language::translate('LBL_RESET_PASSWORD_DESC', $MODULE_NAME)}</div>
				{else}
					<div class="alert alert-danger" role="alert">{\App\Language::translate('LBL_RESET_PASSWORD_ERROR', $MODULE_NAME)}</div>
				{/if}
			{elseif $MODE === 'change'}
				<div class="form-group">
					<label class="control-label col-sm-4">{\App\Language::translate('LBL_OLD_PASSWORD', $MODULE_NAME)}</label>
					<div class="controls col-sm-6">
						<input type="password" name="oldPassword" class="form-control" data-validation-engine="validate[required]" autocomplete="off"/>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">{\App\Language::translate('LBL_NEW_PASSWORD', $MODULE_NAME)}</label>
					<div class="col-sm-6 controls">
						<input type="password" name="password" id="passwordUsersFormPassword" title="{\App\Language::translate('LBL_NEW_PASSWORD', $MODULE_NAME)}" class="form-control" data-validation-engine="validate[required,equals[confirmPasswordUsersFormPassword]]" autocomplete="off"/>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">{\App\Language::translate('LBL_CONFIRM_PASSWORD', $MODULE_NAME)}</label>
					<div class="col-sm-6 controls">
						<input type="password" name="confirmPassword" id="confirmPasswordUsersFormPassword" title="{\App\Language::translate('LBL_CONFIRM_PASSWORD', $MODULE_NAME)}" class="form-control" data-validation-engine="validate[required,equals[passwordUsersFormPassword]]" autocomplete="off"/>
					</div>
				</div>
			{/if}
		</div>
		<div class="modal-footer">
			{if $MODE === 'reset' && $ACTIVE_SMTP}
				<button class="btn btn-success" type="submit" name="saveButton">
					<span class="glyphicon glyphicon-repeat"></span>&nbsp;&nbsp;<strong>{\App\Language::translate('BTN_RESET_PASSWORD', $MODULE_NAME)}</strong>
				</button>
			{/if}
			{if $MODE === 'change'}
				<button class="btn btn-success" type="submit" name="saveButton">
					<span class="glyphicon glyphicon-repeat"></span>&nbsp;&nbsp;<strong>{\App\Language::translate('LBL_CHANGE_PASSWORD', $MODULE_NAME)}</strong>
				</button>
			{/if}
			<button class="btn btn-warning" type="reset" data-dismiss="modal">
				<span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<strong>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
			</button>
		</div>
	</form>		
{/strip}
