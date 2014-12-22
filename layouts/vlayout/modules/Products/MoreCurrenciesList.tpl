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
<div id="currency_class" class="multiCurrencyEditUI modelContainer">
<div class="modal-header">
	<button data-dismiss="modal" class="floatRight close" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
	<h3 id="massEditHeader">{vtranslate('LBL_PRICES',$MODULE)}</h3>
</div>
<div class="multiCurrencyContainer">
	<div class="currencyContent">
		<div class="modal-body">
			<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-bordered">
				<tr class="detailedViewHeader">
					<td><b>{vtranslate('LBL_CURRENCY',$MODULE)}</b></td>
					<td><b>{vtranslate('LBL_PRICE',$MODULE)}</b></td>
					<td><b>{vtranslate('LBL_CONVERSION_RATE',$MODULE)}</b></td>
					<td><b>{vtranslate('LBL_RESET_PRICE',$MODULE)}</b></td>
					<td><b>{vtranslate('LBL_BASE_CURRENCY',$MODULE)}</b></td>
				</tr>
				{foreach item=price key=count from=$PRICE_DETAILS}
					<tr data-currency-id={$price.curname}>
						{if $price.check_value eq 1 || $price.is_basecurrency eq 1}
							{assign var=check_value value="checked"}
							{assign var=disable_value value=""}
						{else}
							{assign var=check_value value=""}
							{assign var=disable_value value="disabled=true"}
						{/if}

						{if $price.is_basecurrency eq 1}
							{assign var=base_cur_check value="checked"}
						{else}
							{assign var=base_cur_check value=""}
						{/if}
						<td>
							<span class="row-fluid">
								<span class="span8 alignBottom">
									<span class="pull-left">{$price.currencylabel|@getTranslatedCurrencyString} ({$price.currencysymbol})</span>
								</span>
								<span class="span2"><input type="checkbox" name="cur_{$price.curid}_check" id="cur_{$price.curid}_check" class="small pull-right enableCurrency" {$check_value}></span>
							</span>
						</td>
						<td>
							<div class="row-fluid">
								<input {$disable_value} type="text" size="10" class="span9 convertedPrice" name="{$price.curname}" id="{$price.curname}" value="{$price.curvalue}" data-validation-engine="validate[funcCall[Vtiger_Currency_Validator_Js.invokeValidation]]" data-decimal-seperator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-seperator='{$USER_MODEL->get('currency_grouping_separator')}' />
							</div>
						</td>
						<td>
							<div class="row-fluid">
								<input readonly="" type="text" size="10" class="span9 conversionRate" name="cur_conv_rate{$price.curid}" value="{$price.conversionrate}">
							</div>
						</td>
						<td>
							<div class="row-fluid">
								<button {$disable_value} type="button" class="btn currencyReset resetButton" id="cur_reset{$price.curid}" value="{vtranslate('LBL_RESET',$MODULE)}">{vtranslate('LBL_RESET',$MODULE)}</button>
							</div>
						</td>
						<td>
							<div class="row-fluid textAlignCenter">
								<input {$disable_value} type="radio" class="baseCurrency" id="base_currency{$price.curid}" name="base_currency_input" value="{$price.curname}" {$base_cur_check} />
							</div>
						</td>
					</tr>
				{/foreach}
				</table>
			</div>
		</div>
	{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</div>
</div>
{/strip}