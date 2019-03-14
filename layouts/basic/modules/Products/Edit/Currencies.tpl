{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Products-Edit-Currencies js-currencies-modal modal fade" tabindex="-1" data-js="container">
		<div class="modal-dialog modal-lg">
			<div class="modal-content js-currencies-modal-content" data-js="container">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="adminIcon-currencies mr-1"></span>
						{\App\Language::translate('LBL_PRICES',$MODULE_NAME)}
					</h5>
					<button type="button" class="close" data-dismiss="modal"
							title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<table class="table table-bordered">
						<tr class="text-center">
							<td><strong>{\App\Language::translate('LBL_CURRENCY',$MODULE_NAME)}</strong></td>
							<td><strong>{\App\Language::translate('LBL_PRICE',$MODULE_NAME)}</strong></td>
							<td><strong>{\App\Language::translate('LBL_CONVERSION_RATE',$MODULE_NAME)}</strong></td>
							<td><strong>{\App\Language::translate('LBL_RESET_PRICE',$MODULE_NAME)}</strong></td>
							<td><strong>{\App\Language::translate('LBL_BASE_CURRENCY',$MODULE_NAME)}</strong></td>
						</tr>
						{foreach item=ITEM from=$PRICE_DETAILS}
							<tr data-currency-id="{$ITEM['curname']}" data-currency-symbol="{$ITEM['currency_symbol']}">
								{if $ITEM['check_value'] eq 1 || $ITEM['is_basecurrency'] eq 1}
									{assign var=CHECK_VALUE value=true}
									{assign var=DISABLE_VALUE value=false}
								{else}
									{assign var=CHECK_VALUE value=false}
									{assign var=DISABLE_VALUE value=true}
								{/if}
								<td class="align-middle text-nowrap">
									<span class="d-flex justify-content-between align-items-center">
										<span>
											<span class="js-currency-name" data-js="text">
												{\App\Language::translate($ITEM['currency_name'], 'Currency')}
												<span class="ml-1">({$ITEM['currency_symbol']})</span>
											</span>
										</span>
										<span class="ml-1">
											<input name="cur_{$ITEM['id']}_check" type="checkbox" value="1"
												   {if $CHECK_VALUE}checked="checked"{/if}
												   id="cur_{$ITEM['id']}_check"
												   class="small float-right js-enable-currency" {if $CHECK_VALUE}title="{\App\Language::translate('LBL_ENABLE_CURRENCY')}"{else}title="{\App\Language::translate('LBL_DISABLE_CURRENCY')}"{/if}
												   data-js="change">
										</span>
									</span>
								</td>
								<td class="align-middle">
									<div class="row justify-content-center">
										<input name="{$ITEM['curname']}" type="text" value="{$ITEM['curvalue']}"
											   size="10" id="{$ITEM['curname']}"
											   class="col-md-9 js-format-numer js-converted-price form-control {if $ITEM['is_basecurrency'] eq 1}js-base-curencies-value{/if}"
											   title="{$ITEM['curvalue']}" {if $DISABLE_VALUE}disabled="disabled"{/if}
											   data-validation-engine="validate[funcCall[Vtiger_Currency_Validator_Js.invokeValidation]]"
											   data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($ITEM.fieldInfo))}"
											   data-js="value"/>
									</div>
								</td>
								<td class="align-middle">
									<div class="row justify-content-center">
										<input name="cur_conv_rate{$ITEM['id']}"
											   value="{App\Fields\Currency::formatToDb($ITEM['conversionrate'])}"
											   class="col-md-9 js-conversion-rate form-control" type="text" size="10"
											   title="{App\Fields\Currency::formatToDb($ITEM['conversionrate'])}"
											   readonly="readonly">
									</div>
								</td>
								<td class="align-middle">
									<div class="row justify-content-center">
										<button type="button" class="btn btn-light js-currency-reset resetButton"
												id="cur_reset{$ITEM['id']}"
												{if $DISABLE_VALUE}disabled="disabled"{/if}
												value="{\App\Language::translate('LBL_RESET',$MODULE_NAME)}"
												data-js="click">
											<span class="fas fa-undo mr-1"></span>
											{\App\Language::translate('LBL_RESET',$MODULE_NAME)}
										</button>
									</div>
								</td>
								<td class="align-middle">
									<div class="row justify-content-center">
										<input name="base_currency_input" value="{$ITEM['curname']}"
											   class="js-base-currency" type="radio" id="base_currency{$ITEM['id']}"
											   {if $ITEM['is_basecurrency'] eq 1}checked="checked"{/if}
											   title="{\App\Language::translate('LBL_BASE_CURRENCY')}" {if $DISABLE_VALUE}disabled="disabled"{/if}
											   data-js="checked"/>
									</div>
								</td>
							</tr>
						{/foreach}
					</table>
				</div>
				{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE_NAME) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
			</div>
		</div>
	</div>
{/strip}
