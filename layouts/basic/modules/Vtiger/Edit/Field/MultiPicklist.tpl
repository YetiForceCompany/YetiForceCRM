{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Base-Edit-Field-MultiPicklist -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{if !empty($FIELD_MODEL->get('fieldvalue'))}
		{assign var=NOT_DISPLAY_LIST_VALUES value=array_diff_key(array_flip($FIELD_VALUE), $PICKLIST_VALUES)}
	{else}
		{assign var=NOT_DISPLAY_LIST_VALUES value=[]}
	{/if}
	<div class="w-100">
		<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="" />
		<select id="{$MODULE}_{$VIEW}_fieldName_{$FIELD_MODEL->getName()}" tabindex="{$FIELD_MODEL->getTabIndex()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" multiple class="select2 form-control {if !empty($NOT_DISPLAY_LIST_VALUES)} hideSelected{/if}" name="{$FIELD_MODEL->getFieldName()}[]" data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isMandatory() eq true} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {/if}{if $FIELD_MODEL->get('dataSelect')} data-select="{$FIELD_MODEL->get('dataSelect')}" {/if}{if $FIELD_MODEL->isEditableReadOnly()} readonly="readonly" {/if}>
			{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
				<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if in_array(\App\Purifier::encodeHtml($PICKLIST_NAME), $FIELD_VALUE)}selected{/if}{if $NOT_DISPLAY_LIST_VALUES && array_key_exists($PICKLIST_NAME, $NOT_DISPLAY_LIST_VALUES)} class="d-none" {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
			{/foreach}
			{foreach from=$NOT_DISPLAY_LIST_VALUES key=PICKLIST_NAME item=ITERATION}
				<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if in_array(\App\Purifier::encodeHtml($PICKLIST_NAME), $FIELD_VALUE)} selected {/if} class="d-none">{\App\Purifier::encodeHtml($PICKLIST_NAME)}</option>
			{/foreach}
		</select>
	</div>
	<!-- /tpl-Base-Edit-Field-MultiPicklist -->
{/strip}
