{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewDiscount -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<div>
		<div class="input-group input-group-sm">
			<input type="text" name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" value="{$FIELD->getEditValue($VALUE)}" class="discount form-control form-control-sm" readonly="readonly" />
			{if $DISCOUNTS_CONFIG['discounts'][0] != ''}
				{if isset($ITEM_DATA['discountmode'])}
					{assign var=DISCOUNT_MODE value=$ITEM_DATA['discountmode']}
				{else}
					{assign var=DISCOUNT_MODE value=$DISCOUNTS_CONFIG['default_mode']}
				{/if}
				<input name="inventory[{$ROW_NO}][discountparam]" type="hidden" value="{if isset($ITEM_DATA['discountparam'])}{\App\Purifier::encodeHtml($ITEM_DATA['discountparam'])}{/if}" class="discountParam" />
				<span class="input-group-append u-cursor-pointer js-change-discount {if $DISCOUNT_MODE == 0}d-none{/if}">
					<div class="input-group-text">
						<span class="small">
							<span class="fas fa-long-arrow-alt-down"></span>
							<span class="fas fa-percent"></span>
						</span>
					</div>
				</span>
			{/if}
		</div>
	</div>
	<!-- /tpl-Base-inventoryfields-EditViewDiscount -->
{/strip}
