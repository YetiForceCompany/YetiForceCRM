{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Picklist-AssignRoles -->
	<div class="modal-body js-modal-body pb-0" data-js="container">
		<form class="form-horizontal validateForm" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
			<input type="hidden" name="action" value="SaveAjax" />
			<input type="hidden" name="mode" value="assignValueToRole" />
			<input type="hidden" name="picklistName" value="{$FIELD_MODEL->getName()}" />
			<div class="form-group row align-items-center">
				<div class="col-md-3 col-form-label text-right"><span class="redColor">*</span>{\App\Language::translate('LBL_ITEM_VALUE', $QUALIFIED_MODULE)}</div>
				<div class="col-md-9 controls">
					<select multiple class="select2 form-control" id="assignValues" name="assign_values[]" data-validation-engine="validate[required,funcCall[Vtiger_MultiSelect_Validator_Js.invokeValidation]]">
						{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$SELECTED_PICKLISTFIELD_ALL_VALUES}
							<option value="{$PICKLIST_KEY}">{\App\Language::translate($PICKLIST_VALUE, $SOURCE_MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			{if $FIELD_MODEL->isRoleBased()}
				<div class="form-group row align-items-center">
					<div class="col-md-3 col-form-label text-right"><span class="redColor">*</span>{\App\Language::translate('LBL_ASSIGN_TO_ROLE',$QUALIFIED_MODULE)}</div>
					<div class="col-md-9 controls">
						<select class="rolesList select2 form-control" id="rolesSelected" name="rolesSelected[]" multiple data-placeholder="{\App\Language::translate('LBL_CHOOSE_ROLES',$QUALIFIED_MODULE)}" data-validation-engine="validate[required,funcCall[Vtiger_MultiSelect_Validator_Js.invokeValidation]]">
							<option value="all" selected>{\App\Language::translate('LBL_ALL_ROLES',$QUALIFIED_MODULE)}</option>
							{foreach from=$ROLES_LIST item=ROLE}
								<option value="{$ROLE->get('roleid')}">{\App\Language::translate($ROLE->get('rolename'))}</option>
							{/foreach}
						</select>
					</div>
				</div>
			{/if}
		</form>
	</div>
	<!-- /tpl-Settings-Picklist-AssignRoles -->
{/strip}
