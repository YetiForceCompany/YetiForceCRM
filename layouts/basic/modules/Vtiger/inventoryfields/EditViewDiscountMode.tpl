{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewDiscountMode -->
	<select {if $ROW_NO} name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]"{/if}{' '}
			class="tpl-Base-inventoryfields-EditViewDiscountMode select2 js-discountmode "{' '}
			{if $FIELD->get('displaytype') == 10}readonly="readonly" {/if}
			title="{\App\Language::translate('LBL_DISCOUNT_MODE', $MODULE)}"
			data-js="change|val">
		<option value="0" {if $ITEM_VALUE == '0'}selected{/if}>
			{\App\Language::translate('LBL_GROUP', $MODULE)}
		</option>
		<option value="1" {if $ITEM_VALUE == '1'}selected{/if}>
			{\App\Language::translate('LBL_INDIVIDUAL', $MODULE)}
		</option>
	</select>
	<!-- /tpl-Base-inventoryfields-EditViewDiscountMode -->
{/strip}
