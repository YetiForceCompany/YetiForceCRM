{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="row">
		<div class="col-md-8">
			<h5 class="dashboardTitle h6" title="{App\Purifier::encodeHtml(App\Language::translate($WIDGET->getTitle(), $MODULE_NAME))}"><strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</strong></h5>
		</div>
		<div class="col-md-4">
			<div class="box float-right">
				{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<hr class="widgetHr" />
	<div class="row" >
		<div class="col-md-12 select2FullWidthContainer">
			{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
		</div>
	</div>
{/strip}
