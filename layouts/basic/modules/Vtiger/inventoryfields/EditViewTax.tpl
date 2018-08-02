{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<div class="input-group input-group-sm">
		<input name="tax{$ROW_NO}" value="{$FIELD->getEditValue($VALUE)}" type="text" class="tax form-control form-control-sm js-tax" readonly="readonly" data-js="data-default-tax|value"/>
		{if $TAXS_CONFIG['taxs'][0] != ''}
			<input name="taxparam{$ROW_NO}" type="hidden" value="{\App\Purifier::encodeHtml($ITEM_DATA['taxparam'])}" class="taxParam" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if} />
			<span class="input-group-append u-cursor-pointer changeTax {if $ITEM_DATA['taxmode'] == 0}d-none{/if}">
				<div class="input-group-text">
					<span class="fa-layers fa-fw">
						<i class="fas fa-circle" data-fa-transform="grow-6"></i>
						<i class="fa-inverse fas fa-long-arrow-alt-up text-white" data-fa-transform="shrink-6  left-4"></i>
						<i class="fa-inverse fas fa-percent text-white" data-fa-transform="shrink-8  right-3"></i>
					</span>
				</div>
			</span>
		{/if}
	</div>
{/strip}
