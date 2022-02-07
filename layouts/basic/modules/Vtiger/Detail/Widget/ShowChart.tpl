{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-ShowChart -->
	{if $CHART_DATA && $CHART_DATA.show_chart}
		<div class="dashboardWidgetHeader">
			<div class="row no-gutters"></div>
		</div>
		<div class="dashboardWidgetContent">
			<input type="hidden" name="typeChart" value="{$CHART_MODEL->getType()}">
			<input class="widgetData" type="hidden" value='{\App\Purifier::encodeHtml(\App\Json::encode($CHART_DATA))}' />
			<div class="widgetChartContainer u-min-height-250"><canvas></canvas></div>
		</div>
	{else}
		<div class="alert alert-warning">
			{\App\Language::translate('LBL_RECORDS_NO_FOUND')}
		</div>
	{/if}
	<!-- /tpl-Base-Detail-Widget-ShowChart -->
{/strip}
