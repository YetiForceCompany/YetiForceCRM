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
{strip}
	<div class="dashboardWidgetHeader">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeader.tpl', $MODULE_NAME)}
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
