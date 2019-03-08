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
	<div class="tpl-Base-Edit-Field-Currency">
		{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
		{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
		{assign var="FIELD_NAME" value=$FIELD_MODEL->getName()}
		{assign var="SYMBOL_PLACEMENT" value=$USER_MODEL->currency_symbol_placement}
		{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
		{if $FIELD_MODEL->getUIType() eq '71'}
			<div class="input-group" data-uitype="71">
				{if $SYMBOL_PLACEMENT neq '1.0$'}
					<span class="input-group-append"><span class="input-group-text js-currency"
														   data-js="text"> {$USER_MODEL->get('currency_symbol')}</span></span>
				{/if}
				<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text"
					   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
					   class="currencyField form-control {if $SYMBOL_PLACEMENT eq '1.0$'} textAlignRight {/if}"
					   name="{$FIELD_MODEL->getFieldName()}"
					   data-fieldinfo='{$FIELD_INFO}' value="{$FIELD_VALUE}"
					   {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if}
					   data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}'
					   data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}'
					   data-number-of-decimal-places='{$USER_MODEL->get('no_of_currency_decimals')}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{else} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if}/>
				{if $SYMBOL_PLACEMENT eq '1.0$'}
					<span class="input-group-append">
						<span class="input-group-text js-currency" data-js="text">
							{$USER_MODEL->get('currency_symbol')}
						</span>
					</span>
				{/if}
			</div>
		{elseif ($FIELD_MODEL->getUIType() eq '72') && ($FIELD_MODEL->getName() eq 'unit_price')}
			<div class="input-group" data-uitype="72|unit_price">
				{if $SYMBOL_PLACEMENT neq '1.0$'}
					<span class="input-group-append row">
						<span class="input-group-text js-currency" data-js="text">
							{$BASE_CURRENCY_SYMBOL}
						</span>
					</span>
				{/if}
				{assign var="DISPLAY_FIELD_VALUE" value=$FIELD_VALUE}
				<input name="{$FIELD_MODEL->getFieldName()}" id="{$MODULE}-editview-fieldname-{$FIELD_NAME}" type="text"
					   value="{$DISPLAY_FIELD_VALUE}"
					   class="col-md-12 unitPrice currencyField js-format-numer form-control {if $SYMBOL_PLACEMENT eq '1.0$'}textAlignRight{/if}"
					   data-fieldinfo='{$FIELD_INFO}'
					   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
					   {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if}
					   data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}'
					   data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}'
					   data-number-of-decimal-places='{$USER_MODEL->get('no_of_currency_decimals')}'
						{if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{else} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if}/>
				{if $SYMBOL_PLACEMENT eq '1.0$'}
					<span class="input-group-append row">
						<span class="input-group-text js-currency" data-js="text">
							{$BASE_CURRENCY_SYMBOL}
						</span>
					</span>
				{/if}
				{if $VIEW eq 'Edit'}
					<div class="input-group-append row">
						<div class="hide js-currencies-container" data-js="container">
							{include file=\App\Layout::getTemplatePath('Edit/Currencies.tpl', $MODULE_NAME)}
						</div>
						<button type="button" class="btn btn-light js-more-currencies js-popover-tooltip"
								data-content="{\App\Language::translate('LBL_MORE_CURRENCIES', $MODULE)}" data-js="click">
							<span class="adminIcon-currencies" title=""></span>
						</button>
					</div>
				{/if}
			</div>
			<input type="hidden" name="base_currency" value="{$BASE_CURRENCY_NAME}">
			<input type="hidden" name="cur_{$BASE_CURRENCY_ID}_check" class="js-base-currency-check-id" data-js="attr:name" value="1">
		{else}
			<div class="input-group">
				<div class="row">
					<span class="col-md-1 input-group-append">
						<span class="input-group-text row js-currency" data-js="text">
							{$USER_MODEL->get('currency_symbol')}
						</span>
					</span>
					{assign var="DISPLAY_FIELD_VALUE" value=$FIELD_VALUE}
					<span class="col-md-7">
						<input name="{$FIELD_MODEL->getFieldName()}" value="{$DISPLAY_FIELD_VALUE}" type="text"
							   class="row-fluid currencyField form-control" data-fieldinfo='{$FIELD_INFO}'
							   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
							   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
							   {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}'
							   data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}'
							   {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} /></span>
				</div>
			</div>
		{/if}
	</div>
{/strip}
