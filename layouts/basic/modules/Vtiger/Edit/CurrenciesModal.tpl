{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Edit-CurrenciesModal js-currencies-modal modal fade" tabindex="-1" data-js="container">
		<div class="modal-dialog modal-lg">
			<div class="modal-content" data-js="container">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="adminIcon-currencies mr-1"></span>
						{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
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
						{foreach item=ITEM key=CURRENCY_ID from=$PRICE_DETAILS}
							<tr data-currency-id="{$CURRENCY_ID}" data-currency-symbol="{$ITEM['symbol']}">
								<td class="align-middle text-nowrap">
									<span class="d-flex justify-content-between align-items-center">
										<span>
											<span class="js-currency-name" data-js="text">
												{\App\Purifier::encodeHtml($ITEM['currencyName'])}
												<span class="ml-1">({$ITEM['symbol']})</span>
											</span>
										</span>
										<span class="ml-1">
											<input type="checkbox" value="1"
												id="cur_{$CURRENCY_ID}_check"
												class="small float-right js-enable-currency"
												data-js="change">
										</span>
									</span>
								</td>
								<td class="align-middle">
									<div class="row justify-content-center">
										<input name="{$ITEM['name']}" type="text" value=""
											size="10" id="{$ITEM['name']}"
											class="col-md-9 js-format-numer js-converted-price form-control"
											data-validation-engine="validate[funcCall[Vtiger_Currency_Validator_Js.invokeValidation]]"
											data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($ITEM.fieldInfo))}"
											data-js="value" disabled="disabled" />
									</div>
								</td>
								<td class="align-middle">
									<div class="row justify-content-center">
										<input name="currencies[{$CURRENCY_ID}][rate]"
											value="{App\Fields\Double::formatToDisplay($ITEM['conversionRate'], false)}"
											class="col-md-9 js-conversion-rate form-control" type="text" size="10"
											readonly="readonly" disabled="disabled">
									</div>
								</td>
								<td class="align-middle">
									<div class="row justify-content-center">
										<button type="button" class="btn btn-light js-currency-reset resetButton"
											id="cur_reset{$CURRENCY_ID}"
											value="{\App\Language::translate('LBL_RESET',$MODULE_NAME)}"
											data-js="click">
											<span class="fas fa-undo mr-1"></span>
											{\App\Language::translate('LBL_RESET',$MODULE_NAME)}
										</button>
									</div>
								</td>
								<td class="align-middle">
									<div class="row justify-content-center">
										<input name="baseCurrencyRadio" value="{$ITEM['name']}"
											class="js-base-currency" type="radio" disabled="disabled"
											title="{\App\Language::translate('LBL_BASE_CURRENCY')}"
											data-js="checked" />
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
