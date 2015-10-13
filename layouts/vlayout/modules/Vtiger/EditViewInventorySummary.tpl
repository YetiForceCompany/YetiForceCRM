{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="row">
		{if in_array("discount",$COLUMNS) && in_array("discountmode",$COLUMNS)}
			<div class="col-md-4">
				<div class="panel panel-default inventorySummaryContainer inventorySummaryDiscounts">
					<div class="panel-heading">
						<img src="{vimage_path('Discount24.png')}" alt="{vtranslate('LBL_DISCOUNT', $MODULE)}" />&nbsp;&nbsp;
						<strong>{vtranslate('LBL_DISCOUNTS_SUMMARY',$MODULE)}</strong>
						<span class="pull-right groupDiscount changeDiscount {if isset($INVENTORY_ROWS[0]) && $INVENTORY_ROWS[0]['discountmode'] == '1'}hide{/if}">
							<button type="button" class="btn btn-primary btn-xs">{vtranslate('LBL_SET_GLOBAL_TAX', $MODULE)}</button>
						</span>
					</div>
					<div class="panel-body">
						<div class="form-group">
							<div class="input-group">
								<input type="text" class="form-control textAlignRight" readonly="readonly">
								{if in_array("currency",$COLUMNS)}
									<div class="input-group-addon currencySymbol">{$CURRENCY_SYMBOLAND['symbol']}</div>
								{/if}
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
		{if in_array("tax",$COLUMNS) && in_array("taxmode",$COLUMNS)}
			<div class="col-md-4">
				<div class="panel panel-default inventorySummaryContainer inventorySummaryTaxes">
					<div class="panel-heading">
						<img src="{vimage_path('Tax24.png')}" alt="{vtranslate('LBL_TAX', $MODULE)}" />&nbsp;&nbsp;
						<strong>{vtranslate('LBL_TAX_SUMMARY',$MODULE)}</strong>
						<span class="pull-right groupTax changeTax {if isset($INVENTORY_ROWS[0]) && $INVENTORY_ROWS[0]['taxmode'] == '1'}hide{/if}">
							<button type="button" class="btn btn-primary btn-xs">{vtranslate('LBL_SET_GLOBAL_DISCOUNT', $MODULE)}</button>
						</span>
					</div>
					<div class="panel-body"></div>
					<div class="panel-footer">
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon percent">{vtranslate('LBL_AMOUNT', $MODULE)}</div>
								<input type="text" class="form-control textAlignRight" readonly="readonly">
								{if in_array("currency",$COLUMNS)}
									<div class="input-group-addon currencySymbol">{$CURRENCY_SYMBOLAND['symbol']}</div>
								{/if}
							</div>
						</div>
					</div>
					<div class="hide">
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon percent"></div>
								<input type="text" class="form-control textAlignRight" readonly="readonly">
								{if in_array("currency",$COLUMNS)}
									<div class="input-group-addon currencySymbol">{$CURRENCY_SYMBOLAND['symbol']}</div>
								{/if}
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="panel panel-default inventorySummaryContainer inventorySummaryCurrencies">
					<div class="panel-heading">
						<strong>{vtranslate('LBL_CURRENCIES_SUMMARY',$MODULE)}</strong>
					</div>
					<div class="panel-body"></div>
					<div class="panel-footer">
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon percent">{vtranslate('LBL_AMOUNT', $MODULE)}</div>
								<input type="text" class="form-control textAlignRight" readonly="readonly">
								{if in_array("currency",$COLUMNS)}
									<div class="input-group-addon">{$BASE_CURRENCY['currency_symbol']}</div>
								{/if}
							</div>
						</div>
					</div>
					<div class="hide">
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon percent"></div>
								<input type="text" class="form-control textAlignRight" readonly="readonly">
								{if in_array("currency",$COLUMNS)}
									<div class="input-group-addon">{$BASE_CURRENCY['currency_symbol']}</div>
								{/if}
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
	</div>
{/strip}
