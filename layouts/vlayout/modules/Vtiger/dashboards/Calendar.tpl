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
		<tr>
			<th class="span6">
				<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle())}</b></div>
			</th>
			<th class="span5">
				{include file="dashboards/SelectAccessibleTemplate.tpl"|@vtemplate_path:$MODULE_NAME}
			</th>
			<th class="widgeticons" align="right">
				{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
			</th>
		</tr>
		<tr>
			<th class="span12 refresh" align="center">
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
	Vtiger_Calendar_Widget_Js('Vtiger_Home_Widget_Js',{},{});
</script>