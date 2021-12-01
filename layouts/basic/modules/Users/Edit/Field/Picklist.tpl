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
	<!-- tpl-Users-Edit-Field-Picklist -->
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{if $FIELD_NAME eq 'defaulteventstatus'}
		{assign var=PICKLIST_VALUES value=Vtiger_Module_Model::getInstance('Calendar')->getField('activitystatus')->getPicklistValues()}
	{else if $FIELD_NAME eq 'defaultactivitytype'}
		{assign var=PICKLIST_VALUES value=Vtiger_Module_Model::getInstance('Calendar')->getField('activitytype')->getPicklistValues()}
	{/if}
	{assign var=PLACE_HOLDER value=($FIELD_MODEL->isEmptyPicklistOptionAllowed() && !($FIELD_MODEL->isMandatory() eq true && $FIELD_VALUE neq ''))}
	<select class="select2 form-control" name="{$FIELD_NAME}" data-fieldinfo='{$FIELD_INFO|escape}'
		title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" tabindex="{$FIELD_MODEL->getTabIndex()}"
		data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true && !in_array($FIELD_NAME,['currency_decimal_separator','currency_grouping_separator','authy_methods'])} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
		{if !empty($SPECIAL_VALIDATOR)}data-validator="{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}" {/if}
		data-selected-value='{$FIELD_VALUE}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} {if $PLACE_HOLDER}data-select="allowClear" {/if}>
		{if $PLACE_HOLDER}
			<optgroup class="p-0">
				<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
			</optgroup>
		{/if}
		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			{assign var=OPTION_VALUE value=\App\Purifier::encodeHtml($PICKLIST_NAME)}
			{if $PICKLIST_NAME eq ' ' and ($FIELD_NAME eq 'currency_decimal_separator' || $FIELD_NAME eq 'currency_grouping_separator')}
				{assign var=PICKLIST_VALUE value=\App\Language::translate('LBL_SPACE', 'Users', false, false)}
				{assign var=OPTION_VALUE value=' '}
				<option value="{$OPTION_VALUE}" {if $FIELD_VALUE eq $OPTION_VALUE} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
			{elseif $FIELD_NAME eq 'currency_decimal_separator' || $FIELD_NAME eq 'currency_grouping_separator'}
				<option value="{$OPTION_VALUE}" {if $FIELD_VALUE eq $OPTION_VALUE} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
			{else}
				<option value="{$OPTION_VALUE}" {if trim($FIELD_VALUE) eq trim($OPTION_VALUE)} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
			{/if}
		{/foreach}
	</select>
	<!-- /tpl-Users-Edit-Field-Picklist -->
{/strip}
