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
	<!-- tpl-Base-Edit-Field-Picklist -->
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{if !isset($PICKLIST_VALUES[$FIELD_VALUE])}
		{assign var=FIELD_VALUE value=\App\Purifier::purify($FIELD_VALUE)}
	{/if}
	{assign var=PLACE_HOLDER value=($FIELD_MODEL->isEmptyPicklistOptionAllowed() && !($FIELD_MODEL->isMandatory() eq true && $FIELD_VALUE neq ''))}
	{assign var=IS_LAZY value=count($PICKLIST_VALUES) > \App\Config::performance('picklistLimit')}
	<div class="w-100">
		<select name="{$FIELD_MODEL->getFieldName()}" class="select2 form-control" data-fieldinfo='{$FIELD_INFO|escape}' tabindex="{$FIELD_MODEL->getTabIndex()}"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
			data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			{if $IS_LAZY} data-select-lazy="true" {/if}
			{if !empty($PLACE_HOLDER)}
				data-select="allowClear" data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION')}"
			{/if}
			{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}
			data-selected-value="{\App\Purifier::encodeHtml($FIELD_VALUE)}" {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if}>
			{if !empty($PLACE_HOLDER)}
				<optgroup class="p-0">
					<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
				</optgroup>
			{/if}
			{if !$IS_LAZY}
				{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
					<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" title="{\App\Purifier::encodeHtml($PICKLIST_VALUE)}" {if trim($FIELD_VALUE) eq trim($PICKLIST_NAME)}selected{/if}>
						{\App\Purifier::encodeHtml($PICKLIST_VALUE)}
					</option>
				{/foreach}
			{/if}
		</select>
	</div>
	<!-- /tpl-Base-Edit-Field-Picklist -->
{/strip}
