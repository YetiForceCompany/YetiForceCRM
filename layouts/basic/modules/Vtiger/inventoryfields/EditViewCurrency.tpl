{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewCurrency -->
	{assign var=CURRENCIES value=\App\Fields\Currency::getAll(true)}
	{assign var=SELECTED_CURRENCY value=$ITEM_VALUE}
	{if $SELECTED_CURRENCY eq ''}
		{assign var=USER_CURRENCY_ID value=$USER_MODEL->get('currency_id')}
		{foreach item=CURRENCY from=$CURRENCIES}
			{if $CURRENCY.id eq $USER_CURRENCY_ID}
				{assign var=SELECTED_CURRENCY value=$CURRENCY.id}
			{/if}
		{/foreach}
		{assign var=CURRENCY_PARAMS value=$FIELD->getCurrencyParam($CURRENCIES)}
	{else}
		{assign var=CURRENCY_PARAMS value=$FIELD->getCurrencyParam($CURRENCIES, $ITEM_DATA['currencyparam'])}
	{/if}
	<input {if $ROW_NO} name="inventory[{$ROW_NO}][currencyparam]" {/if} type="hidden" value="{\App\Purifier::encodeHtml(\App\Json::encode($CURRENCY_PARAMS))}"
		class="js-currencyparam" data-js="" />
	<select class="select2 js-currency" data-minimum-results-for-search="-1" data-old-value="{$SELECTED_CURRENCY}"
		{if $ROW_NO} name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" {/if}
		title="{\App\Language::translate('LBL_CURRENCY', $MODULE_NAME)}"
		{if $FIELD->isReadOnly()}readonly="readonly" {/if}>
		{foreach item=CURRENCY key=count from=$CURRENCIES}
			{assign var=CURRENCY_PARAM value=$CURRENCY_PARAMS[$CURRENCY.id]}
			<option value="{$CURRENCY.id}" class="textShadowNone" data-conversion-rate="{$CURRENCY_PARAM.conversion}"
				data-conversion-date="{$CURRENCY_PARAM.date}"
				data-conversion-symbol="{$CURRENCY.currency_symbol}"
				data-base-currency="{if $CURRENCY.defaultid < 0}1{else}0{/if}"
				{if $SELECTED_CURRENCY eq $CURRENCY.id}selected{/if}>
				{\App\Language::translate($CURRENCY.currency_code, 'Other.Currency')} ({$CURRENCY.currency_symbol})
			</option>
		{/foreach}
	</select>
	<div class="modelContainer modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="fas fa-euro-sign mr-1"></span>
						{\App\Language::translate('LBL_CHANGE_CURRENCY', $MODULE_NAME)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					{if $CURRENCY_PARAMS == false}
						<div class="alert alert-warning"
							role="alert">{\App\Language::translate('LBL_NO_EXCHANGE_RATES', $MODULE_NAME)}</div>
					{else}
						<div class="alert alert-warning"
							role="alert">{\App\Language::translate('LBL_CHANGE_CURRENCY_INFO', $MODULE_NAME)}</div>
						<div>{\App\Language::translate('Currency Name', $MODULE_NAME)}:
							<strong class="currencyName"></strong>
						</div>
						<div>{\App\Language::translate('LBL_EXCHANGE_DATE', $MODULE_NAME)}: <strong
								class="currencyDate"></strong></div>
						<div>
							<div class="input-group">
								<span class="input-group-prepend"><span
										class="input-group-text">{\App\Language::translate('LBL_EXCHANGE_RATE', $MODULE_NAME)}
										:</span></span>
								<input type="text" class="form-control currencyRate" value=""
									aria-label="{\App\Language::translate('LBL_EXCHANGE_RATE', $MODULE_NAME)}">
								<span class="input-group-append"><span
										class="input-group-text">{$BASE_CURRENCY['currency_symbol']}</span></span>
							</div>
						</div>
					{/if}
					<div class="modal-footer">
						{if $CURRENCY_PARAMS != false}
							<button class="btn btn-success" type="submit">
								<strong>
									<span class="fas fa-check mr-1"></span>
									{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}
								</strong>
							</button>
						{/if}
						<button class="btn btn-danger" type="reset" data-dismiss="modal">
							<strong>
								<span class="fas fa-times mr-1"></span>
								{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}
							</strong>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-inventoryfields-EditViewCurrency -->
{/strip}
