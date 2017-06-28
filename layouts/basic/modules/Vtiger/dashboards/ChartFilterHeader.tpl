{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="dashboardWidgetHeader">
		{include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME}
	</div>
	<div class="dashboardWidgetContent">
		{include file="dashboards/ChartFilterContents.tpl"|@vtemplate_path:$MODULE_NAME WIDGET=$WIDGET}
	</div>
	<div class="dashboardWidgetFooter">
		{include file="dashboards/ChartFilterFooter.tpl"|@vtemplate_path:$MODULE_NAME}
	</div>
{/strip}
