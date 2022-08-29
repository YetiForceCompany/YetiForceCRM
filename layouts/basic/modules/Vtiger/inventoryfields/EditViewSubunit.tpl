{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewSubunit -->
	{assign var=VALUE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, $FIELD->getColumnName())}
	<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" type="hidden"
		class="form-control {$FIELD->getColumnName()} valueVal"
		value="{\App\Purifier::encodeHtml($VALUE)}"
		readonly="readonly" />
	<span class="{$FIELD->getColumnName()}Text valueText">
		{$FIELD->getDisplayValue($VALUE, $ITEM_DATA, true)|escape}
	</span>
	<!-- /tpl-Base-inventoryfields-EditViewSubunit -->
{/strip}
