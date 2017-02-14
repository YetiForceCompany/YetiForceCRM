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
<div class='modelContainer modal fade basicCreateView' tabindex="-1">
	<div class="modal-dialog">
        <div class="modal-content">
			<div class="modal-header">
				<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">x</button>
				<h3 class="modal-title">{vtranslate('LBL_ADD_ITEM_TO', $QUALIFIED_MODULE)}&nbsp;{vtranslate($SELECTED_PICKLIST_FIELDMODEL->get('label'),$SELECTED_MODULE_NAME)}</h3>
			</div>
			<form name="addItemForm" class="form-horizontal" method="post" action="index.php">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="source_module" value="{$SELECTED_MODULE_NAME}" />
				<input type="hidden" name="action" value="SaveAjax" />
				<input type="hidden" name="mode" value="add" />
				<input type="hidden" name="picklistName" value="{$SELECTED_PICKLIST_FIELDMODEL->get('name')}" />
				<input type="hidden" name="pickListValues" value='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($SELECTED_PICKLISTFIELD_ALL_VALUES))}' />
				<div class="modal-body tabbable">
					<div class="form-group">
						<div class="col-md-3 control-label"><span class="redColor">*</span>{vtranslate('LBL_ITEM_VALUE',$QUALIFIED_MODULE)}</div>
						<div class="col-md-9 controls"><input class="form-control" type="text" data-prompt-position="topLeft:70" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator={\App\Json::encode([['name'=>'FieldLabel']])} name="newValue"></div>
					</div>
					{if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
						<div class="form-group">	
							<div class="col-md-3 control-label">{vtranslate('LBL_ASSIGN_TO_ROLE',$QUALIFIED_MODULE)}</div>
							<div class="col-md-9 controls">
								<select class="rolesList form-control" name="rolesSelected[]" multiple data-placeholder="{vtranslate('LBL_CHOOSE_ROLES',$QUALIFIED_MODULE)}">
									<option value="all" selected>{vtranslate('LBL_ALL_ROLES',$QUALIFIED_MODULE)}</option>
									{foreach from=$ROLES_LIST item=ROLE}
										<option value="{$ROLE->get('roleid')}">{$ROLE->get('rolename')}</option>
									{/foreach}
								</select>	
							</div>
						</div>
					{/if}
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$qualifiedName}
			</form>
		</div>
	</div>
</div>
{/strip}
