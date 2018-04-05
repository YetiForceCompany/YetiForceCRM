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
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	<div class="tpl-Edit-Field-PickList">
	<select name="{$FIELD_MODEL->getFieldName()}" class="select2 form-control" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-selected-value='{$FIELD_VALUE}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="" {if $FIELD_MODEL->isMandatory() eq true && $FIELD_VALUE neq ''} disabled{/if}>{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" title="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if trim($FIELD_VALUE) eq trim($PICKLIST_NAME)} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
		{/foreach}
	</select>
	</div>
{/strip}
