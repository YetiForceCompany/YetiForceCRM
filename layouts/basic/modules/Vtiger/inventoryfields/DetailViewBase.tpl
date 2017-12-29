{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $FIELD->get('columnname') === 'qty' && $INVENTORY_ROW['unit'] === 'pack' && $INVENTORY_ROW['qtyparam']}
		({\App\Language::translate('pcs','Products')})
	{/if} {$FIELD->getDisplayValue($ITEM_VALUE)}
{/strip}
