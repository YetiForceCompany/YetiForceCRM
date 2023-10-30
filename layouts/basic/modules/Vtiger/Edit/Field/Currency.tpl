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
	<!-- tpl-Base-Edit-Field-Currency -->
	{function FUN_CURRENCY_SYMBOL CURRENCY_SYMBOL='' CLASS=''}
		<span class="input-group-append {$CLASS}">
			<span class="input-group-text js-currency" data-js="text">
				{$CURRENCY_SYMBOL}
			</span>
		</span>
	{/function}
	<div>
		{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
		{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
		{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
		{assign var="SYMBOL_PLACEMENT" value=$USER_MODEL->currency_symbol_placement}
		{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
		{if $FIELD_MODEL->getUIType() eq '71'}
			<div class="input-group {$WIDTHTYPE_GROUP}" data-uitype="71">
				{if $SYMBOL_PLACEMENT neq '1.0$'}
					{FUN_CURRENCY_SYMBOL CURRENCY_SYMBOL=$USER_MODEL->get('currency_symbol')}
				{/if}
				<input name="{$FIELD_MODEL->getFieldName()}" value="{$FIELD_VALUE}" type="text" class="currencyField form-control {if $SYMBOL_PLACEMENT eq '1.0$'} textAlignRight {/if}" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" tabindex="{$FIELD_MODEL->getTabIndex()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
					data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}
					data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}'
					data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}'
					data-number-of-decimal-places='{$USER_MODEL->get('no_of_currency_decimals')}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {else} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if} />
				{if $SYMBOL_PLACEMENT eq '1.0$'}
					{FUN_CURRENCY_SYMBOL CURRENCY_SYMBOL=$USER_MODEL->get('currency_symbol')}
				{/if}
			</div>
		{elseif ($FIELD_MODEL->getUIType() eq '72')}
			<div class="input-group {$WIDTHTYPE_GROUP}">
				{assign var="DISPLAY_FIELD_VALUE" value=$FIELD_VALUE}
				{if $SYMBOL_PLACEMENT neq '1.0$'}
					{if !empty($RECORD_ID) && !empty($RECORD->get('currency_id')) }
						{assign var="CURRENCY" value=\App\Fields\Currency::getById($RECORD->get('currency_id'))}
						{FUN_CURRENCY_SYMBOL CURRENCY_SYMBOL=$CURRENCY['currency_symbol']}
					{else}
						{FUN_CURRENCY_SYMBOL CURRENCY_SYMBOL=$USER_MODEL->get('currency_symbol')}
					{/if}
				{/if}
				<input name="{$FIELD_MODEL->getFieldName()}" value="{$DISPLAY_FIELD_VALUE}" type="text" class="row-fluid currencyField form-control" tabindex="{$FIELD_MODEL->getTabIndex()}"
					data-fieldinfo='{$FIELD_INFO}'
					data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
					title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
					{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}'
					data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}'
					{if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} />
				{if $SYMBOL_PLACEMENT eq '1.0$'}
					{if !empty($RECORD_ID) && !empty($RECORD->get('currency_id')) }
						{assign var="CURRENCY" value=\App\Fields\Currency::getById($RECORD->get('currency_id'))}
						{FUN_CURRENCY_SYMBOL CURRENCY_SYMBOL=$CURRENCY['currency_symbol']}
					{else}
						{FUN_CURRENCY_SYMBOL CURRENCY_SYMBOL=$USER_MODEL->get('currency_symbol')}
					{/if}
				{/if}
			</div>
		{else}
			<div class="input-group {$WIDTHTYPE_GROUP}">
				<div class="row">
					<span class="col-md-1 input-group-append">
						<span class="input-group-text row js-currency" data-js="text">
							{$USER_MODEL->get('currency_symbol')}
						</span>
					</span>
					{assign var="DISPLAY_FIELD_VALUE" value=$FIELD_VALUE}
					<span class="col-md-7">
						<input name="{$FIELD_MODEL->getFieldName()}" value="{$DISPLAY_FIELD_VALUE}" type="text" class="row-fluid currencyField form-control" data-fieldinfo='{$FIELD_INFO}' tabindex="{$FIELD_MODEL->getTabIndex()}"
							data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
							title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
							{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}'
							data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}'
							{if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} />
					</span>
				</div>
			</div>
		{/if}
	</div>
	<!-- /tpl-Base-Edit-Field-Currency -->
{/strip}
