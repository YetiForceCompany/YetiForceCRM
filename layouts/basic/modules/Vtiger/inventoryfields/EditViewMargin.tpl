{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewMargin -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" value="{$FIELD->getEditValue($VALUE)}" type="text" class="margin form-control form-control-sm" readonly="readonly" />
	<!-- /tpl-Base-inventoryfields-EditViewMargin -->
{/strip}
