{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{assign var=ACCESSIBLE_USERS value=$CURRENTUSER->getAccessibleUsers()}
{assign var=ACCESSIBLE_GROUPS value=$CURRENTUSER->getAccessibleGroups()}
{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
<div class="dashboardWidgetHeader">
	<div class="row-fluid">
		<div class="span8">
			<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(),$MODULE_NAME)}</b></div>
		</div>
		<div class="span4">
			<div class="box pull-right">
				{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
			</div>
		</div>
	</div>
	<hr class="widgetHr"/>
	<div class="row-fluid" >
		<div class="span7">
			<div class="headerCalendar fc-center pinUnpinShortCut row-fluid" >
				<div class="span2">
					<button class="btn btn-mini" data-type="fc-prev-button"><i class="icon-chevron-left"></i></button>
				</div>
				<div class="span8 month marginLeftZero" style="text-align:center"> </div>
				<div class="span2">
					<button class="btn btn-mini" data-type="fc-next-button"><i class="icon-chevron-right"></i></button>
				</div>
			</div>
		</div>
		<div class="span5">
			{include file="dashboards/SelectAccessibleTemplate.tpl"|@vtemplate_path:$MODULE_NAME}
		</div>
	</div>
</div>
<div name="history" class="dashboardWidgetContent">
	{include file="dashboards/CalendarContents.tpl"|@vtemplate_path:$MODULE_NAME WIDGET=$WIDGET}
</div>
<script type='text/javascript'>
	YetiForce_Calendar_Widget_Js('YetiForce_Home_Widget_Js',{},{});
</script>
