{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="dashboardWidgetHeader">
		{include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME}
	</div>
	<div class="dashboardWidgetContent noSpaces">
		{include file="dashboards/RssContents.tpl"|@vtemplate_path:$MODULE_NAME WIDGET=$WIDGET}
	</div>
{/strip}
