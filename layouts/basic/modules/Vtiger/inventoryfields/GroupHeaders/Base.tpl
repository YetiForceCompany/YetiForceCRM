{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-GroupHeaders-Base -->
	<span class="text-nowrap u-font-weight-600 middle{if $FIELD->isSummary()} js-inv-container-group-summary" data-sumfield="{lcfirst($FIELD->getType())|escape}{/if}"></span>
	<!-- /tpl-Base-inventoryfields-GroupHeaders-Base -->
{/strip}
