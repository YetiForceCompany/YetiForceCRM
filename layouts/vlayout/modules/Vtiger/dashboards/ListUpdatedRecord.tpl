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
	<table width="100%" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th class="span5">
				<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle())}</b></div>
			</th>
			
			<th class="refresh span1" align="right">
				<span style="position:relative;"></span>
			</th>
			<th class="span2">
				<div>
					<select class="widgetFilter" id="listUpdatedRecordLimit" name="number" style='width:100px;margin-bottom:0px'>
						<option value="all" >{vtranslate('LBL_ALL')}</option>
						<option value="10" >10</option>
						<option value="25" >25</option>
						<option value="50" >50</option>
					</select>
				</div>
			</th>
			<th class="widgeticons span1" align="right">
				{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
			</th>
		</tr>
	</thead>
	</table>
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/ListUpdatedRecordContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>