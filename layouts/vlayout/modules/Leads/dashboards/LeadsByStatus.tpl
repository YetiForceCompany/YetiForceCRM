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
<style>
	#select-user{
		width: 75%;
	}
	#select-date{
		width: 74%;
	}
</style>
<div class="dashboardWidgetHeader">	
{foreach key=index item=cssModel from=$STYLES}
	<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
{/foreach}
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}
{assign var=WIDGET_WIDTH value=$WIDGET->getWidth()}
<table width="100%" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<td class="span2">
				<div class="dashboardTitle textOverflowEllipsis" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}" style="width: 15em;"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>
			</td>
			<td class="span4">
				<span style="margin-right:4px;">
					{if $WIDGET_WIDTH gt 3}
						<span>
							<i style="margin-top:3px;" class="icon-calendar iconMiddle"></i>
						</span>
					{/if}
				</span>
				<span>
					<input type="text" name="createdtime" class="dateRange widgetFilter"  id="select-date" style="margin-bottom:0;" />
				</span>	
			</td>	
			<td class="span5">	
				<span style="margin-right:4px;">
					{if $WIDGET_WIDTH gt 3}
						<span>
							<i style="margin-top:3px;" class="icon-user iconMiddle"></i>
						</span>
					{/if}
				</span>
				<span>
					{assign var=ALL_ACTIVEUSER_LIST value=$CURRENTUSER->getAccessibleUsers()}
					{assign var=LOGGED_USER_ID value=$LOGGEDUSERID}
					<select class="widgetFilter " id="select-user" name="user" style="margin-bottom:0;" >
						<optgroup label="{vtranslate('LBL_USERS')}">
							{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
								<option {if $OWNER_ID eq $LOGGED_USER_ID } selected {/if} value="{$OWNER_ID}">
									{$OWNER_NAME}
								</option>
							{/foreach}
						</optgroup>
					</select>
				</span>
			</td>
			<td class="widgeticons span1" align="right">
				<div class="box pull-right">
					{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
				</div>
			</td>
		</tr>
	</tbody>
</table>	
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/DashBoardWidgetContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>