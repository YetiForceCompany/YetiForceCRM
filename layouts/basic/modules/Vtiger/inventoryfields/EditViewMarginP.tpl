{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewMarginP -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<div class="input-group input-group-sm">
		<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" value="{$FIELD->getEditValue($VALUE)}" type="text" class="marginp form-control form-control-sm" readonly="readonly" />
		<div class="input-group-append">
			<span class="input-group-text">%</span>
		</div>
	</div>
	<!-- /tpl-Base-inventoryfields-EditViewMarginP -->
{/strip}
