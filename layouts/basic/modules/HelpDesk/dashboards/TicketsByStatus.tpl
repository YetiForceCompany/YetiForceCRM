{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<script type="text/javascript">
		YetiForce_Bar_Widget_Js('YetiForce_TicketsByStatus_Widget_Js', {}, {});
	</script>
	<div class="dashboardWidgetHeader">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeadeAccessible.tpl', $MODULE_NAME)}
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
