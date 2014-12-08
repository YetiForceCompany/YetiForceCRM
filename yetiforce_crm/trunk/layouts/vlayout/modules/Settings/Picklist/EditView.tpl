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
<div class='modelContainer'>
	<div class="modal-header">
		<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">x</button>
		<h3>{vtranslate('LBL_RENAME_PICKLIST_ITEM', $QUALIFIED_MODULE)}</h3>
	</div>
	<form id="renameItemForm" class="form-horizontal" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="parent" value="Settings" />
		<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
		<input type="hidden" name="action" value="SaveAjax" />
		<input type="hidden" name="mode" value="rename" />
		<input type="hidden" name="picklistName" value="{$FIELD_MODEL->get('name')}" />
		<input type="hidden" name="pickListValues" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($SELECTED_PICKLISTFIELD_EDITABLE_VALUES))}' />
		<div class="modal-body tabbable">
			<div class="control-group">
				<div class="control-label">{vtranslate('LBL_ITEM_TO_RENAME',$QUALIFIED_MODULE)}</div>
				<div class="controls">
					{assign var=PICKLIST_VALUES value=$SELECTED_PICKLISTFIELD_EDITABLE_VALUES}
					<select class="chzn-select" name="oldValue">
						<optgroup>
							{foreach from=$PICKLIST_VALUES key=PICKLIST_VALUE_KEY item=PICKLIST_VALUE}
								<option {if $FIELD_VALUE eq $PICKLIST_VALUE} selected="" {/if}value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE)}" data-id={$PICKLIST_VALUE_KEY}>{vtranslate($PICKLIST_VALUE,$SOURCE_MODULE)}</option>
							{/foreach}	
						</optgroup>
					</select>	
				</div><br>
				<div class="control-label"><span class="redColor">*</span>{vtranslate('LBL_ENTER_NEW_NAME',$QUALIFIED_MODULE)}</div>
				<div class="controls"><input type="text" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator={Zend_Json::encode([['name'=>'FieldLabel']])} name="newValue"></div>
				{if $SELECTED_PICKLISTFIELD_NON_EDITABLE_VALUES}
					<br>
					<div class="control-label">{vtranslate('LBL_NON_EDITABLE_PICKLIST_VALUES',$QUALIFIED_MODULE)}</div>
					<div class="controls nonEditableValuesDiv">
						<ul class="nonEditablePicklistValues" style="list-style-type: none;">
						{foreach from=$SELECTED_PICKLISTFIELD_NON_EDITABLE_VALUES key=NON_EDITABLE_VALUE_KEY item=NON_EDITABLE_VALUE}
							<li>{vtranslate($NON_EDITABLE_VALUE,$SOURCE_MODULE)}</li>
						{/foreach}
						</ul>
					</div>
				{/if}
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$qualifiedName}
	</form>
</div>
{/strip}