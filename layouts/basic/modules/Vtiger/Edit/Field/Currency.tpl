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
	<div class="tpl-Edit-Field-Currency">
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_NAME" value=$FIELD_MODEL->getName()}
	{assign var="SYMBOL_PLACEMENT" value=$USER_MODEL->currency_symbol_placement}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{if $FIELD_MODEL->getUIType() eq '71'}
		<div class="input-group">
			{if $SYMBOL_PLACEMENT neq '1.0$'}
				<span class="input-group-append"><span class="input-group-text"> {$USER_MODEL->get('currency_symbol')}</span></span>
			{/if}
			<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" class="currencyField form-control {if $SYMBOL_PLACEMENT eq '1.0$'} textAlignRight {/if}" name="{$FIELD_MODEL->getFieldName()}"
				   data-fieldinfo='{$FIELD_INFO}' value="{$FIELD_VALUE}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} 
				   data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' data-number-of-decimal-places='{$USER_MODEL->get('no_of_currency_decimals')}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{else} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if}/>
			{if $SYMBOL_PLACEMENT eq '1.0$'}
				<span class="input-group-append"><span class="input-group-text"> {$USER_MODEL->get('currency_symbol')}</span></span>
			{/if}
		</div>
	{else if ($FIELD_MODEL->getUIType() eq '72') && ($FIELD_MODEL->getName() eq 'unit_price')}
		<div class="input-group">
			{if $SYMBOL_PLACEMENT neq '1.0$'}
				<span class="input-group-append row"><span class="input-group-text">{$BASE_CURRENCY_SYMBOL}</span></span>
			{/if}
			{assign var="DISPLAY_FIELD_VALUE" value=$FIELD_VALUE}
			<input id="{$MODULE}-editview-fieldname-{$FIELD_NAME}" type="text" class="col-md-12 unitPrice currencyField form-control {if $SYMBOL_PLACEMENT eq '1.0$'} textAlignRight {/if}" name="{$FIELD_MODEL->getFieldName()}"
				   data-fieldinfo='{$FIELD_INFO}'  value="{$DISPLAY_FIELD_VALUE}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if}
				   data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' data-number-of-decimal-places='{$USER_MODEL->get('no_of_currency_decimals')}'
				   {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{else} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if}/>
			{if $SYMBOL_PLACEMENT eq '1.0$'}
				<span class="input-group-append row"><span class="input-group-text">{$BASE_CURRENCY_SYMBOL}</span></span>
			{/if}
		</div>
		<input type="hidden" name="base_currency" value="{$BASE_CURRENCY_NAME}">
		<input type="hidden" name="cur_{$BASE_CURRENCY_ID}_check" value="on">
		<input type="hidden" id="requstedUnitPrice" name="{$BASE_CURRENCY_NAME}" value="">
		{if $VIEW eq 'Edit'}
			<a id="moreCurrencies" class="span u-cursor-pointer">{\App\Language::translate('LBL_MORE_CURRENCIES', $MODULE)}>></a>
			<span id="moreCurrenciesContainer" class="d-none"></span>
		{/if}
	{else}
		<div class="input-group">
			<div class="row">
				<span class="col-md-1 input-group-append"><span class="input-group-text row">{$USER_MODEL->get('currency_symbol')}</span></span>
					{assign var="DISPLAY_FIELD_VALUE" value=$FIELD_VALUE}
				<span class="col-md-7"><input type="text" class="row-fluid currencyField form-control" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
											  data-fieldinfo='{$FIELD_INFO}' value="{$DISPLAY_FIELD_VALUE}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} /></span>
			</div>
		</div>
	{/if}
	</div>
{/strip}
