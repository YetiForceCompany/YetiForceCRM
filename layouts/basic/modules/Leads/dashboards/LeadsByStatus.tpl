{*<!--
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ************************************************************************************/
-->*}
<script type="text/javascript">
	YetiForce_Bar_Widget_Js('YetiForce_Leadsbystatus_Widget_Js',{},{
		registerSectionClick : function() {	
			var thisInstance = this;
			var chartData = thisInstance.generateData();
			thisInstance.getPlotContainer().bind("plothover", function (event, pos, item) {
				if (item) {
					$(this).css( 'cursor', 'pointer' );
				}else{
					$(this).css( 'cursor', 'auto' );
				}
			});
			thisInstance.getPlotContainer().bind("plotclick", function (event, pos, item) {			
				if(item) {
					$(chartData['links']).each(function(){
						if(item.seriesIndex == this[0])
							window.location.href = this[1];
					});
				}
			});
		}
	});
</script>
<div class="dashboardWidgetHeader">	
{foreach key=index item=cssModel from=$STYLES}
	<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
{/foreach}
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}
{assign var=WIDGET_WIDTH value=$WIDGET->getWidth()}
<div class="row">
	<div class="col-md-8">
		<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(),$MODULE_NAME)}</strong></div>
	</div>
	<div class="col-md-4">
		<div class="box pull-right">
			{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
		</div>
	</div>
</div>
<hr class="widgetHr"/>
<div class="row">
	<div class="col-sm-6">
		<div class="input-group input-group-sm">
			<span class=" input-group-addon"><span class="glyphicon glyphicon-calendar iconMiddle margintop3"></span></span>
			<input type="text" name="createdtime" title="{vtranslate('Created Time', $MODULE_NAME)}" class="dateRange widgetFilter form-control width90" value="{implode(',', $DTIME)}"/>
		</div>
	</div>
	<div class="col-sm-6">
		{include file="dashboards/SelectAccessibleTemplate.tpl"|@vtemplate_path:$MODULE_NAME}
	</div>
</div>
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/DashBoardWidgetContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>

