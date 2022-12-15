{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-GroupHeaders-DetailViewDiscount -->
	{if $FIELD->isSummary()}{{$FIELD->getDisplayValue($FIELD->getSummaryValuesFromData($INVENTORY_ROWS, $INVENTORY_ROW.groupid), $INVENTORY_ROW)}}{/if}
	{assign var="DISCOUNTS_INFO" value=$FIELD->getDiscountInfo($INVENTORY_ROW)}
	{if $DISCOUNTS_INFO}
		<span class="js-popover-tooltip ml-1" data-toggle="popover"
			data-content="{\App\Purifier::encodeHtml(implode('<br>', $DISCOUNTS_INFO))}">
			<span class="fas fa-info-circle"></span>
		</span>
	{/if}
	<!-- /tpl-Base-inventoryfields-GroupHeaders-DetailViewDiscount -->
{/strip}
