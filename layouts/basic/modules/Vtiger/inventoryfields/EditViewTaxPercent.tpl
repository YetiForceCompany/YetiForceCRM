{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewTaxPecent -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<div class="input-group input-group-sm">
		<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" value="{$FIELD->getEditValue($VALUE)}" type="text"
			class="js-tax-percent form-control form-control-sm js-tax" readonly="readonly" data-js="data-default-tax|value" />
		{if $TAXS_CONFIG['taxs'][0] != ''}
			{if empty($ITEM_DATA['taxparam'])}
				{assign var=TAXPARAM_VALUE value=''}
			{else}
				{assign var=TAXPARAM_VALUE value=$ITEM_DATA['taxparam']}
			{/if}
			<input name="inventory[{$ROW_NO}][taxparam]" type="hidden" value="{\App\Purifier::encodeHtml($TAXPARAM_VALUE)}"
				class="taxParam" {if $FIELD->isReadOnly()}readonly="readonly" {/if} />
			<span class="input-group-append u-cursor-pointer changeTax {if empty($ITEM_DATA['taxmode'])}d-none{/if}">
				<div class="input-group-text">
					<span class="small">
						<span class="fas fa-long-arrow-alt-up"></span>
						<span class="fas fa-percent"></span>
					</span>
				</div>
			</span>
		{/if}
	</div>
	<!-- /tpl-Base-inventoryfields-EditViewTaxPecent -->
{/strip}
