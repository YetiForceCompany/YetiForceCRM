{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header">
		<h5 class="modal-title">
			{if $MODE === 'reset' || $MODE === 'massReset'}
				<span class="fas fa-redo-alt mr-1"></span>
			{elseif $MODE === 'change'}
				<span class="fas fa-key mr-1"></span>
			{/if}
			{\App\Language::translate($MODE_TITLE, $MODULE_NAME)}{if $RECORD} - {App\Fields\Owner::getUserLabel($RECORD)}{/if}
		</h5>
		{if !$LOCK_EXIT}
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		{/if}
	</div>
	<form name="PasswordUsersForm" class="form-horizontal sendByAjax validateForm" action="index.php" method="post" autocomplete="off">
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="action" value="Password" />
		<input type="hidden" name="mode" value="{$MODE}" />
		<input type="hidden" name="record" value="{$RECORD}" />
		{if $MODE === 'massReset'}
			<input type="hidden" name="selected_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_IDS))}">
            <input type="hidden" name="excluded_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($EXCLUDED_IDS))}">
			<input type="hidden" name="search_params" value='{\App\Purifier::encodeHtml(\App\Json::encode($SEARCH_PARAMS))}' />
		{/if}
		<div class="modal-body">
			{if $MODE === 'reset' || $MODE === 'massReset'}
				{if $ACTIVE_SMTP}
					<div class="alert alert-warning" role="alert">{\App\Language::translate('LBL_RESET_PASSWORD_DESC', $MODULE_NAME)}</div>
				{else}
					<div class="alert alert-danger" role="alert">{\App\Language::translate('LBL_RESET_PASSWORD_ERROR', $MODULE_NAME)}</div>
				{/if}
			{elseif $MODE === 'change'}
				{if $WARNING}
					<div class="alert alert-danger" role="alert">
						<span class="fas fa-exclamation-circle fs30 float-left mr-2"></span>
						{$WARNING}
					</div>
				{/if}
				{if App\User::getCurrentUserId() == $RECORD}
					<div class="form-group">
						<label class="col-form-label col-sm-4">{\App\Language::translate('LBL_OLD_PASSWORD', $MODULE_NAME)}</label>
						<div class="controls col-sm-6">
							<input type="password" name="oldPassword" class="form-control" data-validation-engine="validate[required]" autocomplete="off" />
						</div>
					</div>
				{/if}
				<div class="form-group">
					<label class="col-sm-4 col-form-label">{\App\Language::translate('LBL_NEW_PASSWORD', $MODULE_NAME)}</label>
					<div class="col-sm-6 controls">
						<input type="password" name="password" id="passwordUsersFormPassword" title="{\App\Language::translate('LBL_NEW_PASSWORD', $MODULE_NAME)}" class="form-control" data-validation-engine="validate[required]]" autocomplete="off" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 col-form-label">{\App\Language::translate('LBL_CONFIRM_PASSWORD', $MODULE_NAME)}</label>
					<div class="col-sm-6 controls">
						<input type="password" name="confirmPassword" id="confirmPasswordUsersFormPassword" title="{\App\Language::translate('LBL_CONFIRM_PASSWORD', $MODULE_NAME)}" class="form-control" data-validation-engine="validate[required,equals[passwordUsersFormPassword]]" autocomplete="off" />
					</div>
				</div>
				<div class="alert alert-info alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<strong>{\App\Language::translate('LBL_NEW_PASSWORD_CRITERIA', $MODULE_NAME)}</strong><br />
					<ul>
						<li>{\App\Language::translate('Minimum password length', 'Settings::Password')}: {$PASS_CONFIG['min_length']}</li>
						<li>{\App\Language::translate('Maximum password length', 'Settings::Password')}: {$PASS_CONFIG['max_length']}</li>
						{if $PASS_CONFIG['big_letters'] =='true'}<li>{\App\Language::translate('Uppercase letters from A to Z', 'Settings::Password')}</li>{/if}
						{if $PASS_CONFIG['small_letters'] =='true'}<li>{\App\Language::translate('Lowercase letters a to z', 'Settings::Password')}</li>{/if}
						{if $PASS_CONFIG['numbers'] =='true'}<li>{\App\Language::translate('Password should contain numbers', 'Settings::Password')}</li>{/if}
						{if $PASS_CONFIG['special'] =='true'}<li>{\App\Language::translate('Password should contain special characters', 'Settings::Password')}</li>{/if}
					</ul>
				</div>
			{/if}
		</div>
		<div class="modal-footer">
			{if ($MODE === 'massReset' || $MODE === 'reset') &&  $ACTIVE_SMTP}
				<button class="btn btn-success" type="submit" name="saveButton" {if AppConfig::main('systemMode') === 'demo'}disabled="disabled"{/if}>
					<span class="fas fa-redo-alt mr-1"></span><strong>{\App\Language::translate('BTN_RESET_PASSWORD', $MODULE_NAME)}</strong>
				</button>
			{/if}
			{if $MODE === 'change'}
				<button class="btn btn-success" type="submit" name="saveButton" {if AppConfig::main('systemMode') === 'demo'}disabled="disabled"{/if}>
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
