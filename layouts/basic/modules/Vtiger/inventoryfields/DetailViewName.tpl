{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-DetailViewName -->
	<span class="userIcon-{\App\Record::getType($ITEM_VALUE)} mr-1"></span>
	<strong>{$FIELD->getDisplayValue($ITEM_VALUE)}</strong>
	{foreach item=FIELD from=$INVENTORY_MODEL->getFieldsByType('Comment')}
		{if $FIELD->isVisibleInDetail() && $INVENTORY_ROW[$FIELD->getColumnName()]}
			<br/>
			{$FIELD->getDisplayValue($INVENTORY_ROW[$FIELD->getColumnName()])}
		{/if}
	{/foreach}
	<div class="js-subproducts-container" data-js="append">
		<ul class="float-left">
		</ul>
	</div>
	<!-- /tpl-Base-inventoryfields-DetailViewName -->
{/strip}
