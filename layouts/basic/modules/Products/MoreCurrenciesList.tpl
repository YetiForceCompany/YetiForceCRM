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
	<div id="currency_class" class="multiCurrencyEditUI modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="massEditHeader" class="modal-title">{\App\Language::translate('LBL_PRICES',$MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="multiCurrencyContainer">
					<div class="currencyContent">
						<div class="modal-body">
							<table class="table table-bordered">
								<tr class="text-center">
									<td><strong>{\App\Language::translate('LBL_CURRENCY',$MODULE)}</strong></td>
									<td><strong>{\App\Language::translate('LBL_PRICE',$MODULE)}</strong></td>
									<td><strong>{\App\Language::translate('LBL_CONVERSION_RATE',$MODULE)}</strong></td>
									<td><strong>{\App\Language::translate('LBL_RESET_PRICE',$MODULE)}</strong></td>
									<td><strong>{\App\Language::translate('LBL_BASE_CURRENCY',$MODULE)}</strong></td>
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
										<td class="align-middle text-nowrap">
											<span class="d-flex justify-content-between align-items-center">
												<span>
													<span class="currencyName">{\App\Language::translate($price.currencylabel, 'Currency')} ({$price.currencysymbol})</span>
												</span>
												<span class="ml-1">
													<input type="checkbox" name="cur_{$price.curid}_check" id="cur_{$price.curid}_check" class="small float-right enableCurrency" {if $check_value} title="{\App\Language::translate('LBL_ENABLE_CURRENCY')}" {else} title="{\App\Language::translate('LBL_DISABLE_CURRENCY')}" {/if} {$check_value}>
												</span>
											</span>
										</td>
										<td class="align-middle">
											<div class="row justify-content-center">
												<input {$disable_value} type="text" size="10" class="col-md-9 convertedPrice form-control" name="{$price.curname}" id="{$price.curname}" value="{$price.curvalue}" title="{$price.curvalue}" data-validation-engine="validate[funcCall[Vtiger_Currency_Validator_Js.invokeValidation]]" data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' />
											</div>
										</td>
										<td class="align-middle">
											<div class="row justify-content-center">
												<input readonly="" type="text" size="10" class="col-md-9 conversionRate form-control" name="cur_conv_rate{$price.curid}" title="{$price.conversionrate}" value="{$price.conversionrate}">
											</div>
										</td>
										<td class="align-middle">
											<div class="row justify-content-center">
												<button {$disable_value} type="button" class="btn btn-light currencyReset resetButton" id="cur_reset{$price.curid}" value="{\App\Language::translate('LBL_RESET',$MODULE)}">{\App\Language::translate('LBL_RESET',$MODULE)}</button>
											</div>
										</td>
										<td class="align-middle">
											<div class="row justify-content-center">
												<input {$disable_value} type="radio" class="baseCurrency" id="base_currency{$price.curid}" name="base_currency_input" title="{\App\Language::translate('LBL_BASE_CURRENCY')}" value="{$price.curname}" {$base_cur_check} />
											</div>
										</td>
									</tr>
								{/foreach}
							</table>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</div>
			</div>
		</div>
	</div>
{/strip}
