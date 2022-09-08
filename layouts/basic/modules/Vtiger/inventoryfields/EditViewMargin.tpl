{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewMargin -->
	{assign var=VALUE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, $FIELD->getColumnName())}
	<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" value="{$VALUE|escape}" type="text" class="margin form-control form-control-sm" readonly="readonly" />
	<!-- /tpl-Base-inventoryfields-EditViewMargin -->
{/strip}
