{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=CURRENCIES value=Vtiger_Functions::getAllCurrency(true)}
	{assign var=SELECTED_CURRENCY value=$ITEM_VALUE}
	{if $SELECTED_CURRENCY eq ''}
		{assign var=USER_CURRENCY_ID value=$USER_MODEL->get('currency_id')}
		{foreach item=CURRENCY from=$CURRENCIES}
			{if $CURRENCY.id eq $USER_CURRENCY_ID}
				{assign var=SELECTED_CURRENCY value=$CURRENCY.id}
			{/if}
		{/foreach}
	{/if}
	<select class="select2 supDataField" data-minimum-results-for-search="-1" data-old-value="{$SELECTED_CURRENCY}" name="{$FIELD->getColumnName()}{$ROW_NO}" title="{vtranslate('LBL_CURRENCY', $MODULE)}" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}>
		{foreach item=CURRENCY key=count from=$CURRENCIES}
			<option value="{$CURRENCY.id}" class="textShadowNone" data-conversion-rate="{$CURRENCY.conversion_rate}" 
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
					<div class="alert alert-warning" role="alert">{vtranslate('LBL_CHANGE_CURRENCY_INFO', $MODULE)}</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
					<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{vtranslate('LBL_CANCEL', $MODULE)}</strong></button>
				</div>
			</div>
		</div>
	</div>
{/strip}
