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
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="dateFormat" value=$USER_MODEL->get('date_format')}

	<div class="input-group date">
		{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
		<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" class="dateField form-control" name="{$FIELD_MODEL->getFieldName()}" data-date-format="{$dateFormat}"
			type="text" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"   {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldinfo='{$FIELD_INFO}' 
             {if $MODE eq 'edit' && $FIELD_NAME eq 'due_date'} data-user-changed-time="true" {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} />
		<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
		{if $FIELD_NAME neq 'due_date'}
			<span class="input-group-addon notEvent HelpInfoPopover" data-placement="top" data-content="{vtranslate('LBL_AUTO_FILL_DESCRIPTION', $MODULE)}">
				<input type="checkbox" class="autofill" >
			</span>
		{/if}
		
	</div>

{/strip}
