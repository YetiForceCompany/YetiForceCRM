{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<div>
		<div class="input-group input-group-sm">
			<input type="text" name="discount{$ROW_NO}" value="{$FIELD->getEditValue($VALUE)}" class="discount form-control input-sm" readonly="readonly" />
			{if $DISCOUNTS_CONFIG['discounts'][0] != ''}
				<input name="discountparam{$ROW_NO}" type="hidden" value="{Vtiger_Util_Helper::toSafeHTML($ITEM_DATA['discountparam'])}" class="discountParam" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if} />
				<span class="input-group-addon cursorPointer changeDiscount {if $ITEM_DATA['discountmode'] == 0}hide{/if}">
					<img src="{vimage_path('Discount24.png')}" alt="{vtranslate('LBL_DISCOUNT', $MODULE)}" />
				</span>
			{/if}
		</div>
	</div>
{/strip}
