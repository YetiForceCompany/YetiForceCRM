{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" name="typeChart" value="{$CHART_TYPE}">
	<input type="hidden" name="divided" value="{$CHART_DIVIDED}">
	<input type="hidden" class="color" value="{$COLOR}">
	{if $CHART_OWNERS}
		<input class="widgetOwners" type="hidden" value="{\App\Purifier::encodeHtml(\App\Json::encode($CHART_OWNERS))}" />
	{/if}
	<input class="widgetData" name="data" type="hidden" value="{\App\Purifier::encodeHtml(\App\Json::encode($CHART_DATA))}" />
	{if $CHART_DATA['show_chart'] }
		<div class="widgetChartContainer chartcontent"><canvas></canvas></div>
	{else}
		<span class="noDataMsg">
			{\App\Language::translate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
		</span>
	{/if}
{/strip}
