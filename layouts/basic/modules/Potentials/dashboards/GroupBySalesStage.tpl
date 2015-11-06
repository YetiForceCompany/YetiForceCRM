<script type="text/javascript">
	Vtiger_Funnel_Widget_Js('Vtiger_Groupedbysalesstage_Widget_Js',{},{});
</script>
{assign var=ACCESSIBLE_USERS value=$CURRENTUSER->getAccessibleUsers()}
{assign var=ACCESSIBLE_GROUPS value=$CURRENTUSER->getAccessibleGroups()}
{foreach key=index item=cssModel from=$STYLES}
	<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
{/foreach}
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}
<div class="dashboardWidgetHeader">
	{include file="dashboards/WidgetHeadeAccessible.tpl"|@vtemplate_path:$MODULE_NAME}
	<div class="row filterContainer hide" style="position:absolute;z-index:100001">
		<div class="row">
			<span class="col-md-5">
				<span class="pull-right">
					{vtranslate('Expected Close Date', $MODULE_NAME)} &nbsp; {vtranslate('LBL_BETWEEN', $MODULE_NAME)}
				</span>
			</span>
			<span class="col-md-4">
				<input type="text" title="{vtranslate('Expected Close Date')}" name="expectedclosedate" class="dateRange widgetFilter" />
			</span>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/DashBoardWidgetContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
