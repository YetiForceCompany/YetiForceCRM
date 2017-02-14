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
	<select class="chzn-select form-control" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-selected-value='{$FIELD_MODEL->get('fieldvalue')}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="" {if $FIELD_MODEL->isMandatory() eq true && $FIELD_MODEL->get('fieldvalue') neq ''} disabled{/if}>{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" title="{$PICKLIST_VALUE}" {if trim(decode_html($FIELD_MODEL->get('fieldvalue'))) eq trim($PICKLIST_NAME)} selected {/if}>{$PICKLIST_VALUE}</option>
		{/foreach}
	</select>
{/strip}
