{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="dashboardWidgetHeader">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeader.tpl', $MODULE_NAME)}
	</div>
	<div class="dashboardWidgetContent noSpaces">
		{include file=\App\Layout::getTemplatePath('dashboards/RssContents.tpl', $MODULE_NAME) WIDGET=$WIDGET}
	</div>
{/strip}
