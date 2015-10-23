{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<div class="input-group input-group-sm">
		<input name="tax{$ROW_NO}" value="{$FIELD->getEditValue($VALUE)}" type="text" class="tax form-control input-sm" readonly="readonly"/>
		{if $TAXS_CONFIG['taxs'][0] != ''}
			<input name="taxparam{$ROW_NO}" type="hidden" value="{Vtiger_Util_Helper::toSafeHTML($ITEM_DATA['taxparam'])}" class="taxParam" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if} />
			<span class="input-group-addon cursorPointer changeTax {if $ITEM_DATA['taxmode'] == 0}hide{/if}">
				<img src="{vimage_path('Tax24.png')}" alt="{vtranslate('LBL_TAX', $MODULE)}" />
			</span>
		{/if}
	</div>
{/strip}
