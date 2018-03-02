{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<div class="input-group input-group-sm">
		<input name="tax{$ROW_NO}" value="{$FIELD->getEditValue($VALUE)}" type="text" class="tax form-control form-control-sm" readonly="readonly" />
		{if $TAXS_CONFIG['taxs'][0] != ''}
			<input name="taxparam{$ROW_NO}" type="hidden" value="{\App\Purifier::encodeHtml($ITEM_DATA['taxparam'])}" class="taxParam" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if} />
			<span class="input-group-addon cursorPointer changeTax {if $ITEM_DATA['taxmode'] == 0}d-none{/if}">
				<img src="{\App\Layout::getImagePath('Tax24.png')}" alt="{\App\Language::translate('LBL_TAX', $MODULE)}" />
			</span>
		{/if}
	</div>
{/strip}
