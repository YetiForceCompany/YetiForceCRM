{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="row">
		{if in_array("discount",$COLUMNS) && in_array("discountmode",$COLUMNS)}
			<div class="col-md-4">
				<div class="card mb-3 mb-md-0 inventorySummaryContainer inventorySummaryDiscounts">
					<div class="card-header">
						<div class="form-row">
							<div class="col-12 col-lg-9 mb-1 p-0 u-text-ellipsis">
								 <span class="fa-layers fa-fw mr-2 ">
									 <i class="fas fa-circle" data-fa-transform="grow-6"></i>
									 <i class="fa-inverse fas fa-long-arrow-alt-down text-white" data-fa-transform="shrink-6 left-4"></i>
									 <i class="fa-inverse fas fa-percent text-white" data-fa-transform="shrink-8 right-3"></i>
								 </span>
								<strong>{\App\Language::translate('LBL_DISCOUNTS_SUMMARY',$MODULE)}</strong>
							</div>
							<div class="col-12 col-lg-3 p-0 groupDiscount changeDiscount  {if isset($INVENTORY_ROWS[0]) && $INVENTORY_ROWS[0]['discountmode'] == '1'}d-none{/if}">
								<button type="button" class="btn btn-primary btn-sm c-btn-block-md-down float-right">{\App\Language::translate('LBL_SET_GLOBAL_DISCOUNT', $MODULE)}</button>
							</div>
						</div>
					</div>
					<div class="card-body js-panel__body m-0 p-0" data-js=”value”>
						<div class="form-group p-1 m-0">
							<div class="input-group">
								<input type="text" class="form-control text-right" readonly="readonly"/>
								<div class="input-group-append">
									{if in_array("currency",$COLUMNS)}
										<div class="input-group-text currencySymbol">{$CURRENCY_SYMBOLAND['symbol']}</div>
									{/if}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
		{if in_array("tax",$COLUMNS) && in_array("taxmode",$COLUMNS)}
			<div class="col-md-4">
				<div class="card mb-3 mb-md-0 inventorySummaryContainer inventorySummaryTaxes">
					<div class="card-header">
						<div class="form-row">
							<div class="col-12 col-lg-9 mb-1 p-0 u-text-ellipsis">
								 <span class="fa-layers fa-fw mr-2">
									 <i class="fas fa-circle" data-fa-transform="grow-6"></i>
									 <i class="fa-inverse fas fa-long-arrow-alt-up text-white" data-fa-transform="shrink-6 left-4"></i>
									 <i class="fa-inverse fas fa-percent text-white" data-fa-transform="shrink-8 right-3"></i>
								 </span>
								<strong>{\App\Language::translate('LBL_TAX_SUMMARY',$MODULE)}</strong>
							</div>
							<div class="col-12 col-lg-3 p-0 groupTax changeTax {if isset($INVENTORY_ROWS[0]) && $INVENTORY_ROWS[0]['taxmode'] == '1'}d-none{/if}">
								<button type="button" class="btn btn-primary btn-sm float-right c-btn-block-md-down">{\App\Language::translate('LBL_SET_GLOBAL_TAX', $MODULE)}</button>
							</div>
						</div>
					</div>
					<div class="card-body js-panel__body p-0 m-0 js-default-tax" data-js="data-tax-default-value|value" data-tax-default-value="{$TAX_DEFAULT['value']}"></div>
					<div class="card-footer js-panel__footer p-1 m-0 " data-js=”value”>
						<div class="form-group m-0 p-0">
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text percent u-w-85px d-flex justify-content-center">{\App\Language::translate('LBL_AMOUNT', $MODULE)}</div>
								</div>
								<input type="text" class="form-control text-right" readonly="readonly"/>
								<div class="input-group-append">
									{if in_array("currency",$COLUMNS)}
										<div class="input-group-text currencySymbol">{$CURRENCY_SYMBOLAND['symbol']}</div>
									{/if}
								</div>
							</div>
						</div>
					</div>
					<div class="d-none">
						<div class="form-group m-1">
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text percent u-w-85px d-flex justify-content-center"></div>
								</div>
								<input type="text" class="form-control text-right" readonly="readonly"/>
								<div class="input-group-append">
									{if in_array("currency",$COLUMNS)}
										<div class="input-group-text currencySymbol">{$CURRENCY_SYMBOLAND['symbol']}</div>
									{/if}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card mb-3 mb-md-0 inventorySummaryContainer inventorySummaryCurrencies">
					<div class="card-header u-text-ellipsis">
						<span class="fa-layers fa-fw mr-2">
							<i class="fas fa-circle" data-fa-transform="grow-6"></i>
							<i class="fa-inverse fas fa-dollar-sign text-white" data-fa-transform="shrink-4"></i>
						</span>
						<strong>{\App\Language::translate('LBL_CURRENCIES_SUMMARY',$MODULE)}</strong>
					</div>
					<div class="card-body js-panel__body p-0 m-0"></div>
					<div class="card-footer js-panel__footer p-1" data-js=”value”>
						<div class="form-group m-0 p-0">
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text percent u-w-85px d-flex justify-content-center">{\App\Language::translate('LBL_AMOUNT', $MODULE)}</div>
								</div>
								<input type="text" class="form-control text-right" readonly="readonly"/>
								<div class="input-group-append">
									{if in_array("currency",$COLUMNS)}
										<div class="input-group-text">{$BASE_CURRENCY['currency_symbol']}</div>
									{/if}
								</div>
							</div>
						</div>
					</div>
					<div class="d-none">
						<div class="form-group m-1">
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text percent u-w-85px d-flex justify-content-center">

									</div>
								</div>
								<input type="text" class="form-control text-right" readonly="readonly"/>
								<div class="input-group-append">
									{if in_array("currency",$COLUMNS)}
										<div class="input-group-text">{$BASE_CURRENCY['currency_symbol']}</div>
									{/if}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
	</div>
{/strip}
