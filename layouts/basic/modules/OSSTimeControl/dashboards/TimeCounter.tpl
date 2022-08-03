{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-dashboards-TimeCounter -->
	<div class="dashboardWidgetHeader">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME) TITLE=App\Purifier::encodeHtml(App\Language::translate($WIDGET->getTitle(), 'Home'))}
		</div>
	</div>
	<div class="dashboardWidgetContent d-flex justify-content-center align-items-center">
		{include file=\App\Layout::getTemplatePath('dashboards/TimeCounterContents.tpl', $MODULE_NAME)}
	</div>
	<!-- /tpl-Base-dashboards-TimeCounter -->
{/strip}
