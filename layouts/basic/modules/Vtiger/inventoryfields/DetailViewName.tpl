{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-DetailViewName -->
	<strong>{$FIELD->getDisplayValue($ITEM_VALUE)}</strong>
	{if isset($INVENTORY_MODEL)}
		{foreach item=FIELD from=$INVENTORY_MODEL->getFieldsByType('Comment')}
			{if $FIELD->isVisibleInDetail() && $INVENTORY_ROW[$FIELD->getColumnName()]}
				<br />
				<label class="u-text-small-bold mt-2">
					{\App\Language::translate($FIELD->get('label'), $MODULE_NAME)}
				</label>
				{$FIELD->getDisplayValue($INVENTORY_ROW[$FIELD->getColumnName()])}
			{/if}
		{/foreach}
		<div class="js-subproducts-container" data-js="append">
			<ul class="float-left mb-0">
			</ul>
		</div>
	{/if}
	<!-- /tpl-Base-inventoryfields-DetailViewName -->
{/strip}
