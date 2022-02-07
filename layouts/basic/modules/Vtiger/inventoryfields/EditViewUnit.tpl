{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewUnit -->
	<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" type="hidden"
		class="form-control {$FIELD->getColumnName()} valueVal"
		value="{$ITEM_VALUE}"
		readonly="readonly" />
	<span class="{$FIELD->getColumnName()}Text valueText">
		{$FIELD->getDisplayValue($ITEM_VALUE, $ITEM_DATA)}
	</span>
	<!-- /tpl-Base-inventoryfields-EditViewUnit -->
{/strip}
