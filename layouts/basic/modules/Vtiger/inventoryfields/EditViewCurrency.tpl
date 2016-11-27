{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
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

	<input name="currencyparam" type="hidden" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($CURRENCY_PARAMS))}" class="currencyparam" />
	<select class="select2" data-minimum-results-for-search="-1" data-old-value="{$SELECTED_CURRENCY}" name="{$FIELD->getColumnName()}" 
			title="{vtranslate('LBL_CURRENCY', $MODULE)}" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}>
		{foreach item=CURRENCY key=count from=$CURRENCIES}
			{assign var=CURRENCY_PARAM value=$CURRENCY_PARAMS[$CURRENCY.id]}
			<option value="{$CURRENCY.id}" class="textShadowNone" data-conversion-rate="{$CURRENCY_PARAM.conversion}" data-conversion-date="{$CURRENCY_PARAM.date}" 
					data-conversion-symbol="{$CURRENCY.currency_symbol}" data-base-currency="{if $CURRENCY.defaultid < 0}1{else}0{/if}" 
					{if $SELECTED_CURRENCY eq $CURRENCY.id}selected{/if}>
				{vtranslate($CURRENCY.currency_name, $MODULE)} ({$CURRENCY.currency_symbol})
			</option>
		{/foreach}
	</select>
	<div class="modelContainer modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<h3 class="modal-title">{vtranslate('LBL_CHANGE_CURRENCY', $MODULE)}</h3>
				</div>
				<div class="modal-body">
					{if $CURRENCY_PARAMS == false}
						<div class="alert alert-warning" role="alert">{vtranslate('LBL_NO_EXCHANGE_RATES', $MODULE)}</div>
					{else}	
						<div class="alert alert-warning" role="alert">{vtranslate('LBL_CHANGE_CURRENCY_INFO', $MODULE)}</div>
						<div>{vtranslate('Currency Name', $MODULE)}: <strong class="currencyName"></strong></div>
						<div>{vtranslate('LBL_EXCHANGE_DATE', $MODULE)}: <strong class="currencyDate"></strong></div>
						<div>
							<div class="input-group">
								<span class="input-group-addon">{vtranslate('LBL_EXCHANGE_RATE', $MODULE)}:</span>
								<input type="text" class="form-control currencyRate" value="" aria-label="{vtranslate('LBL_EXCHANGE_RATE', $MODULE)}" 
									   {if $FIELD_PARAMS['type'] eq '1'}readonly="readonly"{/if}>
								<span class="input-group-addon">{$BASE_CURRENCY['currency_symbol']}</span>
							</div>
						</div>
					{/if}
					<div class="modal-footer">
						{if $CURRENCY_PARAMS != false}
							<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
								{/if}
						<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{vtranslate('LBL_CANCEL', $MODULE)}</strong></button>
					</div>
				</div>
			</div>
		</div>
	{/strip}
