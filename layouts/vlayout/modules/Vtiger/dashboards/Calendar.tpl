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
	<table width="100%" cellspacing="0" cellpadding="0">
	<thead>
		<tr class="row-float">
			<th class="span3"> 
				 <div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"> <b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle())}</b></div> 
			</th>
			<th class="span5 headerCalendar fc-center pinUnpinShortCut row-fluid" >
				<div class="span2">
				<button type="button prev fc-corner-left " data-type="fc-prev-button"><span class="fc-icon fc-icon-left-single-arrow"></span></button>
				</div>
				<div class="span8 month marginLeftZero" style="text-align:center">
				</div>
				<div class="span2">
				<button type="button next fc-corner-left span3" data-type="fc-next-button"><span class="fc-icon fc-icon-right-single-arrow"></span></button> 
				</div>
			</th>
			<th class="span3">
				{include file="dashboards/SelectAccessibleTemplate.tpl"|@vtemplate_path:$MODULE_NAME}
			</th>
			<th class="widgeticons span1" align="right">
				{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
			</th>
		</tr>
		<tr>
			<th class=" refresh" align="center">
				<span style="position:relative;"></span>
			</th>
		</tr>
	</thead>
	</table>
</div>
<div name="history" class="dashboardWidgetContent">
	{include file="dashboards/CalendarContents.tpl"|@vtemplate_path:$MODULE_NAME WIDGET=$WIDGET}
</div>
<script type='text/javascript'>
	YetiForce_Calendar_Widget_Js('YetiForce_Home_Widget_Js',{},{});
</script>