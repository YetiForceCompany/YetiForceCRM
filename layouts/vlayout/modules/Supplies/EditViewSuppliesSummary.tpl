{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="row">
		<div class="col-md-4">
			<div class="panel panel-default suppliesSummaryContainer suppliesSummaryDiscounts">
				<div class="panel-heading">
					<img src="{vimage_path('Discount24.png')}" alt="{vtranslate('LBL_DISCOUNT', $SUPMODULE)}" />&nbsp;&nbsp;
					<strong>{vtranslate('LBL_DISCOUNTS_SUMMARY',$SUPMODULE)}</strong>
					<span class="pull-right groupDiscount changeDiscount {if isset($SUP_RECORD_DATA[0]) && $SUP_RECORD_DATA[0]['discountmode'] == '1'}hide{/if}">
						<button type="button" class="btn btn-primary btn-xs">{vtranslate('LBL_SET_GLOBAL_TAX', $SUPMODULE)}</button>
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
		<div class="col-md-4">
			<div class="panel panel-default suppliesSummaryContainer suppliesSummaryTaxes">
				<div class="panel-heading">
					<img src="{vimage_path('Tax24.png')}" alt="{vtranslate('LBL_TAX', $SUPMODULE)}" />&nbsp;&nbsp;
					<strong>{vtranslate('LBL_TAX_SUMMARY',$SUPMODULE)}</strong>
					<span class="pull-right groupTax changeTax {if isset($SUP_RECORD_DATA[0]) && $SUP_RECORD_DATA[0]['taxmode'] == '1'}hide{/if}">
						<button type="button" class="btn btn-primary btn-xs">{vtranslate('LBL_SET_GLOBAL_DISCOUNT', $SUPMODULE)}</button>
					</span>
				</div>
				<div class="panel-body"></div>
				<div class="panel-footer">
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon percent">{vtranslate('LBL_AMOUNT', $SUPMODULE)}</div>
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
			<div class="panel panel-default suppliesSummaryContainer suppliesSummaryCurrencies">
				<div class="panel-heading">
					<strong>{vtranslate('LBL_CURRENCIES_SUMMARY',$SUPMODULE)}</strong>
				</div>
				<div class="panel-body"></div>
				<div class="panel-footer">
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon percent">{vtranslate('LBL_AMOUNT', $SUPMODULE)}</div>
							<input type="text" class="form-control textAlignRight" readonly="readonly">
							{if in_array("currency",$COLUMNS)}
								<div class="input-group-addon">{$CURRENCY_SYMBOLAND['symbol']}</div>
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
								<div class="input-group-addon">{$CURRENCY_SYMBOLAND['symbol']}</div>
							{/if}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
