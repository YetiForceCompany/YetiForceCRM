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
{if $FIELD_NAME eq 'defaulteventstatus'}
    {assign var=EVENT_MODULE value=Vtiger_Module_Model::getInstance('Events')}
    {assign var=EVENTSTATUS_FIELD_MODEL value=$EVENT_MODULE->getField('activitystatus')}
    {assign var=PICKLIST_VALUES value=$EVENTSTATUS_FIELD_MODEL->getPicklistValues()} 
{else if $FIELD_NAME eq 'defaultactivitytype'}
    {assign var=EVENT_MODULE value=Vtiger_Module_Model::getInstance('Events')}
    {assign var=ACTIVITYTYPE_FIELD_MODEL value=$EVENT_MODULE->getField('activitytype')}
    {assign var=PICKLIST_VALUES value=$ACTIVITYTYPE_FIELD_MODEL->getPicklistValues()} 
{/if}
    <select class="chzn-select form-control" name="{$FIELD_NAME}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-selected-value='{$FIELD_MODEL->get('fieldvalue')}'>
        {if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
        {if $FIELD_MODEL->get('name') eq 'defaulteventstatus' || $FIELD_MODEL->get('name') eq 'defaultactivitytype' }<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
        {foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			{assign var=OPTION_VALUE value=Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}
			{if $PICKLIST_NAME eq ' ' and ($FIELD_NAME eq 'currency_decimal_separator' || $FIELD_NAME eq 'currency_grouping_separator')}
				{assign var=PICKLIST_VALUE value=vtranslate('LBL_SPACE', 'Users')}
				{assign var=OPTION_VALUE value='&nbsp;'}
				<option value="{$OPTION_VALUE}" {if decode_html($FIELD_MODEL->get('fieldvalue')) eq decode_html($OPTION_VALUE)} selected {/if}>{$PICKLIST_VALUE}</option>
			{elseif $FIELD_NAME eq 'currency_decimal_separator' || $FIELD_NAME eq 'currency_grouping_separator'}
				<option value="{$OPTION_VALUE}" {if decode_html($FIELD_MODEL->get('fieldvalue')) eq decode_html($OPTION_VALUE)} selected {/if}>{$PICKLIST_VALUE}</option>
			{else}
				<option value="{$OPTION_VALUE}" {if trim(decode_html($FIELD_MODEL->get('fieldvalue'))) eq trim(decode_html($OPTION_VALUE))} selected {/if}>{$PICKLIST_VALUE}</option>
			{/if}
        {/foreach}
    </select>
{/strip}
