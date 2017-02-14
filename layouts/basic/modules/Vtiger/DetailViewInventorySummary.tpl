{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{foreach key=KEY item=INVENTORY_ROW from=$INVENTORY_ROWS}
		{assign var="DISCOUNT" value=$DISCOUNT + $INVENTORY_ROW['discount']}
		{assign var="TAXS" value=$INVENTORY_FIELD->getTaxParam($INVENTORY_ROW['taxparam'],$INVENTORY_ROW['net'], $TAXS)}
	{/foreach}
	<div class="row">
		{if in_array("discount",$COLUMNS) && in_array("discountmode",$COLUMNS)}
			<div class="col-md-4">
				<table class="table table-bordered inventorySummaryContainer">
					<thead>
						<tr>
							<th>
								<img src="{vimage_path('Discount24.png')}" alt="{vtranslate('LBL_DISCOUNT', $MODULE_NAME)}" />&nbsp;&nbsp;
								<strong>{vtranslate('LBL_DISCOUNTS_SUMMARY',$MODULE_NAME)}</strong>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="textAlignRight">
								{CurrencyField::convertToUserFormatSymbol($DISCOUNT,false,$CURRENCY_SYMBOLAND['symbol'],true)}
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		{/if}
		{if in_array("tax",$COLUMNS) && in_array("taxmode",$COLUMNS)}
			<div class="col-md-4">
				<table class="table table-bordered inventorySummaryContainer">
					<thead>
						<tr>
							<th colspan="2">
								<img src="{vimage_path('Tax24.png')}" alt="{vtranslate('LBL_TAX', $MODULE_NAME)}" />&nbsp;&nbsp;
								<strong>{vtranslate('LBL_TAX_SUMMARY',$MODULE_NAME)}</strong>
							</th>
						</tr>
					</thead>
					<tbody>
						{foreach item=TAX key=KEY from=$TAXS}
							{assign var="TAX_AMOUNT" value=$TAX_AMOUNT + $TAX}
							<tr>
								<td class="textAlignRight" width='70px'>
									{$KEY}%
								</td>
								<td class="textAlignRight">
									{CurrencyField::convertToUserFormatSymbol($TAX,false,$CURRENCY_SYMBOLAND['symbol'])}
								</td>
							</tr>
						{/foreach}
						<tr>
							<td class="textAlignRight" width='70px'>
								{vtranslate('LBL_AMOUNT',$MODULE_NAME)}
							</td>
							<td class="textAlignRight">
								{CurrencyField::convertToUserFormatSymbol($TAX_AMOUNT,false,$CURRENCY_SYMBOLAND['symbol'])}
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			{if in_array("currency",$COLUMNS) && $BASE_CURRENCY['id'] != $CURRENCY}
				{assign var="CURRENCY_PARAM" value=$INVENTORY_ROWS[0]['currencyparam']|json_decode:true}
				{assign var="RATE" value=$CURRENCY_PARAM[$CURRENCY]['value']}
				<div class="col-md-4">
					<table class="table table-bordered inventorySummaryContainer">
						<thead>
							<tr>
								<th colspan="2">
									<strong>{vtranslate('LBL_CURRENCIES_SUMMARY',$MODULE_NAME)}</strong>
								</th>
							</tr>
						</thead>
						<tbody>
							{foreach item=TAX key=KEY from=$TAXS}
								{assign var="CURRENY_AMOUNT" value=$CURRENY_AMOUNT + $TAX}
								<tr>
									<td class="textAlignRight" width='70px'>
										{$KEY}%
									</td>
									<td class="textAlignRight">
										{CurrencyField::convertToUserFormatSymbol($TAX * $RATE,false,$BASE_CURRENCY['currency_symbol'],true)}
									</td>
								</tr>
							{/foreach}
							<tr>
								<td class="textAlignRight" width='70px'>
									{vtranslate('LBL_AMOUNT',$MODULE_NAME)}
								</td>
								<td class="textAlignRight">
									{CurrencyField::convertToUserFormatSymbol($CURRENY_AMOUNT * $RATE,false,$BASE_CURRENCY['currency_symbol'],true)}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			{/if}
		{/if}
	</div>
{/strip}
