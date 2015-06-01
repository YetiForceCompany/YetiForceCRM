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
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}

{if $FIELD_MODEL->get('uitype') eq '71'}
<div class="input-prepend">
	<span class="add-on">{$USER_MODEL->get('currency_symbol')}</span>
	<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" title="{vtranslate($FIELD_MODEL->get('fieldvalue'))}" class="input-medium currencyField" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL->getFieldName()}"
	data-fieldinfo='{$FIELD_INFO}' value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} 
	data-decimal-seperator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-seperator='{$USER_MODEL->get('currency_grouping_separator')}' data-number-of-decimal-places='{$USER_MODEL->get('no_of_currency_decimals')}' {if $FIELD_MODEL->get('displaytype') == 10}readonly="readonly"{/if}/>
</div>
{else if ($FIELD_MODEL->get('uitype') eq '72') && ($FIELD_MODEL->getName() eq 'unit_price')}
	<div class="input-prepend">
		<div class="row-fluid">
			<span class="span2">
				<span class="add-on row-fluid">{$BASE_CURRENCY_SYMBOL}</span>
			</span>
			<span class="span10 row-fluid">
				{assign var="DISPLAY_FIELD_VALUE" value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}
				<input id="{$MODULE}-editview-fieldname-{$FIELD_NAME}" type="text" class="span6 unitPrice currencyField" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			data-fieldinfo='{$FIELD_INFO}'  value="{$DISPLAY_FIELD_VALUE}" title="{$DISPLAY_FIELD_VALUE}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
			data-decimal-seperator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-seperator='{$USER_MODEL->get('currency_grouping_separator')}' data-number-of-decimal-places='{$USER_MODEL->get('no_of_currency_decimals')}'
			{if $FIELD_MODEL->get('displaytype') == 10}readonly="readonly"{/if}/>
				{if $smarty.request.view eq 'Edit'}
					<a id="moreCurrencies" class="span cursorPointer">{vtranslate('LBL_MORE_CURRENCIES', $MODULE)}>></a>
					<span id="moreCurrenciesContainer" class="hide"></span>
				{/if}
				<input type="hidden" name="base_currency" value="{$BASE_CURRENCY_NAME}">
				<input type="hidden" name="cur_{$BASE_CURRENCY_ID}_check" value="on">
				<input type="hidden" id="requstedUnitPrice" name="{$BASE_CURRENCY_NAME}" value="">
			</span>
		</div>
	</div>
{else}
<div class="input-prepend">
	<div class="row-fluid">
		<span class="span1"><span class="add-on row-fluid">{$USER_MODEL->get('currency_symbol')}</span></span>
		{assign var="DISPLAY_FIELD_VALUE" value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}
		<span class="span7"><input type="text" class="row-fluid currencyField" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
		data-fieldinfo='{$FIELD_INFO}' value="{$DISPLAY_FIELD_VALUE}" title="{$DISPLAY_FIELD_VALUE}" {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if} data-decimal-seperator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-seperator='{$USER_MODEL->get('currency_grouping_separator')}' {if $FIELD_MODEL->get('displaytype') == 10}readonly="readonly"{/if} /></span>
	</div>
</div>
{/if}
{* TODO - UI Type 72 needs to be handled. Multi-currency support also needs to be handled *}
{/strip}
