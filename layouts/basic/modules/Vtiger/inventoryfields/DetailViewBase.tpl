{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if $FIELD->get('columnname') === 'qty' && $INVENTORY_ROW['unit'] === 'pack' && $INVENTORY_ROW['qtyparam']}({vtranslate('pcs','Products')}){/if} {$FIELD->getDisplayValue($ITEM_VALUE)}
{/strip}
