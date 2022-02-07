{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-InventorySummary -->
	<div class="row">
		{if isset($FIELDS[1]['discount']) && isset($FIELDS[0]['discountmode'])}
			{assign var="DISCOUNT" value=$INVENTORY_MODEL->getField('discount')->getSummaryValuesFromData($INVENTORY_ROWS)}
			<div class="col-md-4">
				<table class="table table-bordered inventorySummaryContainer">
					<thead>
						<tr>
							<th>
								<img src="{\App\Layout::getImagePath('Discount24.png')}"
									alt="{\App\Language::translate('LBL_DISCOUNT', $MODULE_NAME)}" />&nbsp;&nbsp;
								<strong>{\App\Language::translate('LBL_DISCOUNTS_SUMMARY',$MODULE_NAME)}</strong>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="textAlignRight">
								{CurrencyField::convertToUserFormatSymbol($DISCOUNT, true, $CURRENCY_SYMBOLAND['currency_symbol'])}
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		{/if}
		{if isset($FIELDS[1]['tax']) && isset($FIELDS[0]['taxmode']) && $INVENTORY_MODEL->isField('net')}
			{assign var=TAX_FIELD value=$FIELDS[1]['tax']}
			{foreach key=KEY item=INVENTORY_ROW from=$INVENTORY_ROWS}
				{if isset($TAXS) && isset($INVENTORY_ROW['taxparam']) }
					{assign var="TAXS" value=$TAX_FIELD->getTaxParam($INVENTORY_ROW['taxparam'], $INVENTORY_ROW['net'], $TAXS)}
				{elseif isset($INVENTORY_ROW['taxparam']) }
					{assign var="TAXS" value=$TAX_FIELD->getTaxParam($INVENTORY_ROW['taxparam'], $INVENTORY_ROW['net'], [])}
				{else}
					{assign var="TAXS" value=[]}
				{/if}
			{/foreach}
			<div class="col-md-4">
				<table class="table table-bordered inventorySummaryContainer">
					<thead>
						<tr>
							<th colspan="2">
								<img src="{\App\Layout::getImagePath('Tax24.png')}"
									alt="{\App\Language::translate('LBL_TAX', $MODULE_NAME)}" />&nbsp;&nbsp;
								<strong>{\App\Language::translate('LBL_TAX_SUMMARY',$MODULE_NAME)}</strong>
							</th>
						</tr>
					</thead>
					<tbody>
						{assign var="TAX_AMOUNT" value=0}
						{foreach item=TAX key=KEY from=$TAXS}
							{assign var="TAX_AMOUNT" value=$TAX_AMOUNT + $TAX}
							<tr>
								<td class="textAlignRight" width='70px'>
									{App\Fields\Double::formatToDisplay($KEY)}%
								</td>
								<td class="textAlignRight">
									{CurrencyField::convertToUserFormatSymbol($TAX, true, $CURRENCY_SYMBOLAND['currency_symbol'])}
								</td>
							</tr>
						{/foreach}
						<tr>
							<td class="textAlignRight" width='70px'>
								{\App\Language::translate('LBL_AMOUNT',$MODULE_NAME)}
							</td>
							<td class="textAlignRight">
								{CurrencyField::convertToUserFormatSymbol($TAX_AMOUNT, true, $CURRENCY_SYMBOLAND['currency_symbol'])}
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			{if isset($FIELDS[0]['currency']) && $BASE_CURRENCY['id'] != $CURRENCY}
				{assign var="CURRENCY_PARAM" value=$INVENTORY_ROW['currencyparam']|json_decode:true}
				{assign var="RATE" value=$CURRENCY_PARAM[$CURRENCY]['value']}
				<div class="col-md-4">
					<table class="table table-bordered inventorySummaryContainer">
						<thead>
							<tr>
								<th colspan="2">
									<strong>{\App\Language::translate('LBL_CURRENCIES_SUMMARY',$MODULE_NAME)}</strong>
								</th>
							</tr>
						</thead>
						<tbody>
							{assign var="CURRENY_AMOUNT" value=0}
							{foreach item=TAX key=KEY from=$TAXS}
								{assign var="CURRENY_AMOUNT" value=$CURRENY_AMOUNT + $TAX}
								<tr>
									<td class="textAlignRight" width='70px'>
										{CurrencyField::convertToUserFormat($KEY)}%
									</td>
									<td class="textAlignRight">
										{CurrencyField::convertToUserFormatSymbol($TAX * $RATE, true, $BASE_CURRENCY['currency_symbol'])}
									</td>
								</tr>
							{/foreach}
							<tr>
								<td class="textAlignRight" width='70px'>
									{\App\Language::translate('LBL_AMOUNT',$MODULE_NAME)}
								</td>
								<td class="textAlignRight">
									{CurrencyField::convertToUserFormatSymbol($CURRENY_AMOUNT * $RATE, true, $BASE_CURRENCY['currency_symbol'])}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			{/if}
		{/if}
	</div>
	<!-- /tpl-Base-Detail-InventorySummary -->
{/strip}
