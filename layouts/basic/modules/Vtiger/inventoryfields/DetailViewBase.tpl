{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	{if $FIELD->get('columnname') === 'qty' && $INVENTORY_ROW['unit'] === 'pack' && $INVENTORY_ROW['qtyparam']}({\App\Language::translate('pcs','Products')}){/if} {$FIELD->getDisplayValue($ITEM_VALUE)}
{/strip}
