{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
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
	<form name="switchUsersForm" action="index.php" method="post">
		<input type="hidden" name="module" value="{$MODULE_NAME}"/>
		<input type="hidden" name="action" value="SwitchUsers"/>
		<input type="hidden" name="id" value="{$BASE_USER_ID}"/>
		{if count($SWITCH_USERS) neq 0}
			<div class="modal-body text-center">
				<div class="form-group">
					<select class="select2 form-control" name="user" id="user">
						{foreach item=ROW key=USER_ID from=$SWITCH_USERS}
							<option value="{$USER_ID}" class="text-center">
								{$ROW['userName']} ({App\Language::translate($ROW['roleName'])})
							</option>
						{/foreach}
					</select>
				</div>
				<button type="button" class="btn btn-success">
					<span class="fas fa-check mr-1"></span>
					{\App\Language::translate('LBL_SWITCH', $MODULE_NAME)}
				</button>
			</div>
		{/if}
		<div class="modal-footer">
			{if $BASE_USER_ID neq $USER_MODEL->getId()}
				<div class="float-left">
					<div class="btn-toolbar">
						<button class="btn btn-primary getYourself" type="submit">
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
