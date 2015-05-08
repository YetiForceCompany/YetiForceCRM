{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<div class="dashboardWidgetHeader">
	<div class="row-fluid">
		<div class="span8">
			<div class="dashboardTitle textOverflowEllipsis" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>
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
				<div>
					<select class="widgetFilter" id="historyType" name="type" style='margin-bottom:0px'>
						<option value="all" >{vtranslate('LBL_ALL')}</option>
						{if $COMMENTS_MODULE_MODEL->isPermitted('DetailView')}<option value="comments" >{vtranslate('LBL_COMMENTS')}</option>{/if}
						<option value="updates" >{vtranslate('LBL_UPDATES')}</option>
					</select>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/HistoryContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
