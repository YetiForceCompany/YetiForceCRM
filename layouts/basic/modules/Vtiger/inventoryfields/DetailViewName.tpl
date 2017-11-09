{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<strong>{$FIELD->getDisplayValue($ITEM_VALUE)}</strong>
	{foreach item=FIELD2 from=$FIELDS[2]}
		{if $FIELD2->getName() == 'Comment'}
			<br />
			{$FIELD2->getDisplayValue($INVENTORY_ROW[$FIELD2->get('columnname')])}
		{/if}
	{/foreach}
{/strip}
