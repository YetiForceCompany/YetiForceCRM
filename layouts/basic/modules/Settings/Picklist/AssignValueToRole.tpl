{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class='modelContainer modal fade basicAssignValueToRoleView' tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{\App\Language::translate('LBL_ASSIGN_VALUES_TO_ROLES', $QUALIFIED_MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form id="assignValueToRoleForm" class="form-horizontal" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="parent" value="Settings" />
					<input type="hidden" name="source_module" value="{$SELECTED_MODULE_NAME}" />
					<input type="hidden" name="action" value="SaveAjax" />
					<input type="hidden" name="mode" value="assignValueToRole" />
					<input type="hidden" name="picklistName" value="{$SELECTED_PICKLIST_FIELDMODEL->get('name')}" />
					<input type="hidden" name="pickListValues" value='{\App\Json::encode($SELECTED_PICKLISTFIELD_ALL_VALUES)}' />
					<div class="modal-body tabbable">
						<div class="form-group row align-items-center">
							<div class="col-md-3 col-form-label text-right"><span class="redColor">*</span>{\App\Language::translate('LBL_ITEM_VALUE',$QUALIFIED_MODULE)}</div>
							<div class="col-md-9 controls">
								<select multiple class="select2 form-control" id="assignValues" name="assign_values[]">
									{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$SELECTED_PICKLISTFIELD_ALL_VALUES}
										<option value="{$PICKLIST_KEY}">{\App\Language::translate($PICKLIST_VALUE,$SELECTED_MODULE_NAME)}</option>
									{/foreach}
								</select>
							</div>
						</div>		
						{if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
							<div class="form-group row align-items-center">	
								<div class="col-md-3 col-form-label text-right"><span class="redColor">*</span>{\App\Language::translate('LBL_ASSIGN_TO_ROLE',$QUALIFIED_MODULE)}</div>
								<div class="col-md-9 controls">
									<select class="rolesList select2 form-control" id="rolesSelected" name="rolesSelected[]" multiple data-placeholder="{\App\Language::translate('LBL_CHOOSE_ROLES',$QUALIFIED_MODULE)}">
										<option value="all" selected>{\App\Language::translate('LBL_ALL_ROLES',$QUALIFIED_MODULE)}</option>
										{foreach from=$ROLES_LIST item=ROLE}
											<option value="{$ROLE->get('roleid')}">{\App\Language::translate($ROLE->get('rolename'))}</option>
										{/foreach}
									</select>	
								</div>
							</div>
						{/if}
					</div>
					{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
{/strip}
