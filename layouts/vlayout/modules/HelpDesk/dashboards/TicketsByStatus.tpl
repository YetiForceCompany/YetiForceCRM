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
	YetiForce_Bar_Widget_Js('YetiForce_Ticketsbystatus_Widget_Js',{},{});
</script>

<div class="dashboardWidgetHeader">
	{foreach key=index item=cssModel from=$STYLES}
		<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
	{/foreach}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}

	<table width="100%" cellspacing="0" cellpadding="0">
		<tbody>
			<tr>
				<td class="span4">
					<div class="dashboardTitle textOverflowEllipsis" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}" style="width: 15em;"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>
				</td>
				<td class="refresh span2" align="right">
					<span style="position:relative;">&nbsp;</span>
				</td>
				<td class="span5">
					<div class="pull-right">
						{include file="dashboards/SelectAccessibleTemplate.tpl"|@vtemplate_path:$MODULE_NAME}
					</div>
				</td>
				<td class="widgeticons span2" align="right">
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