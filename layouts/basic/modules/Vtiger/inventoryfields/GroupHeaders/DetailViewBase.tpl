{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-GroupHeaders-DetailViewBase -->
	{if $FIELD->isSummary()}{{$FIELD->getDisplayValue($FIELD->getSummaryValuesFromData($INVENTORY_ROWS, $INVENTORY_ROW.groupid), $INVENTORY_ROW)}}{/if}
	<!-- /tpl-Base-inventoryfields-GroupHeaders-DetailViewBase -->
{/strip}
