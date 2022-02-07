{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Users-SwitchUsers -->
	<div class="modal-header">
		<h5 class="modal-title">
			<span class="fas fa-exchange-alt mr-1"></span>
			{\App\Language::translate('LBL_SWITCH_USER', $MODULE_NAME)}
		</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<form name="switchUsersForm" class="validateForm" action="index.php" method="post">
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="action" value="SwitchUsers" />
		<input type="hidden" name="id" value="{$BASE_USER_ID}" />
		{if count($SWITCH_USERS) neq 0}
			{assign var=FIRST_SWITCH_USER value=current($SWITCH_USERS)}
			<div class="modal-body text-center">
				<div class="form-group">
					<select class="select2 form-control js-switch-user" name="user" id="user">
						{foreach item=ROW key=USER_ID from=$SWITCH_USERS}
							<option value="{$USER_ID}" class="text-center" data-admin="{$ROW.isAdmin}">
								{$ROW['userName']} ({App\Language::translate($ROW['roleName'])})
							</option>
						{/foreach}
					</select>
				</div>
				{if \App\Config::security('askAdminAboutVisitSwitchUsers', true)}
					<div class="form-group js-sub-container{if !$FIRST_SWITCH_USER.isAdmin} d-none{/if}">
						<textarea id="visitPurpose" placeholder="{App\Language::translate('LBL_VISIT_PURPOSE_INFO',$MODULE_NAME)}" maxlength="501" class="form-control js-text-element" name="visitPurpose" data-validation-engine="validate[required,maxSize[500]]" {if !$FIRST_SWITCH_USER.isAdmin} disabled="disabled" {/if}></textarea>
					</div>
				{/if}
				<button type="button" class="btn btn-success js-switch-btn">
					<span class="fas fa-check mr-1"></span>
					{\App\Language::translate('LBL_SWITCH', $MODULE_NAME)}
				</button>
			</div>
		{/if}
		<div class="modal-footer">
			{if $BASE_USER_ID neq $USER_MODEL->getId()}
				<div class="float-left">
					<div class="btn-toolbar">
						<button class="btn btn-primary js-switch-to-yourself" type="button">
							<strong>{\App\Language::translate('LBL_SWITCH_TO_YOURSELF', $MODULE_NAME)}</strong></button>
					</div>
				</div>
			{/if}
			<button type="button" class="btn btn-danger" data-dismiss="modal">
				<span class="fas fa-times mr-1"></span>
				{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}
			</button>
		</div>
	</form>
	<!-- /tpl-Users-SwitchUsers -->
{/strip}
