{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=CURRENCIES value=vtlib\Functions::getAllCurrency(true)}
	{assign var=SELECTED_CURRENCY value=$ITEM_VALUE}
	{assign var=FIELD_PARAMS value=\App\Json::decode($FIELD->get('params'))}

	{if $SELECTED_CURRENCY eq ''}
		{assign var=USER_CURRENCY_ID value=$USER_MODEL->get('currency_id')}
		{foreach item=CURRENCY from=$CURRENCIES}
			{if $CURRENCY.id eq $USER_CURRENCY_ID}
				{assign var=SELECTED_CURRENCY value=$CURRENCY.id}
			{/if}
		{/foreach}
		{assign var=CURRENCY_PARAMS value=$FIELD->getCurrencyParam($CURRENCIES)}
	{else}
		{assign var=CURRENCY_PARAMS value=$FIELD->getCurrencyParam($CURRENCIES, $INVENTORY_ROWS[0]['currencyparam'])}
	{/if}

	<input name="currencyparam" type="hidden" value="{\App\Purifier::encodeHtml(\App\Json::encode($CURRENCY_PARAMS))}" class="currencyparam" />
	<select class="select2" data-minimum-results-for-search="-1" data-old-value="{$SELECTED_CURRENCY}" name="{$FIELD->getColumnName()}" 
			title="{\App\Language::translate('LBL_CURRENCY', $MODULE)}" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}>
		{foreach item=CURRENCY key=count from=$CURRENCIES}
			{assign var=CURRENCY_PARAM value=$CURRENCY_PARAMS[$CURRENCY.id]}
			<option value="{$CURRENCY.id}" class="textShadowNone" data-conversion-rate="{$CURRENCY_PARAM.conversion}" data-conversion-date="{$CURRENCY_PARAM.date}" 
					data-conversion-symbol="{$CURRENCY.currency_symbol}" data-base-currency="{if $CURRENCY.defaultid < 0}1{else}0{/if}" 
					{if $SELECTED_CURRENCY eq $CURRENCY.id}selected{/if}>
				{\App\Language::translate($CURRENCY.currency_name, $MODULE)} ({$CURRENCY.currency_symbol})
			</option>
		{/foreach}
	</select>
	<div class="modelContainer modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<h5 class="modal-title">
						<span class="fas fa-euro-sign mr-1"></span>
						{\App\Language::translate('LBL_CHANGE_CURRENCY', $MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					{if $CURRENCY_PARAMS == false}
						<div class="alert alert-warning" role="alert">{\App\Language::translate('LBL_NO_EXCHANGE_RATES', $MODULE)}</div>
					{else}	
						<div class="alert alert-warning" role="alert">{\App\Language::translate('LBL_CHANGE_CURRENCY_INFO', $MODULE)}</div>
						<div>{\App\Language::translate('Currency Name', $MODULE)}: <strong class="currencyName"></strong></div>
						<div>{\App\Language::translate('LBL_EXCHANGE_DATE', $MODULE)}: <strong class="currencyDate"></strong></div>
						<div>
							<div class="input-group">
								<span class="input-group-prepend"><span class="input-group-text">{\App\Language::translate('LBL_EXCHANGE_RATE', $MODULE)}:</span></span>
								<input type="text" class="form-control currencyRate" value="" aria-label="{\App\Language::translate('LBL_EXCHANGE_RATE', $MODULE)}" 
									   {if $FIELD_PARAMS['type'] eq '1'}readonly="readonly"{/if}>
								<span class="input-group-append"><span class="input-group-text">{$BASE_CURRENCY['currency_symbol']}</span></span>
							</div>
						</div>
					{/if}
					<div class="modal-footer">
						{if $CURRENCY_PARAMS != false}
							<button class="btn btn-success" type="submit">
								<strong>
									<span class="fas fa-check mr-1"></span>
									{\App\Language::translate('LBL_SAVE', $MODULE)}
								</strong>
							</button>
						{/if}
						<button class="btn btn-danger" type="reset" data-dismiss="modal">
							<strong>
								<span class="fas fa-times mr-1"></span>
								{\App\Language::translate('LBL_CANCEL', $MODULE)}
							</strong>
						</button>
					</div>
				</div>
			</div>
		</div>
	{/strip}
