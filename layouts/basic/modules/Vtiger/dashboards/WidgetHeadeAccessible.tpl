{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
<div class="row">
	<div class="col-md-8">
		<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</strong></div>
	</div>
	<div class="col-md-4">
		<div class="box pull-right">
			{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
		</div>
	</div>
</div>
<hr class="widgetHr"/>
<div class="row" >
	<div class="col-md-12">
		<div class="pull-right">
			{include file="dashboards/SelectAccessibleTemplate.tpl"|@vtemplate_path:$MODULE_NAME}
		</div>
	</div>
</div>
{/strip}
