{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewValue -->
	{assign var=VALUE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, $FIELD->getColumnName())}
	{assign var="INPUT_TYPE" value='text'}
	{if $FIELD->isReadOnly() || $FIELD->getMapDetail($REFERENCE_MODULE)}
		{assign var="INPUT_TYPE" value='hidden'}
		<span class="{$FIELD->getColumnName()}Text valueText">
			{$FIELD->getDisplayValue($VALUE, $ITEM_DATA, true)|escape}
		</span>
	{/if}
	<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" type="{$INPUT_TYPE}" class="form-control form-control-sm {$FIELD->getColumnName()} valueVal" data-validation-engine="validate[maxSize[{$FIELD->getRangeValues()}]]" value="{\App\Purifier::encodeHtml($VALUE)}" {if $FIELD->isReadOnly()}readonly="readonly" {/if} />
	<!-- /tpl-Base-inventoryfields-EditViewValue -->
{/strip}
