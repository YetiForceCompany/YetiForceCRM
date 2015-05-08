<div class="row-fluid">
	<div class="span8">
		<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>
	</div>
	<div class="span4">
		<div class="box pull-right">
			{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
		</div>
	</div>
</div>
<hr class="widgetHr"/>
<div class="row-fluid" >
	<div class="span12">
		<div class="pull-right">
			{include file="dashboards/SelectAccessibleTemplate.tpl"|@vtemplate_path:$MODULE_NAME}
		</div>
	</div>
</div>
