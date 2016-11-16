{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
   * Contributor(s): YetiForce.com
 ********************************************************************************/
-->*}
{strip}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="FIELD_VALUE_LIST" value=$FIELD_MODEL->getUITypeModel()->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}
{if !empty($FIELD_MODEL->get('fieldvalue'))}
	{assign var=NOT_DISPLAY_LIST_VALUES value=array_diff_key(array_flip($FIELD_VALUE_LIST), $PICKLIST_VALUES)}
{else}
	{assign var=NOT_DISPLAY_LIST_VALUES value=[]}
{/if}
<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="" />
<select id="{$MODULE}_{$VIEW}_fieldName_{$FIELD_MODEL->get('name')}" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" multiple class="chzn-select form-control col-md-12 {if !empty($NOT_DISPLAY_LIST_VALUES)} hideSelected{/if}" name="{$FIELD_MODEL->getFieldName()}[]" data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isMandatory() eq true} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
    {foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
		<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if in_array(Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME), $FIELD_VALUE_LIST)} selected {/if}{if $NOT_DISPLAY_LIST_VALUES && array_key_exists($PICKLIST_NAME, $NOT_DISPLAY_LIST_VALUES)} class="hide" {/if}>{$PICKLIST_VALUE}</option>
    {/foreach}
	{foreach from=$NOT_DISPLAY_LIST_VALUES key=PICKLIST_NAME item=ITERATION}
		<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if in_array(Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME), $FIELD_VALUE_LIST)} selected {/if} class="hide">{$PICKLIST_NAME}</option>
	{/foreach}
</select>
{/strip}
