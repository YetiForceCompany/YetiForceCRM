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
	{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{if $FIELD_NAME eq 'defaulteventstatus'}
		{assign var=EVENT_MODULE value=Vtiger_Module_Model::getInstance('Events')}
		{assign var=EVENTSTATUS_FIELD_MODEL value=$EVENT_MODULE->getField('activitystatus')}
		{assign var=PICKLIST_VALUES value=$EVENTSTATUS_FIELD_MODEL->getPicklistValues()} 
	{else if $FIELD_NAME eq 'defaultactivitytype'}
		{assign var=EVENT_MODULE value=Vtiger_Module_Model::getInstance('Events')}
		{assign var=ACTIVITYTYPE_FIELD_MODEL value=$EVENT_MODULE->getField('activitytype')}
		{assign var=PICKLIST_VALUES value=$ACTIVITYTYPE_FIELD_MODEL->getPicklistValues()} 
	{/if}
		<select class="tpl-Edit-Field-Picklist select2 form-control" name="{$FIELD_NAME}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-selected-value='{$FIELD_VALUE}'>
			{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
			{if $FIELD_MODEL->getName() eq 'defaulteventstatus' || $FIELD_MODEL->getName() eq 'defaultactivitytype' }<option value="">{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
			{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
				{assign var=OPTION_VALUE value=\App\Purifier::encodeHtml($PICKLIST_NAME)}
				{if $PICKLIST_NAME eq ' ' and ($FIELD_NAME eq 'currency_decimal_separator' || $FIELD_NAME eq 'currency_grouping_separator')}
					{assign var=PICKLIST_VALUE value=\App\Language::translate('LBL_SPACE', 'Users')}
					{assign var=OPTION_VALUE value='&nbsp;'}
					<option value="{$OPTION_VALUE}" {if $FIELD_VALUE eq $OPTION_VALUE} selected {/if}>{$PICKLIST_VALUE}</option>
				{elseif $FIELD_NAME eq 'currency_decimal_separator' || $FIELD_NAME eq 'currency_grouping_separator'}
					<option value="{$OPTION_VALUE}" {if $FIELD_VALUE eq $OPTION_VALUE} selected {/if}>{$PICKLIST_VALUE}</option>
				{else}
					<option value="{$OPTION_VALUE}" {if trim($FIELD_VALUE) eq trim($OPTION_VALUE)} selected {/if}>{$PICKLIST_VALUE}</option>
				{/if}
			{/foreach}
		</select>
{/strip}
