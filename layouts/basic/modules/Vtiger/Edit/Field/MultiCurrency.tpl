{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-MultiCurrency -->
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	{assign var="MODULE_NAME" value=$FIELD_MODEL->getModuleName()}
	{assign var="SYMBOL_PLACEMENT_ON_RIGHT" value=$USER_MODEL->get('currency_symbol_placement') eq '1.0$'}
	{assign var="CURRENCY_SYMBOL" value=$USER_MODEL->get('currency_symbol')}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getUITypeModel()->getEditViewFormatData($FIELD_MODEL->get('fieldvalue'))}
	{assign var=TABINDEX value=$FIELD_MODEL->getTabIndex()}
	{if isset($FIELD_VALUE['currencyId'])}
		{assign var="CURRENCY" value=\App\Fields\Currency::getById($FIELD_VALUE['currencyId'])}
		{assign var="CURRENCY_SYMBOL" value=$CURRENCY['currency_symbol']}
	{/if}
	{function FUNC_CURRENCY_SYMBOL_PLACEMENT CURRENCY_SYMBOL=''}
		<span class="input-group-append">
			<span class="input-group-text js-currency" data-js="text">
				{$CURRENCY_SYMBOL}
			</span>
		</span>
	{/function}
	<div class="js-multicurrency-container">
		<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_VALUE))}" class="js-multicurrency-field-to-save"
			{if $FIELD_MODEL->isEditableReadOnly()} disabled="disabled" {/if} tabindex="{$TABINDEX}">
		<div class="input-group {$WIDTHTYPE_GROUP}">
			{if !$SYMBOL_PLACEMENT_ON_RIGHT}
				{FUNC_CURRENCY_SYMBOL_PLACEMENT CURRENCY_SYMBOL=$CURRENCY_SYMBOL}
			{/if}
			<input id="{$MODULE_NAME}-editview-fieldname-{$FIELD_NAME}" type="text" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}"
				class="col-md-12 js-multicurrency-field js-format-numer form-control{if $SYMBOL_PLACEMENT_ON_RIGHT} textAlignRight{/if}"
				title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}" tabindex="{$TABINDEX}" data-fieldinfo="{$FIELD_INFO}"
				{if $FIELD_MODEL->isEditableReadOnly()} disabled="disabled" {else}data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if} {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}>
			{if $SYMBOL_PLACEMENT_ON_RIGHT}
				{FUNC_CURRENCY_SYMBOL_PLACEMENT CURRENCY_SYMBOL=$CURRENCY_SYMBOL}
			{/if}
			{if !$FIELD_MODEL->isEditableReadOnly()}
				<div class="input-group-append">
					<div class="hide js-currencies-container" data-js="container">
						{include file=\App\Layout::getTemplatePath('Edit/CurrenciesModal.tpl', $MODULE_NAME) PRICE_DETAILS=$FIELD_MODEL->getUITypeModel()->getCurrencies()}
					</div>
					<button type="button" class="btn btn-light js-multicurrency-event js-popover-tooltip" tabindex="{$TABINDEX}" data-content="{\App\Language::translate('LBL_MORE_CURRENCIES', $MODULE_NAME)}" data-js="click">
						<span class="adminIcon-currencies" title=""></span>
					</button>
				</div>
			{/if}
		</div>
	</div>
	<!-- /tpl-Base-Edit-Field-MultiCurrency -->
{/strip}
